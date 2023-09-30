<?php 
    include("conexao.php");

    $aviso = "Balanço Dão Silveira!";
    if($_SERVER["REQUEST_METHOD"]==="POST"){//Verifica se houve envio do formulálrio
        $login = $_POST["login"];
        $senha = $_POST["senha"];

        if(empty($login) & empty($senha) ){

        }
        elseif(empty($login)){
            $aviso = "Deixasse de preencher teu login";
        }
        elseif(empty($senha)){
            $aviso = "Deixasse de preencher tua senha";
        }
        else{
            $sqlCode = "SELECT * FROM usuario_e_senhas WHERE usuario = '$login' AND senha = '$senha'";
            $sqlQuery = $mysqli->query($sqlCode);

            if($sqlQuery->num_rows != 1){
                /*Se o número de registros for diferente de 1
                  então está errado */
                $aviso = "Login ou Senha errados " . $sqlQuery->num_rows; 
            }else{
                /**Iniciar uma SESSION */ 
                $aviso = "Rapaz, parece que deu certo";
                
            }
        }
    }        
?>

<!DOCTYPE html>
<html lang="pr-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balanço Dão Silveira</title>
    <link rel="stylesheet" href="CSS/styleLogin.css">
    
</head>
<body>
    <img src="imgs/familia.png" alt="" id="imagem">
    <form action="" method="POST">
        <div class="painel-login">
            <div class="painel-login-input">
                <label for="login">Login: </label>
                <input type="text" name="login" id="">
            </div>
            <div class="painel-login-input">
                <label for="senha">Senha: </label>
                <input type="password" name="senha" id="">
            </div>
            <button type="submit" class="btn-enviar"><b>Entrar</b></button><br>
            <div class="aviso"> <?php echo $aviso ?> </div>
        </div>
    </form>
</body>
</html>