<?php 

require_once 'WebSocket.php';

class SocketController extends WebSocket {	
	
	private $sessions; //информация о сессиях

	function __construct ($address) {
		parent::__construct($address);
		$this->sessions = array();
	}

	function __destruct() { //закрытие потока сокета
		parent::__destruct();
	}

	protected function onOpen($connect, $info) { //обработка события подключения к сокету
	}
	
	protected function onClose($connect) { //обработка события закрытия соединения
		echo "connection $connect has been closed\n";
	} 
	
	protected function onMessage($connect, $data) { //обработка события получения сообщения	
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
				$this->sendData($connect, $message );
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

	private function multicast($connection, $message) { //пересылка сообщений, полученных от сокета-клиента
		$read = $this->connects; //формируем массив сокетов, которым отправим message
		
		if (!$connection) { 
			foreach($read as $connect) {//массовая рассылка
				$this->sendData($connect, $message); //отправка сообщения (запись в поток)
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
						$this->sendData($con_elem, $message); //отправка сообщения (запись в поток)
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
}

?>