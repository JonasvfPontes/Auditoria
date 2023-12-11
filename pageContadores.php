<?php
session_start();
include_once('conexao.php');
if ($_SESSION['logado'] == false ?? null) {
    header("location: index.php");
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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Balanço Dão Silveira</title>
</head>

<body>
    <main id="paginaToda">
        <div class="TudoMenosFooter">
            <header id="cabecalho">
                <img src="imgs\grp-daosilveira.png" alt="logo Dao Silveira" id="logoDaoSilveira">
            </header>
            <form action="lista.php" method="post">
                <section class="grade-equipes">
                    <?php
                    if (isset($_SESSION['qtdeEquipes'])) {
                        for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) {
                            
                            $sqlCode = "SELECT *  FROM nome_equipes WHERE id='$i'";
                            $sqlQuery = $mysqli->query($sqlCode);
                            if($sqlQuery->num_rows > 0){
                                $NomeEquipe = $sqlQuery->fetch_assoc()['Nome_Equipe'];
                                $_SESSION['Nome_Equipe' . $i] = $NomeEquipe;
                            }else{
                                $_SESSION['Nome_Equipe' . $i] = 'Equipe '. $i;
                            }

                            echo '<button type="submit" name=listaEquipe' . $i . ' class="btnListas">' . strtoupper($_SESSION['Nome_Equipe' . $i]) . '</button>';
                        }
                    } else {
                        echo '<h1>Nenhuma equipe foi disponibilizada ainda</h1>';
                    }
                    ?>

                </section>
            </form>
        </div>

        <footer>
            <?php
            if (strtolower($_SESSION['NomeUsuario']) == 'contadores') {
                echo '<a href="logout.php">';
                echo '<button type="submit" id="btnSair">Sair</button>';
                echo '</a>';
            } else {
                echo '<a href="main.php">';
                echo '<button type="submit" id="btnSair">Voltar</button>';
                echo '</a>';
            }

            ?>
        </footer>
    </main>
</body>

</html>