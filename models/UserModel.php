<?php

include_once ROOT.'/components/Database.php';

class UserModel {

	/**
	 * Производит регистрацию клиента в системе, при условии, что введенный логин свободен
	 */
	public function registerUser($userData){

		if( $this->isLoginUnique( $userData['login']) ){

			$db = new Database();
			$db->connect(HOST, USER, USERPWD, DB_NAME);

			$stmt = $db->prepare("INSERT INTO users VALUES (NULL, ?, ?, ?, ?, ?)");


			$login = $userData['login'];
			$password = password_hash($userData['password'], PASSWORD_DEFAULT);
			$sex = $userData['sex'];
			$age = $userData['age'];
			$city = $userData['city'];

			$stmt->bind_param("ssiis", $login, $password, $sex, $age, $city);
			$stmt->execute();

			return 200;

		} else return 403;

	}

	/**
	 * Проверяет не занят ли уже логин
	 */
	private function isLoginUnique($login){

		$db = new Database();
		$db->connect(HOST, USER, USERPWD, DB_NAME);

		$stmt = $db->prepare("SELECT * FROM users WHERE login=?");
		$stmt->bind_param("s", $login);

		$stmt->execute();

		$result = $stmt->fetch();

		if ($result>0) return false;
		else return true;

	}

	/**
	 * Возвращает токен сессии, при условии, что логин возможен
	 */
	public function loginUser($userData){

		$login = $userData['login'];
		$password = $userData['password'];

		if($this->isPasswordTrue($login, $password)){

			return $this->generateUserToken($login);

		} else return false;

	}

	/**
	 * Генерирует токен сессии для каждого подключения
	 */
	private function generateUserToken($username){

		$token = uniqid($username, true);
		$token = sha1($token);

		$tstamp = $_SERVER['REQUEST_TIME'];

		$db = new Database();
		$db->connect(HOST, USER, USERPWD, DB_NAME);

		$stmt = $db->prepare("INSERT INTO users_tokens VALUES (?, ?, ?) ");
		$stmt->bind_param('ssi', $token, $username, $tstamp);

		$res = $stmt->execute();

		if($res){
			return $token;
		} else return false;

	}

	/**
	 * Проверяет правильность введенных пользователем данных и возвращает true, если логин возможен
	 */
	private function isPasswordTrue($login, $password){

		$db = new Database();
		$db->connect(HOST, USER, USERPWD, DB_NAME);

		$stmt = $db->prepare("SELECT password FROM users WHERE login=?");
		$stmt->bind_param('s', $login);

		$stmt->execute();
		$stmt->bind_result($hash);

		$password_hash = '';

		while ($stmt->fetch()){
			$password_hash = $hash;
		}

		if( password_verify( $password, $password_hash ) ){
			return true;
		} else return false;

	}

	/**
	 * Возвращает информацию о пользователе по токену, при условии, что validateUserToken() вернул true
	 */
	public function getUserInfo($token){

		$db = new Database();
		$db->connect(HOST, USER, USERPWD, DB_NAME);

		if($this->validateUserToken($token)){

			$stmt = $db->prepare("SELECT city, age, sex FROM users 
										INNER JOIN users_tokens on users_tokens.login = users.login
										WHERE token = ?");
			$stmt->bind_param('s', $token);
			$stmt->bind_result($city, $age, $sex);

			$stmt->execute();

			while ($stmt->fetch()){
				$userInfo['city'] = $city;
				$userInfo['age'] = $age;
				$userInfo['sex'] = $sex;
			}

			return $userInfo;

		} else return false;


	}

	/**
	 * Проверяет наличие токена в таблица сессий
	 */
	private function validateUserToken($token){

		$db = new Database();
		$db->connect(HOST, USER, USERPWD, DB_NAME);

		$stmt = $db->prepare("SELECT * FROM users_tokens WHERE token = ?");
		$stmt->bind_param('s', $token);

		$stmt->execute();

		$result = $stmt->fetch();

		if ($result>0) return true;
		else return false;

	}

}