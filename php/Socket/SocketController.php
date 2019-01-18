<?php 

namespace App\Socket;
use Ratchet\ConnectionInterface;

/*
класс для обработки сообщений, получаемых от клиента
*/
class SocketController extends SessionWebSocket {	
	/*
	типы сообщений: (... означает любое число любых параметров - они не обрабатываются сервером, а просто пересылаются)

	от клиента
		1) msg ( 'type' => 'txtMessage', 'text' => string, ...) - текстовое сообщение
		2) msg ( 'type' => 'pngMessage', ...) - графическое сообщение
		3) msg ( 'type' => 'sessionMessage', 'action' => 'open', 'sessionID' => string) - первое сообщение после рукопожатия. передача ид клиента и инициализация
		4) msg ( 'type' => 'sessionMessage', 'action' => 'create', 'name' => string, 'password' => string) - создание сессии
		5) msg ( 'type' => 'sessionMessage', 'action' => 'enter', 'hostID' => string, 'password' => string) - заход в сессию
		6) msg ( 'type' => 'sessionMessage', 'action' => 'exit') - выход из сессии

	от сервера
		1) msg ( 'type' => 'createSession', 'result' => boolean) - результат создания сессии
		2) msg ( 'type' => 'enterSession', 'result' => boolean) - результат входа в сессию (аутентификации)
		3) msg ( 'type' => 'exitSession', 'result' => boolean) - выход клиента из текущей сессии (boolean тут наверно не нужен)
		4) msg ( 'type' => 'infoSession', 'items' => array( number => array( 'hostID' => string, 'name' => string ), number => ...) ) - короткая информация по всем созданным сессиям
	*/

	function __construct () {
		parent::__construct();
	}

	function __destruct() { //закрытие потока сокета
		parent::__destruct();
	}

	public function onMessage(ConnectionInterface $conn, $message) {
		$data = parent::onMessage($conn, $message); //вызов метода родителя
		$connect = $conn->resourceId;

		if (!isset($data->type)) {
			return;
		}
		
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
						echo "openopen";
						$this->info[(int)$connect]['clientID'] = $data->sessionID; //присваивание идентификатора клиенту
						$this->replaceSession($connect); //перевод сессии клиента
						$this->sendSessionInfo($connect); //пересылка созданных сессий клиенту $connect (перешлется, если он в общей сессии)
						break;
					case 'create': //событие создания сессии
						$answer = array( //результат создания сессии
							'type' => 'createSession', 
							'result' => false 
						); 
						$authData = array( //данные сессии для функции createSession
							'name' => $data->name, 
							'password' => $data->password 
						);
						if ( $this->createSession($connect, $authData) ) {
							$answer['result'] = true;
							$this->sendSessionInfo(null); //пересылка созданных сессий всем клиентам в общей сессии 
						}
						$this->sendData($connect, $answer); //отправка результата хосту (от него требуется операция захода в редактор)
						break;
					case 'enter': //событие входа в сессию
						$answer = array( 
							'type' => 'enterSession', 
							'result' => false 
						); //результат входа в сессиию

						if ( isset($data->hostID, $data->password) && $this->authSession($connect, $data->hostID, $data->password) ) { //проверка пароля для входа в сессиию
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
						echo "\n\n UNKNOWN MESSAGE TYPE (data->action)!!!\n";print_r($data->action);echo "\n\n";
						break;
				}
				break;
			default:
				echo "\n\n UNKNOWN MESSAGE TYPE (data->type)!!!\n";print_r($data->type);echo "\n\n";
				break;
		}
	}

	private function sendSessionInfo($connect) { //отправка данных по созданным сессиям клиенту $connect, если он находится в сессии $this->startSession. Если вместо $connect указан null, то сообщение получат все в сессии $this->startSession
		$message = array( //информация о сессиях
			'type' => 'infoSession',
			'items' => array()
		);
		$message['items'] = $this->getShortInfo();

		if ( $connect && $this->info[(int)$connect]['session']==$this->startSession ) {
			$this->sendData($connect, $message);
		}
		else {
			$this->sessionCast(null, $this->startSession, $message);
		}
	}
}

?>