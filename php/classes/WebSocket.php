<?php 

	class WebSocket {
		protected $socket; //сам вебсокет
		private $connects; //массив подключенных сокетов
		private $info; //информация о подключенных соединениях
		private $sessions; //информация о сессиях
		
		function __construct ($address) { //конструктор класса - инициализация переменных и информирование в консоли о результате
			$this->socket = stream_socket_server($address, $errno, $error); //создает websocket
			
			if (!$this->socket) { //если создать не получилось, убиваем файл
				echo "socket is unavailable\n";
				die($error. "(" .$errno. ")\n"); 
			}
			else {
				echo "Start connection at $address\n";
			}
			$this->connects = array();
			$this->info     = [];
			$this->sessions = array();
		}

		function __destruct() { //закрытие потока сокета
			fclose($this->socket); 
		}
		
		public function run () { //запуск работы вебсокета
			while (true) {
				$read = $this->connects; //формируем массив прослушиваемых сокетов: 
				$read[]= $this->socket;	//добавляем к массиву сам сокет-сервер:
				$write = $except = null; 
				//ожидаем сокеты доступные для чтения (без таймаута) 
				if (!stream_select($read, $write, $except, null)) {
					break; 
				}

				//есть новое соединение 
				if (in_array($this->socket, $read)) {
					//принимаем новое соединение и производим рукопожатие: 
					if (($connect = stream_socket_accept($this->socket, -1)) //принимаем соединение и записываем его в $connect
							&& $this->info[(int)$connect] = $this->handshake($connect)) { //в массив $info записываем данные о рукопожатии (ip,port)
						echo "new connection...\n";   
						echo "connect= $connect OK\n";    
						$this->connects[(int)$connect] = $connect;//добавляем соединение в список необходимых для обработки (подключенных) 
						$this->onOpen($connect, $this->info[(int)$connect]);//вызываем пользовательский сценарий для события подключения к сокету
					} 
					unset($read[array_search($this->socket, $read)]); //соединение установленно, поэтому удаляем сам сокет изпрослушиваемых
				}

				//обрабатываем все соединения 
				foreach($read as $connect) {
					$data = fread($connect, 1000000); //чтение из потока сокета 
					$data = $this->decode($data);
					//соединение было закрыто (прошлый варик - !strlen($data) )
					if ($data["type"] == "close") {
						fclose($connect); //закрытие потока сокета (чтения из сокета)
						unset($this->connects[(int)$connect]); //удаление сокета из массива соединений
						$this->onClose($connect);//вызываем пользовательский сценарий закрытия 
						continue; //перейти к следующей итерации цикла 
					}
					else if ($data["type"] == "text") {
						$this->onMessage($connect, $data);
					}
				} 
			}
		}
		
		private function handshake($connect) { //Функция рукопожатия
			$info = array(); 
			$line = fgets($connect); 
			$header = explode(' ', $line); 
			$info['method'] = $header[0]; 
			$info['uri'] = $header[1]; 

			//считываем заголовки из соединения 
			while ($line = rtrim(fgets($connect))) {
				if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
					$info[$matches[1]] = $matches[2]; 
				}
				else {
					break;
				}
			} 

			//получаем адрес клиента 
			$address = explode(':', stream_socket_get_name($connect, true));
			$info['ip'] = $address[0]; 
			$info['port'] = $address[1]; 

			if (empty($info['Sec-WebSocket-Key'])) { 
				return false; 
			} 

			//отправляем заголовок согласно протоколу вебсокета 
			$SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))); 
			$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" . 
			"Upgrade: websocket\r\n" . 
			"Connection: Upgrade\r\n" . 
			"Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n"; 
			fwrite($connect, $upgrade); 
			
			return $info; 
		}
		
		private function encode($payload, $type = 'text', $masked = false) { // Кодирование уходящих данных
			$frameHead = array(); 
			$payloadLength = strlen($payload); 

			switch ($type) {
				case 'text': 
					// first byte indicates FIN, Text-Frame (10000001): 
					$frameHead[0] = 129; 
					break; 

				case 'close': 
					// first byte indicates FIN, Close Frame(10001000): 
					$frameHead[0] = 136; 
					break; 

				case 'ping': 
					// first byte indicates FIN, Ping frame (10001001): 
					$frameHead[0] = 137; 
					break; 

				case 'pong': 
					// first byte indicates FIN, Pong frame (10001010): 
					$frameHead[0] = 138; 
					break; 
			}

			// set mask and payload length (using 1, 3 or 9 bytes) 
			if ($payloadLength > 65535) {
				$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8); 
				$frameHead[1] = ($masked === true) ? 255 : 127; 
				for ($i = 0; $i < 8; $i++) {
					$frameHead[$i + 2] = bindec($payloadLengthBin[$i]); 
				} 
				// most significant bit MUST be 0 
				if ($frameHead[2] > 127) {
					return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)'); 
				} 
			}
			elseif ($payloadLength > 125) {
				$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8); 
				$frameHead[1] = ($masked === true) ? 254 : 126; 
				$frameHead[2] = bindec($payloadLengthBin[0]); 
				$frameHead[3] = bindec($payloadLengthBin[1]); 
			}
			else {
				$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength; 
			} 

			// convert frame-head to string: 
			foreach (array_keys($frameHead) as $i) {
				$frameHead[$i] = chr($frameHead[$i]); 
			} 
			if ($masked === true) {
				// generate a random mask: 
				$mask = array(); 
				for ($i = 0; $i < 4; $i++) {
					$mask[$i] = chr(rand(0, 255));
				} 

				$frameHead = array_merge($frameHead, $mask); 
			} 
			$frame = implode('', $frameHead); 

			// append payload to frame: 
			for ($i = 0; $i < $payloadLength; $i++) { 
				$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i]; 
			} 

			return $frame;
		}
		
		private function decode($data) {// Декодирование входящих данных
			$unmaskedPayload = ''; 
			$decodedData = array(); 

			// estimate frame type: 
			$firstByteBinary = sprintf('%08b', ord($data[0])); 
			$secondByteBinary = sprintf('%08b', ord($data[1])); 
			$opcode = bindec(substr($firstByteBinary, 4, 4)); 
			$isMasked = ($secondByteBinary[0] == '1') ? true : false; 
			$payloadLength = ord($data[1]) & 127; 

			// unmasked frame is received: 
			if (!$isMasked) { 
				return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)'); 
			} 

			switch ($opcode) { 
				// text frame: 
				case 1: 
					$decodedData['type'] = 'text'; 
					break; 

				case 2: 
					$decodedData['type'] = 'binary'; 
					break; 

				// connection close frame: 
				case 8: 
					$decodedData['type'] = 'close'; 
					break; 

				// ping frame: 
				case 9: 
					$decodedData['type'] = 'ping'; 
					break; 

				// pong frame: 
				case 10: 
					$decodedData['type'] = 'pong'; 
					break; 

				default: 
					return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)'); 
			} 

			if ($payloadLength === 126) {
				$mask = substr($data, 4, 4); 
				$payloadOffset = 8; 
				$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset; 
			}
			elseif ($payloadLength === 127) {
				$mask = substr($data, 10, 4); 
				$payloadOffset = 14; 
				$tmp = ''; 
				for ($i = 0; $i < 8; $i++) { 
					$tmp .= sprintf('%08b', ord($data[$i + 2])); 
				} 
				$dataLength = bindec($tmp) + $payloadOffset; 
				unset($tmp); 
			}
			else { 
				$mask = substr($data, 2, 4); 
				$payloadOffset = 6; 
				$dataLength = $payloadLength + $payloadOffset; 
			}

			/** 
			* We have to check for large frames here. socket_recv cuts at 1024 bytes 
			* so if websocket-frame is > 1024 bytes we have to wait until whole 
			* data is transferd. 
			*/ 
			if (strlen($data) < $dataLength) { 
				return false; 
			} 

			if ($isMasked) {
				for ($i = $payloadOffset; $i < $dataLength; $i++) {
					$j = $i - $payloadOffset; 
					if (isset($data[$i])) { 
						$unmaskedPayload .= $data[$i] ^ $mask[$j % 4]; 
					} 
				} 
				$decodedData['payload'] = $unmaskedPayload; 
			}
			else { 
				$payloadOffset = $payloadOffset - 4; 
				$decodedData['payload'] = substr($data, $payloadOffset); 
			} 

			return $decodedData; 
		}
		
		private function onOpen($connect, $info) { //обработка события подключения к сокету
			//echo "connected $connect\n ";
			//print_r( $this->info[(int)$connect] );
			//echo "\n";
		}
		
		private function multicast($connection, $message) { //пересылка сообщений, полученных от сокета-клиента
			$message = $this->encode( $message ); //представление сообщения в виде строки json и кодирование данных
			$read = $this->connects; //формируем массив сокетов, которым отправим message
			
			if (!$connection) { 
				foreach($read as $connect) {//массовая рассылка
					fwrite($connect, $message); //отправка сообщения (запись в поток)
				}
			}
			else {
				unset($read[(int)$connection]); //удаляем сокет-автора message из рассылки
				$k = null;
				$login = $this->info[(int)$connection]['login'];
				foreach ($this->sessions as $key=>$val){
					foreach ($val as $elem) {
						if ($elem == $login) {
							$k = $key;
						}
					}
				}
				$users = $this->sessions[ $k ];
				
				foreach($read as $con_elem) {//массовая рассылка
					foreach( $users as $val ){
						if ( $val == $this->info[(int)$con_elem]['login'] ) {
							echo "запись в $con_elem выполнена\n";
							fwrite($con_elem, $message); //отправка сообщения (запись в поток)
						}
					}
				}
			}
		} 

		private function sendInfo() { //передача информации о соединениях
			$data = $this->sessions; //формируем массив сокетов
			//массовая рассылка
			$message = array( 'type'=>'list', 'items'=>array() );
			foreach($data as $elem) {
				$message['items'][] = $elem['name'];
			}
			$message = json_encode( $message );
			echo $message;
			$this->multicast(null, $message);
		}
		
		private function onClose($connect) { //обработка события закрытия соединения
			echo "connection $connect has been closed\n";
			unset($this->connects[(int)$connect]);
			unset($this->info[(int)$connect]);
		} 
		
		private function onMessage($connect, $data) { //обработка события получения сообщения	
			$data = json_decode(json_decode($data["payload"])); //берем переданные данные из $data: $data["payload"] и парсим json в переменную
			echo "get message from $connect: "; //выводим данные в консоль
			print_r( $data );
			echo "\n";
			if ( 'txtMessage' == $data->type ) {
				if ( trim( $data->text ) ) { // проверяем, если передали пустую строку
					$data->text = htmlspecialchars( $data->text ); //чистим от опасных элементов
					$data = json_encode( json_encode( $data ) ); //кодируем обратно в json
					$this->multicast($connect, $data); // пересылаем данные остальным участникам
				}
			}
			else if ( 'pngMessage' == $data->type ) {
				$data = json_encode( json_encode( $data ) ); //кодируем обратно в json
				$this->multicast($connect, $data); // пересылаем данные остальным участникам
			}
			else if ( 'sessionMessage' == $data->type ) {
				if ( 'open' == $data->action ) {
					$this->info[(int)$connect]['login'] = $data->login;
					if ($data->broad) {$this->sendInfo();}
				}
				else if ( 'create' == $data->action && $data->name && $data->password ) {
					$flag = true;
					foreach($this->sessions as $elem) {
						if ( $elem['name'] == $data->name ) {
							$flag = false;
						}
					}
					if ( $flag ){
						$this->sessions[] = array(
							'name'=>$data->name,
							'password'=>$data->password
						);
					}
					
					$this->sendInfo();
				}
				else if ( 'enter' == $data->action ){
					$message = array( 'type'=>'submit', 'allow'=>false );
					foreach($this->sessions as $key=>$elem) {
						if ( ( $elem['name'] == $data->name ) && ( $elem['password'] == $data->password ) ) {
							$this->sessions[$key][] = $this->info[(int)$connect]['login'];
							$message['allow'] = true ; 
						}
					}
					$message = json_encode( $message );
					fwrite($connect, $this->encode( $message ));
				}
				else if ( 'exit' == $data->action ){
					$k = null;
					$login = $this->info[(int)$connect]['login'];
					foreach ($this->sessions as $key=>$val){
						foreach ($val as $k=>$elem) {
							if ($elem == $login) {
								unset( $this->sessions[$key][$k] );
							}
						}
					}
				}
			}
		}
	}
?>