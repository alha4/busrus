<?php
#header('Access-Control-Allow-Origin: *'); //Разрешенные адреса запроса
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header("Content-type: application/json; charset=utf-8");
header('Cache-Control: no-cache, must-revalidate'); 

require_once $_SERVER['DOCUMENT_ROOT']."/autoloader.php";

/**
 ответственный по умолчанию
*/
const DEFAULT_ASSIGNED = 39;
/**
 логировать все запросы 
*/
const LOG_ENABLED = 'Y';

/**
 относительный путь к файлу лога
 const LOG_PATH = '';

 максимальный размер файла лога в МБ
 const MAX_LOG_SIZE = 0;
*/

use \B24Entity\Helpers\JsonEncoder,
    \B24Entity\Route;

/**
  @param string rest_url url веб хук
  @param string param_name имя ключа запроса 
  @param array values @keys ключи значение параметра param_name

  https://server.addr/?type=order|contractor|clear
*/
try {

  $params = [
       "rest_url"   => "https://bus-rus.bitrix24.ru/rest/17/4mwjobk0lz2g7lvq/",
       "param_name" => "type",
       "values" => [
          "order"       => "\B24Entity\Commands\Orders",
          "contractor"  => "\B24Entity\Commands\Contractor",
          /*"clear"      => "\B24Entity\Commands\Cleaner",*/
       ]  
  ];

  $route = Route::init($params);
  echo $route->response(new JsonEncoder());

} catch(Error $err) {
 
  echo json_encode(array("Line" => $err->getLine(),'file' => $err->getFile(),'message' => $err->getMessage()));

} catch(Exception $err) {

  echo json_encode(array("Line" => $err->getLine(),'file' => $err->getFile(),'message' => $err->getMessage()));
}

?>