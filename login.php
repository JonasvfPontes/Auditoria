<?php 
include_once("conexao.php");
session_start();

$_SESSION['logado'] = $_SESSION['logado'] ?? null;
$_SESSION['aviso'] = "Balanço Dão Silveira!";

if($_SESSION['logado']){
    f_Ir_Para($_SESSION['NomeUsuario']);
}

if($_SERVER["REQUEST_METHOD"]==="POST"){//Verifica se houve envio do formulálrio
    $login = $_POST["login"];
    $senha = $_POST["senha"];

    if(empty($login) & empty($senha) ){

    }
    elseif(empty($login)){
        $_SESSION['aviso'] = "Deixasse de preencher teu login";
    }
    elseif(empty($senha)){
        $_SESSION['aviso'] = "Deixasse de preencher tua senha";
    }
    else{
        //Iniciar SELECT para retornar registros correspondentes com o login e senha
        //digitados pelo usuario
        $sqlCode = "SELECT * FROM usuario_e_senhas WHERE usuario = '$login' AND senha = '$senha'";
        $sqlQuery = $mysqli->query($sqlCode);

        if($sqlQuery->num_rows != 1){
            /*Se o número de registros for diferente de 1
              então está errado */
              $_SESSION['aviso'] = "Login ou senha incorretos ";
        }else{
            /**Iniciar uma SESSION */ 
            $_SESSION['logado'] = true;
            $_SESSION['NomeUsuario'] = $_POST['login'];
            f_Ir_Para($_SESSION['NomeUsuario']);
        }
    }
}

function f_Ir_Para($tipoUsuario){
    if( $tipoUsuario == 'contadores'){
        header("location: pageContadores.php");
    }else{
        header("location: main.php");
    }

}
?>