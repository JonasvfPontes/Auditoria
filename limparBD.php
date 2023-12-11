    <?php
    session_start();

    if (isset($_POST['sim'])) {
        //Limpar tabela estoque no banco de dados
        include('conexao.php');
        $sqlCode = "TRUNCATE TABLE estoque";
        $sqlQuery = $mysqli->query($sqlCode);
        $_SESSION['mensagem_Importacao'] = 'Sucesso, tabela limpa!';
        $_SESSION['enviar_lista0'] = 'Enviar';
        $_SESSION['mensagemAlterarQtdEstoque'] = '';
        $sqlCode = "TRUNCATE TABLE nome_equipes";
        $sqlQuery = $mysqli->query($sqlCode);
        header('location: main.php');
    } elseif (isset($_POST['nao'])) {
        header('location: VerEstoque.php');
    }

    ?>
    <!DOCTYPE html>
    <html lang="pt-bt">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Gabarito:wght@600&family=Karla&family=Red+Hat+Display:wght@900&display=swap');
            *{
                font-family: 'Karla', sans-serif;
            }
            .pergutaConfimacao {
                display: flex;
                flex-direction: column;
                width: 90vw;
                height: 90vh;
                margin: auto;
                border: 1px solid;
                border-radius: 5px;
                background: linear-gradient(0deg, rgba(150, 25, 40, 1) 0%, rgba(255, 255, 255, 1) 62%);
                text-align: center;
                justify-content: center;
                max-width: 1300px;
            }
            .sim{
                padding: 5px 10px;
                border-radius: 5px;
                border: 1px solid;
                margin: 50px;
                background-color: rgba(150, 25, 40, 1);
                color: white;
            }
            .nao{
                padding: 30px 60px;
                border-radius: 5px;
                border: 1px solid;
                font-size: larger;
                margin: 50px;

            }
            .pergutaConfimacao input:hover{
                transform: scale(1.1);
            }
            .imagem {
                width: 15%;
            }
        </style>
        <title>Estoque</title>
    </head>

    <body>
        <div class="pergutaConfimacao">
            <div>
                <h1>Você tem certeza que deseja apagar os dados do Estoque?</h1>
                ________________________________________________________________
                <h3>Isso vai apagar todas as configurações que você fez até agora como,<br>
                    divisões das equipes e os nomes de cada uma</h3>
                <form action="" method="post">
                    <input type="submit" class="nao" name="nao" value="Não">
                    <input type="submit" class="sim" name="sim" value="Sim">
                </form>
            </div>
            <div>
                <img src="imgs/lixo.png" class="imagem" alt="">
            </div>
        </div>
    </body>

    </html>