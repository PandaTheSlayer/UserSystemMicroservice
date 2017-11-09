<?php


class Router {

	private $routes;
	private $projectName = 'RegistrationMicroService/';


	public function __construct() {

		$routesPath = ROOT.'/config/routes.php';
		$this->routes = include ($routesPath);

	}


	/**
	 * Возвращает URI
	 * @return string
	 */
	private function getURI(){

		if(!empty($_SERVER['REQUEST_URI'])){
			$uri = trim($_SERVER['REQUEST_URI'], '/');
			$uri = substr_replace($uri, '', 0, strlen($this->projectName));
		}
		return $uri;

	}

	public function run() {


		// Получить строку запроса
		$uri = $this->getURI();


		// Проверить наличие такого запроса в роутах
		foreach ($this->routes as $uriPattern => $path){

			if (preg_match("~$uriPattern$~", $uri)){

				// Определяем контроллер и экшен
				$segments = explode('/', $path);

				$controllerName = ucfirst (array_shift($segments)."Controller");
				$actionName = 'action'.ucfirst (array_shift($segments));


				// Подключаем контроллер
				$controllerFile = ROOT . '/controllers/' . $controllerName . '.php';

				if(file_exists($controllerFile))
					include_once ($controllerFile);

				// Создаем объект контроллера и вызываем экшен
				$controllerObj = new $controllerName;
				$result = $controllerObj->$actionName();
				if($result != null){
					break;
				}

			}

		}

	}

}