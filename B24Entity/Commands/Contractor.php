<?php
namespace B24Entity\Commands;

use \B24Entity\Commands\Command,
    \B24Entity\Helpers\Logger;

class Contractor extends Command  {

 use \B24Entity\Helpers\Contractor;

 public function execute($request) {

   if(!$request['Контрагент']) {

      return false;

   }

   if(defined('LOG_ENABLED') && LOG_ENABLED == 'Y') {
  
     Logger::log($request);

   }

   $contractor = $request['Контрагент']; 

   $company_id = $this->getCompanyID($contractor['CODE']);

   $arCompany = array(
     "TITLE" => $contractor['Название'], 
     "PHONE" => $this->getCompanyPhone($contractor['Телефон'], $company_id),
     "EMAIL" => $this->email($contractor['Email']),
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
   ); 

   if($company_id) {

     $arCompany["EMAIL"] = $this->getEntityEmail("company", $company_id, $contractor['Email']);
    
     if($this->updateCompany($company_id, $arCompany)) {

       $contacts = $request['Контрагент']['КонтактныеЛица']; 

       foreach($contacts as $contact) {

         $ID = $this->getContactID($contact['CODE']);

         $arContact = array(
           "COMPANY_ID"        => $company_id,
           "UF_CRM_1534925895" => $contact['CODE'],
           "NAME"              => $contact['Имя'],
           "LAST_NAME"         => $contact['Фамилия'],
           "SECOND_NAME"       => $contact['Отчество'],
           "POST"              => $contact['Должность'],
           "ASSIGNED_BY_ID"    => $contractor['ИДОтветственный'] ? : DEFAULT_ASSIGNED,
           "PHONE"             => $this->getContactPhone($contact['Телефон'], $ID),
           "UF_CRM_1536578561" => $this->encodePhone($contact['Телефон'],'contact', $ID),
           "EMAIL"             => $this->email($contact['Email']),
           "COMMENTS"          => $contact['Комментарий']
         );

         if(!$ID) {
      
            $this->addContact($arContact);
        
         } else {

           $arContact["EMAIL"] = $this->getEntityEmail("contact", $ID, $contact['Email']);
 
           $this->updateContact($ID,$arContact);
       
        } 
       }
      
       return array("RESPONSE_UPDATE" => "200","ERRORS" => array_merge(self::$COMPANY_ERRORS, self::$CONTACT_ERRORS));

    }
   
    return array("RESPONSE_ERROR" => self::$COMPANY_ERRORS);

   } else {

    $company_id = $this->addCompany($arCompany); 

    if($company_id) {

      $contacts = $request['Контрагент']['КонтактныеЛица']; 

      foreach($contacts as $contact) {

       $ID = $this->getContactID($contact['CODE']);

       $arContact = array(
          "COMPANY_ID"        => $company_id,
          "UF_CRM_1534925895" => $contact['CODE'],
          "NAME"              => $contact['Имя'],
          "LAST_NAME"         => $contact['Фамилия'],
          "SECOND_NAME"       => $contact['Отчество'],
          "POST"              => $contact['Должность'],
          "ASSIGNED_BY_ID"    => $contractor['ИДОтветственный']  ? : DEFAULT_ASSIGNED,
          "PHONE"             => $this->getContactPhone($contact['Телефон'], $ID),
          "UF_CRM_1536578561" => $this->encodePhone($contact['Телефон'],'contact', $ID),
          "EMAIL"             => $this->email($contact['Email']),
          "COMMENTS"          => $contact['Комментарий']
       );

       if(!$ID) {
      
        if(!$this->addContact($arContact)) {

           Logger::log(self::$CONTACT_ERRORS);

        }
          
       } else {

          $arContact["EMAIL"] = $this->getEntityEmail("contact", $ID, $contact['Email']);
        
          if(!$this->updateContact($ID, $arContact)) {

             Logger::log(self::$CONTACT_ERRORS);

          }   
       } 
     }
    
    return array("RESPONSE" => "200","ERRORS" => self::$CONTACT_ERRORS);

   }

   Logger::log(self::$COMPANY_ERRORS);

   return array("ERROR" => self::$COMPANY_ERRORS);

  }
 }
}