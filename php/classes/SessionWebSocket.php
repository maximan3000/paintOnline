<?php

require_once 'WebSocket.php';

/*
класс для реализации логики сессий (разделения клиентов на группы) в рамках сокет-сервера 
*/
class SessionWebSocket extends WebSocket {

	/*добавилось: 
	$info[(int)$connect]['clientID'] - идентификатор сессии клиента (ид сеанса авторизации)
	$info[(int)$connect]['session'] - идентификатор сессии сокета, к которой принадлежит клиент
	*/
	protected $sessions; //информация о сессиях
	protected $startSession = 'general'; //название изначальной сессии - общей для всех
	
	function __construct ($address) { //инициализация класса
		parent::__construct($address);
		$this->sessions = array( 
			$this->startSession => array(
				'connects' => array()
			)
		);
	}

	function __destruct() { //закрытие потока сокета
		parent::__destruct();
	}

	/* ******************************************************** */
	/*			переопределяемые методы (класса-потомка)	 	*/
	/* ******************************************************** */

	protected function onOpen($connect, $info) { //обработка события подключения к сокету
		$this->info[(int)$connect]['session'] = $this->startSession; //запись в информацию о сокете - его принадлежности к сессии (изначально - общей)
		$this->sessions[$this->startSession]['connects'][] = $connect; //отнесение данного сокета к списку сокетов общей сессии
	}

	protected function onMessage($connect, $data) { //обработка события получения сообщения - переопределение метода
		$data = json_decode($data['payload']); //декодирование из json
		return $data;
	}

	protected function onClose($connect) { //обработка события закрытия соединения - переопределение метода
		echo "\n connection $connect closed...";
		$this->exitSession($connect);
		echo "\n"; print_r($this->sessions); echo "\n";
	}

	/* ******************************************************** */
	/*														 	*/
	/* ******************************************************** */

	protected function authSession($sessionID, $password) {
		if ( isset($this->sessions[$sessionID]) && $this->sessions[$sessionID]['data']['password'] == $password ) {
			$this
			return true;
		}
		return false;
	}

	protected function sendData($connect, $data) { //отправка данных - переопределение функции
		echo "\nsend message:\n"; print_r($data); echo "\n";
		$data = json_encode($data); //кодирование объекта в json
		parent::sendData($connect, $data); //вызов старого метода
	}

	protected function exitSession($connect) { //выход сокет-клиента из сессии
		if ( isset($this->info[(int)$connect]['clientID']) && $this->info[(int)$connect]['session'] == $this->info[(int)$connect]['clientID'] ) { //если данный клиент является хостом сессии (при условии что клиент успел передать свои данные clientID)
			$this->deleteSession( $this->info[(int)$connect]['session'] );
			echo "\nexiting host from session\n";
		}
		else { //если данный клиент не является хостом
			$info = $this->info[(int)$connect]['session']; //информация о сессии клиента; проверять, есть ли эта информация не нужно, так как она точно задается при открытии соединения
			$index = array_search($connect, $this->sessions[$info]['connects']);
			$indexID = array_search($connect, $this->sessions[$info]['sessionsID']);
			unset( $this->sessions[$info]['connects'][$index] ); //удаляем данный сокет из сессии, которой он принадлежит
			unset( $this->sessions[$info]['sessionsID'][$indexID] ); //удаляем данный сокет из сессии, которой он принадлежит
			echo "\nexiting common from session\n";
		}
		echo "\n"; print_r($this->sessions); echo "\n";
	}

	protected function enterSession($connect, $authData) { //вход сокет-клиента $connect в сессию с идентификатором $sessionID
		if ( $this->sessions[ $authData['sessionID'] ]['data']['password'] == $authData['password'] ) { //если отправленные клиентом пароль для сессии, к которой он подключается совпадают с установленным хостом паролем, то подключение успешно
			$this->exitSession($connect); //выход из предыдущей (общей) сессии
			$this->sessions[ $authData['sessionID'] ]['connects'][] = $connect; //вход в текущую сессию
			return true;
		}
		else {
			return false;
		}	
	}

	protected function sessionCast($connection, $sessionID, $message) { //групповая пересылка сообщений, полученных от сокета-клиента (либо всем в группе, если вместо $connection стоит null)
		$read = $this->sessions[$sessionID]['connects']; //формируем массив сокетов, которым отправим message

		if ($connection) { 
			unset($read[(int)$connection]); //удаляем сокет-автора message из 
		}
		foreach($read as $connect) {//массовая рассылка
			$this->sendData($connect, $message); //отправка сообщения (запись в поток)
		}
	}

	protected function createSession($connection, $authData) { //создание новой сессии сокет-клиентом $connetion на его имя с данными сессии $authData. при удаче возвращаеся true
		if ( isset( $this->info[(int)$connection]['clientID'] ) ) { //если клиент передал идентификатор сессии
			$this->sessions[ $this->info[(int)$connection]['clientID'] ] = array( 
				'data' => array( 
					'name' => $authData->name,
					'hostID' => $this->info[(int)$connection]['clientID'],
					'password' => $authData->password 
					),
				'connects' => array()
			); //сессия создается на имя (идентификатор) создателя и будет работать, пока создатель не удалит её (не выйдет из сессии). В поле data хранятся данные для подключения к сессии, а поле connects содержит массив сокетов, которые в данный момент подключены к сессии
			return true; //удалось создать сессию
		}
		else {
			return false; //иначе создать сессию не удалось
		}
	}

	protected function deleteSession($sessionID) { //удаляет сессию с данным идентификатором и полсылает сообщение всем подключенным к сессии клиентам, что сессия удалена
		$message = array( 
			'type' => 'sessionMessage',
			'action' => 'deleteSession' 
		);
		foreach ($this->sessions[ $sessionID ]['connects'] as $connect) {
			$this->sendData( $connect, $message );
			//удалять из сокет клиента информацию о сессии и добавлять его к общей сессии (general) не нужно, так как:
			//при правильной работе клиента после отправки ему данного сообщения он должен переподключиться, и ему будут переназначены данные параметры
			//в случае неправильной работы, сокет клиент просто останется вне сессий и не будет получать сообщений

			//но все же сделаем это =)
			$this->info[(int)$connect]['session'] = $this->startSession;
		}
		unset( $this->sessions[ $sessionID ] );
	}

	protected function getShortInfo() { //возвращает короткую информацию (название, ид хоста) для каждой сессии, созданной клиентом
		$sessData = array(); //информация о всех сессииях для клиентов
		foreach ($this->sessions as $key => $value) {
			if ($key!=$this->startSession) {
				$sessData[] = array( 
					'hostID' => $value['data']['hostID'] ,
					'name' => $value['data']['name'] 
				);
			}
		}
		return $sessData;
	}
}

?>