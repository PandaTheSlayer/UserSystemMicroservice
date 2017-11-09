<?php

define('USER', 'root');
define('USERPWD', 'root');
define('HOST', 'localhost');
define('DB_NAME', 'UserMicroService');
define('DB_CHAR', 'utf8');

class Database extends mysqli {


	/**
	 * @Name toAssocArray
	 * @About Парсит результат функции query() в ассоциативный массив
	 * @param $result
	 * @return array
	 */
	public function toAssocArray( $result ){

		while( $row = mysqli_fetch_assoc( $result) ){
			$resultArray[] = $row;
		}

		return $resultArray;

	}

}