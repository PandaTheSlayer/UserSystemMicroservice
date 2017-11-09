<?php

include_once ROOT.'/models/UserModel.php';

class UserController {


	/**
	 * Принимает POST-запрос с данными для регистрации нового аккаунта в системе
	 * Запрос содержит следующие поля (обязательные помечены звездочкой):
	 * 		login*
	 * 		password*
	 * 		sex
	 * 		age
	 * 		city
	 * Далее вызывается функция валидации пришедшей формы данных и данные передаются модели
	 * */
	public function actionRegistration(){

		if(isset($_POST) && !empty($_POST)){

			$data = $_POST;

			if($this->validateRegistrationFormData($data)) {

				$userModel = new UserModel();
				if ( $userModel->registerUser($data) == 200 ){
					echo json_encode(array(
						"status_code" => 200,
						"message" => 'Пользователь успешно добавлен!'
					), JSON_UNESCAPED_UNICODE);
				} else echo json_encode(array(
						"status_code" => 403,
						"error_message" => 'Такой логин уже занят!'
				), JSON_UNESCAPED_UNICODE);

			} else
				echo json_encode(array(
					"status_code" => 401,
					"error_message" => 'Некорректная форма запроса!'
				), JSON_UNESCAPED_UNICODE);

		}

		return true;
	}

	/**
	 * Принимает запрос на валидацию регистрационной формы
	 */
	private function validateRegistrationFormData($request){

		if (
			!empty($request['login']) &&
			!empty($request['password']) &&
			isset($request['sex']) &&
			isset($request['age']) &&
			isset($request['city']) &&
			count($request) == 5
		){
			return true;
		} else return false;

	}


	/**
	 * Принимает данные от пользователя, валидирует форму запроса на логин
	 * Если форма запроса валидна то отправляем данные на модель
	 * Запрос возвращает токен, по которому в дальнейшем можно получать информацию о юзере
	 * */
	public function actionLogin(){

		if(isset($_POST) && !empty($_POST)){

			$data = $_POST;

			if($this->validateLoginFormData($data)){

				$userModel = new UserModel();

				$res = $userModel->loginUser($data);

				if($res){

					echo json_encode(array(
						"status_code" => 200,
						"token" => $res
					));

				} else
					echo json_encode(array(
						"status_code" => 500,
						"error_message" =>"Некорректный логин или пароль!"
				), JSON_UNESCAPED_UNICODE);

			} else
				echo json_encode(array(
					"status_code" => 402,
					"error_message" => 'Некорректная форма запроса!'
			), JSON_UNESCAPED_UNICODE);

		}

		return true;
	}

	/**
	 * Валидация формы логина
	 */
	private function validateLoginFormData($request){

		if (
			!empty ( $request['login'] ) &&
			!empty ( $request['password'] )
		) {
			return true;
		} else return false;

	}


	/**
	 * Принимает данные от пользователя, валидирует форму запроса информации
	 * Если форма запроса валидна, то отправляет данные на модель
	 * Запрос возвращает информацию о юзере, при условии, что токен валидный
	 */
	public function actionInfo(){

		if( isset($_POST) && !empty($_POST) ){

			$request = $_POST;

			if ($this->validateInfoFormData($request)){

				$token = $request['token'];

				$userModel = new UserModel();
				$res = $userModel->getUserInfo($token);

				if($res){
					echo json_encode($res);
				} else echo json_encode(array(
					'status_code' => 401,
					"error_message" => 'Некорректный токен!'
				), JSON_UNESCAPED_UNICODE);

			} else echo json_encode(array(
					'status_code' => 402,
					'error_message' => 'Некорректная форма запроса!'
			), JSON_UNESCAPED_UNICODE);

		}

		return true;
	}

	/**
	 * Валидация формы информации о юзере
	 */
	private function validateInfoFormData($request){

		if(!empty($request['token']))
			return true;
		else return false;

	}

}