<?php
session_start();
if ($_SESSION['logado'] == false ?? null) {
    header("location: index.php");
} else {
    //Atribuir configurações do banco de dados à SESSION
    //--------------------------------------------------
    include_once('conexao.php');
    if (empty($_SESSION['separador'])) {
        //Se essa variável estiver vazia é porque a sessão está começando agora
        //então devo pegar as configurações necessárias
        $sqlCode = "SELECT valor FROM config WHERE parametro='separador'";
        $sqlQuery = $mysqli->query($sqlCode);
        if ($sqlQuery->num_rows > 0) {
            $row = $sqlQuery->fetch_assoc(); //pega os valores do resultado da query
            $_SESSION['separador'] = $row['valor'];
        }
        $sqlCode = "SELECT valor FROM config WHERE parametro='temcabecalho'";
        $sqlQuery = $mysqli->query($sqlCode);
        if ($sqlQuery->num_rows > 0) {
            $row = $sqlQuery->fetch_assoc(); //pega os valores do resultado da query
            $_SESSION['temcabecalho'] = $row['valor'];
        }
    }
    //--------------------------------------------------
    if (!isset($_SESSION['qtdeEquipes'])) {
        $sqlCode = "SELECT valor FROM config WHERE parametro='qtde_Equipes'";
        $sqlQuery = $mysqli->query($sqlCode);
        if ($sqlQuery->num_rows > 0) {
            $row = $sqlQuery->fetch_assoc(); //pega os valores do resultado da query
            $_SESSION['qtdeEquipes'] = $row['valor'];
        }
    }
    //--------------------------------------------------
    $_SESSION['enviar_lista0'] = $_SESSION['enviar_lista0'] ?? 'Enviar';
    for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) { //Atribuir uma SESSION para cada equipe
        //Se o botão estiver com o valor null ou 'Enviar'
        if (empty($_SESSION['enviar_lista' . $i])) {
            $_SESSION['enviar_lista' . $i] = "Enviar";
        }
    }
    //--------------------------------------------------
    if ($_SESSION['temcabecalho'] != null) { //Adicionar input da configuração do cabeçalho
        $checktemcabecalho = '<input type="checkbox" name="temcabecalho" id="" checked>';
        $_SESSION['aviso_checktemcabecalho'] = "Tem cabeçalho";
    } else {
        $checktemcabecalho = '<input type="checkbox" name="temcabecalho" id="">';
        $_SESSION['aviso_checktemcabecalho'] = "Não tem cabeçalho";
    }
    //--------------------------------------------------
    if (!isset($_SESSION['mensagem_Importacao'])) {
        $sqlCode = "SELECT * FROM estoque";
        $sqlQuery = $mysqli->query($sqlCode);
        if ($sqlQuery->num_rows != 0) {
            $_SESSION['mensagem_Importacao'] = 'Você possui '. $sqlQuery->num_rows .' linhas na tabela de estoque. <p><a href="limparBD.php">- Clique aqui -</a></p> para excluir a lista atual';
        }else{
            $_SESSION['mensagem_Importacao'] = '';

        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/styleMain.css">
    <title>Balanço Dão Silveira</title>
</head>

<body>
    <div class="larguraPageMain">
        <main id="paginaToda">
            <div class="TudoMenosFooter">
                <header id="cabecalho">
                    <img src="imgs\grp-daosilveira.png" alt="logo Dao Silveira" id="logoDaoSilveira">
                </header>
                <div class=configEquipes>
                    <form action="" method="post">
                        <label for="qtdeEquipes">Equipes</label>
                        <input type="number" name="qtdeEquipes" id="">
                        <button type="submit">Salvar</button>
                    </form>
                    <form action="processarArquivo.php" method="post" enctype="multipart/form-data">
                        <label>Estoque</label>
                        <input type="file" name="listaEquipe0" id="">
                        <button type="submit" name="enviar_equipe0"><?php echo $_SESSION['enviar_lista0'] ?></button>
                    </form>
                    <div class="mensagemImportacao">
                        <?php echo $_SESSION['mensagem_Importacao']; ?>
                    </div>
                </div>
                <form action="processarArquivo.php" method="post">
                    <section class="grade-equipes">
                        <?php
                        if (isset($_POST['qtdeEquipes'])) {
                            if ($_POST['qtdeEquipes'] > 0) {
                                $_SESSION['qtdeEquipes'] = $_POST['qtdeEquipes'];
                            }
                        }
                        //Crier essa SESSÃO lá no cabeçalho
                        if ($_SESSION['qtdeEquipes'] <= 0) {
                            echo '<div class="larguraPageMain">';
                            echo '<h1>Digite a quantidade de equipes que quer formar</h1>';
                            echo '</div">';
                        }

                        if ($_SESSION['qtdeEquipes'] > 0) {

                            if ($_SESSION['qtdeEquipes'] > 50) {
                                echo '<div class="larguraPageMain">';
                                echo '<h1>Você digitou um número muito grande!<br>O limite de equipes é 50 e você digitou ' . $_SESSION['qtdeEquipes'] . '</h1>';
                                echo '</div">';
                            } else {
                                for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) {
                                    $_SESSION['enviar_lista' . $i] = $_SESSION['enviar_lista' . $i] ?? 'Enviar';
                                    echo '<div class="equipe">';
                                    echo '<label>Equipe ' . $i . '</label>';
                                    echo '<div>';
                                    echo 'De:<input type="number" name="listaEquipe' . $i . '" id=""> ';
                                    echo 'Até:<input type="number" name="listaEquipe' . $i . '" id="">';
                                    echo '</div>';
                                    echo '<button type="submit" name="enviar_equipe' . $i . '">' . $_SESSION['enviar_lista' . $i] . '</button>';
                                    echo '</div>';
                                }
                            }

                            //salvar Qtde no banco de dados
                            include_once('conexao.php');
                            if (isset($_POST['qtdeEquipes'])) {
                                if (!empty($_POST['qtdeEquipes']) & $_POST['qtdeEquipes'] >= 0) {
                                    $novoValor = $_POST['qtdeEquipes'];
                                    $sqlCode = "UPDATE config SET valor = '$novoValor' WHERE parametro='qtde_Equipes'";
                                    $sqlQuery = $mysqli->query($sqlCode);
                                }
                            }
                        }
                        ?>
                    </section>
                </form>

                <div class="configFile-e-navegador">
                    <div class="configFile">
                        <form action="processarArquivo.php" method="post">
                            <label for="separador">Separado por <strong><?php echo '" ' . $_SESSION['separador'] . ' "' ?></strong></label>
                            <div class="separador-config">
                                <input type="text" name="separador" id="">
                                <button type="submit">Salvar</button>
                            </div>
                            <label for="temcabecalho"><?php echo $_SESSION['aviso_checktemcabecalho'] ?></label>
                            <div class="separador-config">
                                <?php echo $checktemcabecalho ?>
                                <button type="submit">Salvar</button>
                            </div>
                        </form>
                    </div>
                    <div>
                        <a href="pageContadores.php">
                            <button class="btnVerEquipes">Ver Equipes</button>
                        </a>
                    </div>
                </div>
            </div>

            <footer>
                <a href="logout.php">
                    <button id="btnSair">Sair</button>
                </a>
            </footer>
        </main>
    </div>
</body>

</html>