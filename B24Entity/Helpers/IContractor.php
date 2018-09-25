<?php
 namespace B24Entity\Helpers;

 interface IContractor {

    const PHONE_TYPES = ['Рабочий телефон контактного лица контрагента'   => 'WORK', 
                         'Мобильный телефон контактного лица контрагента' => 'MOBILE',
                         'Телефон контрагента' => 'WORK',
                         'Телефон контрагента добавочный' => 'WORK',
                         'Телефон для СМС оповещения' => 'WORK'
                       ];

    const PHONE_ENTITY_MAP = ["company" => "UF_CRM_1536562700",
                              "contact" => "UF_CRM_1536578561"
                             ];
 }
?>
