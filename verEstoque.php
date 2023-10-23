<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/styleLista.css">
    <title>Estoque</title>
</head>

<body>
    <div class="estoque">
        <header>
            <button><a href="limparBD.php">Excluir tudo</a></button>
        </header>

        <?php
        include_once('conexao.php');

        $sqlCode = "SELECT * FROM estoque";
        $sqlQuery = $mysqli->query($sqlCode);
        echo '<table style="font-size: 15px;">';
        echo '<tr>';
        echo '<th>Linha</th>';
        echo '<th>Locação</th>';
        echo '<th>Código</th>';
        echo '<th style="width: 500px;" >Descrição</th>';
        echo '<th>Qtd Estoque</th>';
        echo '</tr>';
        while ($row = $sqlQuery->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id_item'] . '</td>';
            echo '<td>' . $row['locacao'] . '</td>';
            echo '<td>' . $row['cod_Item'] . '</td>';
            echo '<td>' . $row['desc_Item'] . '</td>';
            echo '<td>' . $row['qtd_Estoque'] . '</td>';
            echo '</tr>';
        }
        ?>
    </div>
</body>

</html>