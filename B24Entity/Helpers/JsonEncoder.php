<?php
namespace B24Entity\Helpers;

use B24Entity\Helpers\IEncoder;

final class JsonEncoder implements IEncoder {

   public function encode($data) {  

      return json_encode($data, JSON_UNESCAPED_UNICODE);

   }

}