<?php
namespace B24Entity\Commands;
session_start();

class Cleaner extends Command  {

 private static $COMPANY_LIST    = 'crm.company.list.json';

 private static $COMPANY_DELETE  =  "crm.company.delete.json";

 private static $CONTACT_LIST    = 'crm.contact.list.json';

 private static $CONTACT_DELETE  =  "crm.contact.delete.json";

 private static $DEAL_LIST       = 'crm.deal.list.json';

 private static $DEAL_DELETE     = 'crm.deal.delete.json';

 public function execute($request) {

  if(empty($_SESSION['NEXT'])) {
     $_SESSION['NEXT'] = 0;
  }

  switch($request['entity']) {

    case  'company':
       return $this->deleteCompany();
    break;

    case  'contact':
       return $this->deleteContact();
    break;

    case  'deal':
       return $this->deleteDeal();
    break;

    case  'stop':
     unset($_SESSION['NEXT']);
     return false;
    break;

  }

  return false; 

 }

 private function deleteCompany() {

   $queryData = array(
     "order"  => array("UF_CRM_1534925547" => "ASC"),
     "filter" =>  array(),
     "select" => array("UF_CRM_1534925547","ID"),
     "start"  => $_SESSION['NEXT']
  );

  $result = $this->request(self::$COMPANY_LIST, $queryData, true);

  if($result['next']) {
  
    $_SESSION['NEXT']+= (int)$result['next'];

  }

  $found = false;

  foreach($result['result'] as $item) {

    if(!$item['UF_CRM_1534925547']) {

        $found = true;

        $data = array("id" => $item['ID']);

        $result = $this->request(self::$COMPANY_DELETE, [], $data);

         if($result['error_description']) {
 
              #echo $item['ID'],' ', $result['error_description'], "\n\r";

        } else {

            #echo $item['ID'], '-ok' , "\n\r";
       }

    } else {
        #echo $item['UF_CRM_1534925547'],"\n\r";
   }
  }
  if($result['next'] && $found) {
     return ['RESPONSE' => $_SESSION['NEXT']];

   } else {

     unset($_SESSION['NEXT']);
     return false;
  }
 }

 private function deleteDeal() {

     $queryData = array(
     "order"  => array("COMPANY_ID" => "ASC"),
     "filter" => array(),
     "select" => array("COMPANY_ID","ID"),
     "start"  => $_SESSION['NEXT']
  );

  $result = $this->request(self::$DEAL_LIST, $queryData, true);

  if($result['next']) {
  
    $_SESSION['NEXT']+= (int)$result['next'];

  }
  $found  = false;

  foreach($result['result'] as $item) {

    if(!$item['COMPANY_ID']) {

        $found  = true;

        $data = array("id" => $item['ID']);

        $result = $this->request(self::$DEAL_DELETE, $data);

         if($result['error_description']) {
 
            #echo $item['ID'],' ', $result['error_description'], "\n\r";

        } else {

            #echo $item['ID'], '-ok' , "\n\r";
       }

    }
  }
  if($result['next'] && $found) {
     return ['RESPONSE' => $_SESSION['NEXT'],'next' => $result['next']];

   } else {
   
     unset($_SESSION['NEXT']);
     return false;
     
  }
 }

 private function deleteContact() {

     $queryData = array(
     "order"  => array("COMPANY_ID" => "ASC"),
     "filter" =>  array(),
     "select" => array("COMPANY_ID","ID"),
     "start"  => $_SESSION['NEXT']
  );

  $result = $this->request(self::$CONTACT_LIST, $queryData, true);

  if($result['next']) {
  
    $_SESSION['NEXT']+= (int)$result['next'];

  }
  $found  = false;

  foreach($result['result'] as $item) {

    if(!$item['COMPANY_ID']) {

        $found  = true;

        $data = array("id" => $item['ID']);

        $result = $this->request(self::$CONTACT_DELETE, $data);

         if($result['error_description']) {
 
              #echo $item['ID'],' ', $result['error_description'], "\n\r";

        } else {

            #echo $item['ID'], '-ok' , "\n\r";
       }

    }
  }
  if($result['next'] && $found) {
     return ['RESPONSE' => $_SESSION['NEXT']];

   } else {

     unset( $_SESSION['NEXT']);
     return false;
     
  }
 }
}