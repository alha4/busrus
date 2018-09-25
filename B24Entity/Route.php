<?php
namespace B24Entity;

use B24Entity\Http\HttpClient;

final class Route {

 private static $instance; 

 private $params;

 public static function init(array $options) {

    if(is_null(self::$instance)) {

      self::$instance = new static();
      self::$instance->setParam($options);

    }

    return self::$instance;

 }

 private function setParam(array $params) {

    $this->params = $params;
     
 }

 private function getParams() {

   return $this->params;

 }

 private function resolve() {

   $params = $this->getParams();
  
   $key = $params['param_name'];

   $class = $params['values'][$_REQUEST[$key]];

   $reflection = new \ReflectionClass($class);
  
   return $reflection->newInstance($params['rest_url']);

 }
 
 public function response(\B24Entity\Helpers\IEncoder $encoder) {

    $entity = $this->resolve();

    $request = new HttpClient();

    if(!$request->getRequest()) {

        return false;

    }
    
    return $encoder->encode($entity->execute($request->getRequest()));

 }
 
 private function __construct() {}

 private function __clone() {}

}
