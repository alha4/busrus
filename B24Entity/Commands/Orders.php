<?php
namespace B24Entity\Commands;

use \B24Entity\Commands\Command,
    \B24Entity\Helpers\Logger;

class Orders extends Command  {

 private static $DEAL_LIST    = 'crm.deal.list.json';

 private static $DEAL_ADD     = 'crm.deal.add.json';

 private static $DEAL_UPDATE  = 'crm.deal.update.json';

 private static $PRODUCT_ADD  = 'crm.deal.productrows.set.json';

 private static $DEAL_ERRORS  = [];

 private static $PRODUCT_ERRORS = [];

 private static $MAP_STATUS = ['Новый'             => 'NEW',
                               'Оплачен'           => 'PREPARATION', 
                               'Частично выполнен' => 'PREPAYMENT_INVOICE', 
                               'Выполнен'          => 'WON', 
                               'Отменен'           => 'LOSE' 
                             ];

 use \B24Entity\Helpers\Contractor;

 public function execute($request) {

  if(defined('LOG_ENABLED') && LOG_ENABLED == 'Y') {
  
     Logger::log($request);

  }

  if(array_key_exists('Заказы', $request)) {

    $result = [];

    foreach($request['Заказы'] as $order) {

       sleep(3);

       $result[] = $this->load($order);
   
    }

    return $result;

  } else {

    if($request['Статус'] && $request['Номер']) {

       return array("STATUS" => $this->updateStatus($request));

    }

    return $this->load($request);
  }
  
 }

 private function load(array $request) {

  if(!$this->getDealID($request['Номер'])) {

   $arOrder = array( 
     "TITLE"          => $request['Номер'],
     "TYPE_ID"        => "SALE",
     "STAGE_ID"       => $this->getStatus($request['Статус']), 
     "CURRENCY_ID"    => "RUB", 
     "ASSIGNED_BY_ID" => $request['ИДОтветственный'],
     "OPPORTUNITY"    => $request['Сумма'],
     "COMMENTS"       => $request['Комментарий'],
     "UF_CRM_1526460231" => $request['Дата'],
     "UF_CRM_1526460177" => $request['id']
   );

   $contractor = $request['Контрагент'];

   $company_id = $this->getCompanyID($contractor['CODE']);

   if($company_id) {

      $arOrder['COMPANY_ID'] = $company_id;
 
   } elseif($contractor) {

      $company_id = $this->addCompany(array(
        "TITLE" => $contractor['Название'], 
        "PHONE" => $this->getCompanyPhone($contractor['Телефон'], $company_id),
        "EMAIL" => array(array("VALUE" => $this->emailParse($contractor['Email']), 'VALUE_TYPE' => 'WORK')),
        "ASSIGNED_BY_ID"    => $contractor['ИДОтветственный'] ? : DEFAULT_ASSIGNED,
        "UF_CRM_1526459919" => $contractor['ИНН'],
        "UF_CRM_1526459960" => $contractor['КПП'],
        "UF_CRM_1526460005" => $contractor['ФактАдрес'],
        "UF_CRM_1526460043" => $contractor['ЮрАдрес'],
        "UF_CRM_1534762574" => $contractor['OGRN'],
        "UF_CRM_1534925547" => $contractor['CODE'],
        "UF_CRM_1536061390" => $contractor['Факс'],
        "UF_CRM_1536061503" => $contractor['ЕмайлДобавочный'],
        "UF_CRM_1536562700" => $this->encodePhone($contractor['Телефон'], 'company', $company_id),
        "COMMENTS"          => $contractor['Комментарий']
      ));

     if($company_id) {

       $arOrder['COMPANY_ID'] = $company_id;  
 
     } else {

       Logger::log(self::$COMPANY_ERRORS);

     }

   } 

   $contacts = $contractor['КонтактныеЛица']; 

   foreach($contacts as $contact) {

     $ID = $this->getContact($contact['CODE']);

     $arContact = array(
       "COMPANY_ID"  => $company_id,
       "NAME"        => $contact['Имя'],
       "LAST_NAME"   => $contact['Фамилия'],
       "SECOND_NAME" => $contact['Отчество'],
       "UF_CRM_1534925895" => $contact['CODE'],
       "ASSIGNED_BY_ID"    => $contractor['ИДОтветственный']  ? : DEFAULT_ASSIGNED,
       "PHONE"             => $this->getContactPhone($contact['Телефон'], $ID),
       "UF_CRM_1536578561" => $this->encodePhone($contact['Телефон'], 'contact', $ID),
       "EMAIL"       => array(array("VALUE" => $this->emailParse($contact['Email']), 'VALUE_TYPE' => 'WORK')),
       "COMMENTS"    => $contact['Комментарий']
     );

     if(!$ID) {
      
        if(!$this->addContact($arContact)) {

           Logger::log(self::$CONTACT_ERRORS);

        }
        
     } else {

        if(!$this->updateContact($ID, $arContact)) {

           Logger::log(self::$CONTACT_ERRORS);

        }
       
     } 
   }
   
   $products = [];

   foreach($request['Товары'] as $k=>$item) {

     $products[] = array(
       "PRODUCT_NAME" => $item['Номенклатура'],
       "PRICE" =>        $item['Цена'],
       "QUANTITY" =>     $item['Количество']
     );

   }

   $ID = $this->add($arOrder);
   
   if($ID) {

    if($this->addProduct($ID, $products)) {

        return array("STATUS"=>"OK","ERRORS" => array_merge(self::$COMPANY_ERRORS, self::$CONTACT_ERRORS));

    }
    
    return array("ERROR" => self::$PRODUCT_ERRORS);

   }

   Logger::log(array($arOrder, self::$DEAL_ERRORS));

   return array("ERROR" => self::$DEAL_ERRORS);

  }

  return array("RESPONSE" => "$request[Номер] ORDER HAS EXISTS");

 }

 private function add(array $fields) {

  $data = array("fields" => $fields);

  $result = $this->request(self::$DEAL_ADD, $data);

  if(!$result['error_description']) {

     return $result;

  }

  self::$DEAL_ERRORS[] = $result['error_description'];

  return false;

 }

 private function updateStatus($request) {

  $data = array(
          "id"     => $this->getDealID($request['Номер']),
          "fields" => array(
              'STAGE_ID'       => $this->getStatus($request['Статус']), 
              'ASSIGNED_BY_ID' => $request['ИДОтветственный'],
              'UF_CRM_1537514183' => $request['Причина'],
              'COMMENTS'          => $request['Комментарий'] 
           ) 
  );

  $result = $this->request(self::$DEAL_UPDATE, $data);

  if(!$result['error_description']) {

      return true;

  }

  Logger::log("error update status: ".$result['error_description']);

  return $result['error_description'];
 
 }

 private function getDealID($code) {

  $queryData = array(
     "order"  => array("TITLE" => "DESC"),
     "filter" => array("TITLE" => $code),
     "select" => array("ID") 
  );

  $result = $this->request(self::$DEAL_LIST, $queryData);

  return array_pop($result)['ID'] ? : false;

 } 

 private function getStatus($stage) {

  return self::$MAP_STATUS[$stage] ? : 'NEW';

 }

 private function addProduct($deal_id, array $items) {

  $data = array(
    "id" =>   $deal_id,
    "rows" => $items
  );

  $result = $this->request(self::$PRODUCT_ADD, $data);

  if(!$result['error_description']) {

     return true;

  }

  self::$PRODUCT_ERRORS[] = $result['error_description'];

  return false;

 }
}