<?php
session_start();
if ($_SESSION['logado'] == false ?? null) {
    header("location: index.php");
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
                    //na página main eu criei primeiro o HTML, mas essa página criei com loop no PHP
                    //para treinar e tentar deixar mais dinamico a criação independente da quantidade de equipes
                    if (isset($_SESSION['qtdeEquipes'])) {
                        for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) {
                            echo '<button type="submit" name=listaEquipe' . $i . ' class="btnListas">Lista Equipe ' . $i . '</button>';
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
            if ($_SESSION['NomeUsuario'] == 'contadores') {
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