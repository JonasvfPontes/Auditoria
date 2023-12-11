<?php 
session_start();
$_SESSION['logado'] = false;
setcookie('Permanecer_Logado',true, time() - 60);//Permanecer Logado por até 12hrs
setcookie('Nome_usuario',strtolower($_POST['login']), time() -60);//Permanecer Logado por até 12hrs
setcookie('Usuario_Contador',strtolower($_POST['login']), time() -60);//Permanecer Logado por até 12hrs
session_destroy();
header("location: index.php");
?>