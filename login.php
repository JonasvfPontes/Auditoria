<?php
include_once("conexao.php");
session_start();

//verificar cookie

if (isset($_COOKIE['Permanecer_Logado'])) {
    $_SESSION['logado'] = true;
    $_SESSION['NomeUsuario'] = $_COOKIE['Nome_usuario'];


    if (isset($_COOKIE['Usuario_Contador'])) {
        header('location: pageContadores.php');
    } else {
        header('location: main.php');
    }
} else {

    $_SESSION['logado'] = $_SESSION['logado'] ?? null;
    $_SESSION['aviso'] = "Balanço Dão Silveira!";

    if ($_SERVER["REQUEST_METHOD"] === "POST") { //Verifica se houve envio do formulálrio
        $login = $_POST["login"];
        $senha = $_POST["senha"];

        if (empty($login) & empty($senha)) {
        } elseif (empty($login)) {
            $_SESSION['aviso'] = "Deixasse de preencher teu login";
        } elseif (empty($senha)) {
            $_SESSION['aviso'] = "Deixasse de preencher tua senha";
        } else {
            //Iniciar SELECT para retornar registros correspondentes com o login e senha
            //digitados pelo usuario
            $sqlCode = "SELECT * FROM usuario_e_senhas WHERE usuario = '$login' AND senha = '$senha'";
            $sqlQuery = $mysqli->query($sqlCode);

            if ($sqlQuery->num_rows != 1) {
                /*Se o número de registros for diferente de 1
              então está errado */
                $_SESSION['aviso'] = "Login ou senha incorretos ";
            } else {
                /**Iniciar uma SESSION */
                setcookie('Permanecer_Logado', true, time() + 3600 * 12); //Permanecer Logado por até 12hrs
                setcookie('Nome_usuario', strtolower($_POST['login']), time() + 3600 * 12); //Permanecer Logado por até 12hrs
                
                $_SESSION['logado'] = true;
                $_SESSION['NomeUsuario'] = strtolower($_POST['login']);
                $tipoContador = f_Ir_Para($_SESSION['NomeUsuario']);
                if ($tipoContador) {
                    setcookie('Usuario_Contador', true, time()+ 3600*12);
                    header('location: pageContadores.php');
                } else {
                    setcookie('Usuario_Contador', false, time()+ 3600*12);
                    header('location: main.php');
                }
            }
        }
    }

}

function f_Ir_Para($tipoUsuario)
{
    $usuario = str_replace(" ", "", strtolower($tipoUsuario));
    $_SESSION['NomeUsuario'] = $usuario;
    if ($usuario == 'contadores') {
        return true;
    } else {
        return false;
    }
}
