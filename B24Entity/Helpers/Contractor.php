<?php
 namespace B24Entity\Helpers;

 use \B24Entity\Helpers\IContractor;

 trait Contractor {
 
 private static $COMPANY_ADD     = 'crm.company.add.json';

 private static $COMPANY_LIST    = 'crm.company.list.json';

 private static $COMPANY_UPDATE  = 'crm.company.update.json';
 
 private static $CONTACT_ADD     = 'crm.contact.add.json';

 private static $CONTACT_LIST    = 'crm.contact.list.json';

 private static $CONTACT_UPDATE  = 'crm.contact.update.json';

 private static $CONTACT_ERRORS  = [];

 private static $COMPANY_ERRORS  = [];

 private static $NOT_VALID_CHARS = [" ",":",";",","];
 
 private function addCompany(array $fields) {

   $data = array(
     "fields"  => $fields
   );
 
   $result = $this->request(self::$COMPANY_ADD, $data);

   if($result['error_description']) {

      self::$COMPANY_ERRORS[] = array($result['error_description'], $fields['TITLE']);

      return false;

   }

   return $result;
  
 }

 private function updateCompany($id, array $fields) {

   $data = array(
     "id"      => $id,
     "fields"  => $fields
   );

   $result = $this->request(self::$COMPANY_UPDATE, $data);

   if($result['error_description']) {
 
      self::$COMPANY_ERRORS[] = $result['error_description'];

      return false;

   }

   return true;

 }

 private function getCompanyID($code) {

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" => array("UF_CRM_1534925547" => $code), 
     "select" => array("ID") 
  );

  $result = $this->request(self::$COMPANY_LIST, $queryData);

  return array_pop($result)['ID'] ? : false;

 }

 private function addContact(array $fields) {

  $data = array(
     "fields"  => $fields
  );

  $result = $this->request(self::$CONTACT_ADD, $data);

  if($result['error_description']) {

     self::$CONTACT_ERRORS[] = $result['error_description'];

     return false;

  }

  return  true;
  
 }

 private function updateContact($id, array $fields) {

  $data = array(
     "id"      => $id,
     "fields"  => $fields
  );

  $result = $this->request(self::$CONTACT_UPDATE, $data);

  if($result['error_description']) {
 
     self::$CONTACT_ERRORS[] = $result['error_description'];

     return false;

  }

  return true;

 }

 private function getContactID($code) {

  $queryData = array(
     "order"  => array("UF_CRM_1534925895" => "DESC"),
     "filter" =>  array(
       "UF_CRM_1534925895" => $code
      ),
     "select" => array("ID") 
  );

  $result = array_pop($this->request(self::$CONTACT_LIST, $queryData));

  return $result['ID'] ? : false;

 }

 private function getCompanyPhone(array $phones, $entityID) {

  $new_phones = [];

  if(count($phones) == 0) {

     return $this->clearAllPhone('company', $entityID);

  }

  if(!$this->getAllEntityPhoneID('company', $entityID)) {

     $arCompany['PHONE'] = $this->clearAllPhone('company',$entityID);

     if($entityID) {

        $this->updateCompany($entityID, $arCompany);

     }
  }

  foreach($phones as $phone) {

    $type = IContractor::PHONE_TYPES[$phone['TYPE']];
 
    $phone_id = $entityID ? $this->getEntityPhoneID('company', $entityID, $phone['ID']) : false;

    if($phone_id) {
       
      $new_phones[] = array("ID" => $phone_id, "VALUE" => $this->phoneParse($phone['VALUE']), 'VALUE_TYPE' => $type);

    } else {

      $new_phones[] = array("VALUE" => $this->phoneParse($phone['VALUE']), 'VALUE_TYPE' => $type);

    }
   }

   return $new_phones;

 }

 private function getContactPhone(array $phones, $entityID) {

  $new_phones = [];

  if(count($phones) == 0) { 

     return $this->clearAllPhone('contact', $entityID);

  }

  if(!$this->getAllEntityPhoneID('contact', $entityID)) {

     $arContact['PHONE'] = $this->clearAllPhone('contact',$entityID);

     if($entityID) {

        $this->updateContact($entityID, $arContact);

     }
  }

  foreach($phones as $phone) {

    $type = IContractor::PHONE_TYPES[$phone['TYPE']];

    if($phone_id = $this->getPhoneType("contact", $entityID, $type)) {

       $new_phones[] = array("ID" => $phone_id, "VALUE" => $this->phoneParse($phone['VALUE']), 'VALUE_TYPE' => $type);

     } else {
     
       $new_phones[] = array("VALUE" => $this->phoneParse($phone['VALUE']), 'VALUE_TYPE' =>  $type);
           
     }  
  }

  return $new_phones;

 }

 private function clearAllPhone($entity, $entityID) {

  if(!$entityID) {
 
      return false;

  }

  $new_phones = [];

  foreach($this->getAllPhoneID($entity, $entityID) as $phone_id) {

     $new_phones[] = array("ID" => $phone_id, "VALUE" => '');

  }

  return $new_phones;

 }

 private function getEntityPhoneID($entity, $entityID, $phone_id) {

  $code = IContractor::PHONE_ENTITY_MAP[$entity];

  $alias_entity = $entity;

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" =>  array(
       "ID"   => $entityID
      ),
     "select" => array($code) 
  );

  $entity = strtoupper($entity);
  $entity.="_LIST";

  $result = array_pop($this->request(self::$$entity, $queryData));

  if(count($result[$code]) > 0) {

    foreach($result[$code] as $item) {

      $phone_entity = json_decode($item, 1);

      if($phone_entity['ID'] == $phone_id) {

        if($alias_entity == 'company') {
 
           return $this->getPhoneID($alias_entity, $entityID, false, $phone_entity['VALUE']);

        }

        return $this->getPhoneID($alias_entity, $entityID, $phone_entity['TYPE']);

      } 
    }
  } 

  return false;

 }

 private function getAllEntityPhoneID($entity, $entityID) {

  $code = IContractor::PHONE_ENTITY_MAP[$entity];

  $alias_entity = $entity;

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" =>  array(
       "ID"   => $entityID
      ),
     "select" => array($code) 
  );

  $entity = strtoupper($entity);
  $entity.="_LIST";

  $result = array_pop($this->request(self::$$entity, $queryData));

  return $result[$code];

 }

 private function getPhoneID($entity, $entityID, $number_type, $number = false) {

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" =>  array(
       "ID"   => $entityID
      ),
     "select" => array("PHONE") 
  );

  $entity = strtoupper($entity);
  $entity.="_LIST";

  $result = array_pop($this->request(self::$$entity, $queryData));

  foreach($result['PHONE'] as $item) {

    if($item['VALUE_TYPE'] == $number_type || $item['VALUE'] == $number) {

        return $item['ID'];

    } 

  }

  return false;

 }

 private function getAllPhoneID($entity, $entityID) {

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" =>  array(
       "ID"   => $entityID
      ),
     "select" => array("PHONE") 
  );

  $ids = [];

  $entity = strtoupper($entity);
  $entity.="_LIST";

  $result = array_pop($this->request(self::$$entity, $queryData));

  foreach($result['PHONE'] as $item) {

      $ids[] = $item['ID'];

  }

  return $ids;

 }
 
 private function getPhoneType($entity, $entityID, $phone_type) {

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" =>  array(
       "ID"   => $entityID
      ),
     "select" => array("PHONE") 
  );

  $entity = strtoupper($entity);
  $entity.="_LIST";

  $result = array_pop($this->request(self::$$entity, $queryData));

  foreach($result['PHONE'] as $item) {

    if($item['VALUE_TYPE'] == $phone_type) {

       return $item['ID'];

    } 

  }

  return  false;

 }

 private function encodePhone(array $phones, $entity, $entityID) {

  $arPhones = [];

  if(count($phones) == 0 && $entity && $entityID) { 

    foreach($this->getAllEntityPhoneID($entity, $entityID) as $field) {

       $arPhones[] = '';

    }

    return $arPhones;

  }

  foreach($phones as $phone) {

    $arPhones[] = json_encode(array(
                     "ID"    => $phone['ID'],
                     "VALUE" => $this->phoneParse($phone['VALUE']), 
                     "TYPE"  => IContractor::PHONE_TYPES[$phone["TYPE"]]
                   ) 
                  );

  }
  
  return $arPhones;

 }

 private function emailParse($email) {

  $email = trim($email);
  $email = str_replace(self::$NOT_VALID_CHARS,"", $email);

  if($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

     return '';

  }

  return $email;

 } 

 private function email($email, $type = 'WORK') {

   return array(array("VALUE" => $this->emailParse($email), 'VALUE_TYPE' => $type));

 }

 private function getEmailID($entity, $entityID) {

  $queryData = array(
     "order"  => array("ID" => "DESC"),
     "filter" =>  array(
       "ID"    => $entityID
      ),
     "select" => array("EMAIL") 
  );

  $entity = strtoupper($entity);
  $entity.="_LIST";

  $result = $this->request(self::$$entity, $queryData);

  return array_pop($result)['EMAIL'][0]['ID'] ? : false;

 }

 private function getEntityEmail($entity, $id, $email) {

  return array(array("ID" => $this->getEmailID($entity, $id), "VALUE" => $this->emailParse($email)));
  
 }

 private function phoneParse($phone) { 
  
  $phone = trim($phone);
  $phone = str_replace(self::$NOT_VALID_CHARS,"", $phone);

  return $phone ? : '';

 }
} 