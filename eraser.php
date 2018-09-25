<html>
<head>
<title>run</title>
</head>
<body>
идёт процесс ...
<script>
 /*
  entity : 
    company, contact, deal

*/
 var entity_obj = {
    entity : "contact",
 };

 fetch("1c_import.php?type=clear",{
  method : "POST",
  type : "json",
  body : JSON.stringify( entity_obj ) ,
  headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
  }

 }).then(function(response) {
         
  if(response.ok) {

     return {body : response.text(), headers : response.headers};
  }

  throw new Error('Network response was not valid.');

  }).then(function(response){
           
    if(response.body) {
       return response.body;
   }
}).then(function(value) {

  value = JSON.parse(value);

  if(value.RESPONSE) {
      location.reload();
  } else {

     document.write('очистка завершена, не найдены не корректные сущности crm');
 }
   
});      
</script>
</body>
</html>