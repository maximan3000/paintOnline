<?php

require_once 'WebSocket.php';

/*
класс для реализации логики сессий (разделения клиентов на группы) в рамках сокет-сервера 
*/
class SessionWebSocket extends WebSocket {

	/*добавилось: 
	$info[(int)$connect]['clientID'] - идентификатор клиента (ид сеанса авторизации)
	$info[(int)$connect]['session'] - идентификатор сессии сокета, к которой принадлежит клиент
	$session - переменная с информацией о сессиях (описание в конструкторе)

	*/
	protected $sessions; //информация о сессиях
	protected $startSession = 'general'; //название изначальной сессии - общей для всех
	
	function __construct ($address) { //инициализация класса
		parent::__construct($address);
		$this->sessions = array( 
			$this->startSession => array( 
				'data' => array( //данные для подключения к сессии
					'name' => null, //название сессии
					'hostID' => null, //идентификатор сессии = идентификатор клиента-создателя
					'password' => null //пароль для подключения к сессии
					),
				'connects' => array(), //массив прямых подключений сессии
				'clients' => array() //массив идентификаторов клиентов, которые подключены к сессии 
				//!!! в общей сессии задействовано только свойство 'connects'
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
		$this->clearConnect($connect);
	}

	protected function sendData($connect, $data) { //отправка данных - переопределение функции
		echo "\nsend message to $connect:\n"; print_r($data); echo "\n";
		$data = json_encode($data); //кодирование объекта в json
		parent::sendData($connect, $data); //вызов старого метода
	}

	/* ******************************************************** */
	/*			методы для работы сессий					 	*/
	/* ******************************************************** */

	protected function createSession($connection, $authData) { //создание новой сессии сокет-клиентом $connetion на его имя с данными сессии $authData. при удаче возвращаеся true. $authData = array( 'name' => string, 'password' => string )
		if ( isset( $this->info[(int)$connection]['clientID'] ) ) { //если клиент передал свой идентификатор
			$this->sessions[ $this->info[(int)$connection]['clientID'] ] = array( 
				'data' => array( 
					'name' => $authData['name'],
					'hostID' => $this->info[(int)$connection]['clientID'],
					'password' => $authData['password'] 
					),
				'connects' => array(),
				'clients' => array()
			); //сессия создается на имя (идентификатор) создателя и будет работать, пока создатель не удалит её (не выйдет из сессии). В поле data хранятся данные для подключения к сессии, а поле connects содержит массив сокетов, которые в данный момент подключены к сессии
			return true; //удалось создать сессию
		}
		else {
			return false; //иначе создать сессию не удалось
		}
	}

	protected function authSession($connect, $sessionID, $password) { //подключение $connect к сессии $sessionID. возвращает true, если переданный пароль $password подходит к сессии с ИД $sessionID
		if ( isset($this->sessions[$sessionID]) && $this->sessions[$sessionID]['data']['password'] == $password ) {
			$this->enterSession($connect, $sessionID);
			return true;
		}
		return false;
	}

	protected function replaceSession($connect) { //функция, перетаскивающая соединение $connect в прямое подключение к сессии (добавление в 'connects'), для которой в массиве 'clients' есть этот клиент. Если такой сессии нет, то клиент остается в общей сессии. Функция должна отработать сразу после получения от клиента его hostID
		foreach ($this->sessions as $key => $value) {
			if ( $key!=$this->startSession && in_array( $this->info[(int)$connect]['clientID'] , $value['clients'])) { //если это не общая сессия и данный клиент присутствует в списке подключенных к данной сессии
				$this->exitSession($connect); //выход клиента из общей сессии (так как в иной он быть не может)
				$this->info[(int)$connect]['session'] = $key;
				$this->sessions[$key]['connects'][] = $connect;
			}
		}
	}

	protected function exitSession($connect) { //выход сокет-клиента из сессии
		if ( isset($this->info[(int)$connect]['clientID']) && $this->info[(int)$connect]['session'] == $this->info[(int)$connect]['clientID'] ) { //если данный клиент является хостом сессии
			$this->deleteSession( $this->info[(int)$connect]['session'] ); //удаляется вся сессия
			echo "\nexiting host client from session ".$this->info[(int)$connect]['session']."\n";
		}
		else if ( $this->info[(int)$connect]['session']!=$this->startSession ) { //если данный клиент не является хостом и находится в локальной сессии
			$info = $this->info[(int)$connect]['session']; //информация о сессии клиента; проверять, есть ли эта информация не нужно, так как она точно задается при открытии соединения
			$indexConn = array_search( $connect, $this->sessions[$info]['connects'] ); //поиск данного соединения среди прямых подключений сессии
			$indexID = array_search( $this->info[(int)$connect]['clientID'], $this->sessions[$info]['clients']); //поиск идентификатора клиента среди подключенных к сессии клиентов
			unset( $this->sessions[$info]['connects'][$indexConn] ); //удаляем данный сокет из сессии, которой он принадлежит
			unset( $this->sessions[$info]['clients'][$indexID] ); //удаляем данный сокет из сессии, которой он принадлежит

			//возвращение клиента к общей сессии
			$this->info[(int)$connect]['session'] = $this->startSession;
			$this->sessions[ $this->startSession ]['connects'][] = $connect;

			echo "\nexiting common client from session ".$this->info[(int)$connect]['session']."\n";
		}
		else { //если данный клиент находится в общей сессии
			$indexConn = array_search( $connect, $this->sessions[$this->startSession]['connects'] );
			unset( $this->sessions[$this->startSession]['connects'][$indexConn] ); //удаляем данный сокет из сессии, которой он принадлежит
			echo "\nexiting common client from session ".$this->startSession."\n";
		}
	}

	protected function sessionCast($connection, $sessionID, $message) { //групповая пересылка сообщений, полученных от сокета-клиента (либо всем в группе, если вместо $connection стоит null)
		$read = $this->sessions[$sessionID]['connects']; //формируем массив сокетов, которым отправим message

		if ($connection) { 
			unset($read[ array_search($connection, $read) ]); //удаляем сокет-автора message из рассылки 
		}
		foreach($read as $connect) {//массовая рассылка
			$this->sendData($connect, $message); //отправка сообщения (запись в поток)
		}
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

	/* ******************************************************** */
	/*			вспомогательные методы класса				 	*/
	/* ******************************************************** */

	private function enterSession($connect, $sessionID) { //добавление сокет-клиента $connect в сессию $sessionID
		$this->exitSession($connect); //выход из предыдущей (общей) сессии
		$this->sessions[ $sessionID ]['clients'][] = $this->info[(int)$connect]['clientID']; //вход в текущую сессию
	}

	private function deleteSession($sessionID) { //удаляет сессию с данным идентификатором и полсылает сообщение всем подключенным к сессии клиентам, что сессия удалена
		$message = array( 
			'type' => 'exitSession', 
			'result' => true 
		); //результат выхода из сессии
		foreach ($this->sessions[ $sessionID ]['connects'] as $connect) {
			$this->sendData( $connect, $message );
			//при правильной работе клиента после отправки ему данного сообщения он должен переподключиться, и ему будут переназначены данные параметры
			//в случае неправильной работы, сокет клиент просто останется вне сессий и не будет получать сообщений

			//но все же стоит переназначить его сессию
			$this->info[(int)$connect]['session'] = $this->startSession;
			$this->sessions[ $this->startSession ]['connects'][] = $connect;
		}
		unset( $this->sessions[ $sessionID ] );
	}

	private function clearConnect($connect) { //для закрытия соединения - удаляет все связи (прямые соединения) данного клиента
		foreach ($this->sessions as $key => $value) {
			$index = array_search( $connect,  $value['connects']);
			if ($index!==false) {
				unset( $this->sessions[$key]['connects'][$index] );
			}
		}
	}

}

?>