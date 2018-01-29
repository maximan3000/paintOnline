<?php 

//последняя идея - перепроверить работу с сессиями и переделать так, чтобы в переменную сессии записывался идентификатор сеанса клиента, а потом при подключении к сокету при получении от него этого идентификатора, происходило бы перетаскивание этого сокета в коннекты у переменной сессий

require_once 'SessionWebSocket.php';

class SocketController extends SessionWebSocket {	
	
	function __construct ($address) {
		parent::__construct($address);
	}

	function __destruct() { //закрытие потока сокета
		parent::__destruct();
	}

	protected function onMessage($connect, $data) { //обработка события получения сообщения	
		$data = parent::onMessage($connect, $data); //вызов метода родителя

		echo "get message from $connect: "; //выводим данные в консоль
		print_r( $data );
		echo "\n"; print_r($this->sessions); echo "\n";
		switch ($data->type) {
			case 'txtMessage': //текстовое сообщение (для чата)
				if ( trim( $data->text ) ) { // проверяем, если передали пустую строку
					$data->text = htmlspecialchars( $data->text ); //чистим от опасных элементов
					$this->sessionCast($connect, $this->info[(int)$connect]['session'], $data); // пересылаем данные остальным участникам
				}
				break;
			case 'pngMessage': //графическое сообщение (для редактора графики)
				$this->sessionCast($connect, $this->info[(int)$connect]['session'], $data); // пересылаем данные остальным участникам
				break;
			case 'sessionMessage': //управляющее сообщение (для работы сессий)
				switch ($data->action) {
					case 'open': //первое действие при подключении - получение от клиента идентификатора его сессии
						$this->info[(int)$connect]['clientID'] = $data->sessionID;
						//if ($data->broad) { $this->sendInfo(); }
						break;
					case 'create': //событие создания сессии
						$answer = array( 
							'type' => 'createSession', 
							'result' => false 
						); //результат создания сессии

						if ( $this->createSession($connect, $data) ) {

							$message = array( //информация о сессиях
								'type' => 'infoSession',
								'items' => array()
							);
							$message['items'] = $this->getShortInfo();

							$answer['result'] = true;
							$this->sessionCast(null, $this->startSession, $message); //пересылка данных созданной сессии всем ожидающим клиентам 
						}
						$this->sendData($connect, $answer); //отправка результата хосту (от него требуется операция захода в редактор)
						break;
					case 'enter': //событие входа в сессию
						$answer = array( 
							'type' => 'enterSession', 
							'result' => false 
						); //результат входа в сессиию

						if ( isset($data->hostID, $data->password) && $this->authSession($data->hostID, $data->password) ) { //проверка пароля для входа в сессиию
							$answer['result'] = true;
						}
						$this->sendData($connect, $answer); //отправка результата хосту (от него требуется операция захода в редактор при корректности прочего)
						break;
					case 'exit':
						$answer = array( 
							'type' => 'exitSession', 
							'result' => true 
						); //результат выхода из сессии

						$this->exitSession($connect); //выход из сессии
						$this->sendData($connect, $answer);
						break;
					default:
						echo "\n\n UNKNOWN MESSAGE TYPE (data->action)!!! \n\n";
						break;
				}
				break;
			default:
				echo "\n\n UNKNOWN MESSAGE TYPE (data->type)!!! \n\n";
				break;
		}

		/*
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
		*/
	}

}

?>