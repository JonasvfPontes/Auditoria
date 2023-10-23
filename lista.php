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
    <title>Balanço Dão Silveira</title>
    <link rel="stylesheet" href="CSS/styleLista.css">
</head>

<body>
    <main>
        <?php
        include_once "conexao.php";
        $chavePost = array_keys($_POST);
        $nomeEquipe = '';
        $nomeEquipe = $chavePost[0];
        $sqlCode = "SELECT * FROM estoque  WHERE nome_Equipe = '$nomeEquipe'";
        $sqlQuery = $mysqli->query($sqlCode);


        //montando tabela com as informações do BD
        if ($sqlQuery->num_rows > 0) {
            echo '<table style="font-size: 12px;">';
            echo '<tr>';
            echo '<th>Linha</th>';
            echo '<th>Código</th>';
            echo '<th>Descrição</th>';
            echo '<th>Locação</th>';
            echo '<th>Qtd Estoque</th>';
            echo '<th>Qtd Contada</th>';
            echo '</tr>';
            
            $linha = 1;
            while ($row = $sqlQuery->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $linha . '</td>';
                echo '<td>' . $row['cod_Item'] . '</td>';
                echo '<td>' . $row['desc_Item'] . '</td>';
                echo '<td>' . $row['locacao'] . '</td>';
                echo '<td>' . $row['qtd_Estoque'] . '</td>';
                echo '<td><input type="number" onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="inputCelula"></td>';
                echo '</tr>';
                $linha++;
            }
            echo '</table>';
            echo "</p>";
        } else {
            echo '<div class="aviso-de-lista-vazia">';
            echo '<h1> </h1>';
            echo '<h2>Não há nada aqui</h2>';
            echo '<img src="imgs/sozinho.png" class="imgSozinho" alt="lista-vazia">';
            echo '</div>';
        }
        ?>
    </main>
    <footer>
        <a href="pageContadores.php">
            <input type="button" value="Voltar">
        </a>
    </footer>
</body>

</html>