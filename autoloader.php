<?php
spl_autoload_register(function($class) {

$class = str_replace("\\","/",$class);

$path = $_SERVER['DOCUMENT_ROOT']."/{$class}.php";

if(file_exists($path)) {
   
   require_once $path;

} else {
    
   throw new Exception("file $path not found");

}
});