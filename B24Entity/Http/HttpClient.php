<?php
namespace B24Entity\Http;

use B24Entity\Http\IHttp;

final class HttpClient implements IHttp {

  private static $MAX_URL_LEN = 200;

  private $base_url;

  const HTTP_PROTOCOL = 'https';

  private static $ACCEPT_PARAMS  = ['filter','select','order','fields','id','rows'];

  public function __construct($url = '') {
 
  if($url) {

   $protocol = parse_url($url, PHP_URL_SCHEME);

   if(!filter_var($url,FILTER_VALIDATE_URL) || 
      $protocol != self::HTTP_PROTOCOL || 
      strlen($url) > self::$MAX_URL_LEN) {

      throw new \Exception('NOT VALID URL HANDLER');  

   }

   $this->base_url = $url;
  }
 }

 public function send($command, array $request_params = [], $show_next = false) {

   $params_keys = array_keys($request_params);
 
   if(count(array_diff($params_keys, self::$ACCEPT_PARAMS)) > 0) {

      throw new Exception('not accept parameters');

   } 

   $url = $this->base_url;
   $url.= $command;

   $curl = curl_init();

   curl_setopt_array($curl, array(
     CURLOPT_SSL_VERIFYPEER => 0,
     CURLOPT_POST => 1,
     CURLOPT_HEADER => 0,
     CURLOPT_RETURNTRANSFER => 1,
     CURLOPT_URL => $url,
     CURLOPT_POSTFIELDS => http_build_query($request_params),
   ));

   $result = curl_exec($curl);
   curl_close($curl);

   $response = json_decode($result, 1);
   
   if($response['result'] && !$response['error_description']) {

     if($show_next) {

        return array("result" => $response['result'], "next" => $response['next']);

     }

     return $response['result'];
 
   }
   
   return $response;
 }

 public function getRequest() {

   $context = stream_context_create(array());

   return json_decode(file_get_contents("php://input",FALSE, $context),1);

 }

}