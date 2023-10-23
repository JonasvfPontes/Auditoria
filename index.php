<?php 
include('login.php');

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
                <button type="submit" class="btn-enviar"><b>Entrar</b></button>
                <br><div class="aviso"> <?php echo $_SESSION['aviso'] ?> </div>
            </div>
        </form>
</body>
</html>