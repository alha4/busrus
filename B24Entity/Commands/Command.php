<?php
namespace B24Entity\Commands;

use \B24Entity\Http\HttpClient;

abstract class Command {

 private static $MAX_URL_LEN = 200;

 private $base_url;

 private $http;
 
 public function __construct($rest_url) {

   $this->http = new HttpClient($rest_url);

 }

 protected function request($command, array $data, $show_next = false) {

   return $this->http->send($command, $data, $show_next);

 }

 abstract public function execute($request);
} 
