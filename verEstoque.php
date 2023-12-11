<?php 
session_start();
$_SESSION['logado'] = $_SESSION['logado'] ?? null;
if (!$_SESSION['logado']){
    header('location: index.php');
}
?>
<!DOCTYPE html>
<html lang="pr-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0%;
            padding: 0%;
            font-family: 'Gabarito', sans-serif;
        }

        body {
            min-width: 98vw;
        }

        table {
            margin: auto;
            margin-top: 100px;

        }

        td {
            border: 1px solid;
            text-align: center;
            width: 10px;
            padding: 0px 5px;
        }

        td input {
            border: none;
            padding: 3px;
        }

        td input:focus {
            transform: scale(1.1);
        }

        header {
            position: fixed;
            display: flex;
            justify-content: space-around;
            background-color: rgb(127, 204, 165);
            padding: 20px;
            top: 0%;
            width: 100%;

        }

        form {
            display: flex;
            justify-content: space-around;
            width: 300px;
            border: 1px solid;
            padding: 5px;
            border-radius: 5px;
        }

        header input {
            height: 35px;
            border-radius: 5px;
            border: 1px solid;
        }

        header input:focus {
            transform: scale(1.2);
        }

        header button {
            padding: 0.5rem;
            border: 1px solid;
            border-radius: 5px;

        }

        header button:hover {
            transform: scale(1.1);
        }

        .estoque {
            padding: 20px;
            margin: 0%;
            max-width: 95vw;
        }

        .info {
            position: absolute;
            margin: auto;
            text-align: center;
        }
    </style>
    <title>Estoque</title>
</head>

<body>
    <header>
        <div>
            <form action="" method="post">
                <input type="search" name="filtroEstoque" id="">
                <button type="submit">Filtrar</button>
            </form>
        </div>
        <button><a href="limparBD.php">Excluir tudo</a></button>
    </header>
    <div class="estoque">
        <?php
        include_once('conexao.php');
        //Verificar se é  para liberar a adição de qtde manualmente
        $sqlCode1 = "SELECT valor FROM config WHERE parametro='alterar_estoque'";
        $valor = $mysqli->query($sqlCode1)->fetch_assoc();
        if ($valor['valor'] == 'on') {
            $addInclusao = true;
        } else {
            $addInclusao = false;
        }
        //---------------------------------------------------------
        //Verificar se é pra selecionar tudo ou só uma equipe pelo SELECT
        if (isset($_POST['filtroEstoque'])) {
            $sqlCode2 = "SELECT * FROM estoque WHERE nome_Equipe LIKE '%" . $_POST['filtroEstoque'] . "%' ORDER BY locacao ASC";
            $sqlQuery2 = $mysqli->query($sqlCode2);
            if ($sqlQuery2->num_rows == 0){//Se o retorno for 0, verificar se o que foi digitado é a referencia de uma peça
                $sqlCode2 = "SELECT * FROM estoque WHERE cod_Item LIKE '%" . $_POST['filtroEstoque'] . "%' ORDER BY locacao ASC";
                $sqlQuery2 = $mysqli->query($sqlCode2);
            }

        } else {
            $sqlCode2 = "SELECT * FROM estoque";
            $sqlQuery2 = $mysqli->query($sqlCode2);
        }
        //---------------------------------------------------------
        $informar = $sqlQuery2->num_rows . ' linhas resultantes';

        echo '<table style="font-size: 15px;">';
        echo '<tr>';
        echo '<th>Nome Equipe</th>';
        echo '<th>Linha</th>';
        echo '<th>Locação</th>';
        echo '<th>Código</th>';
        echo '<th>Descrição</th>';
        echo '<th>Qtd Estoque</th>';
        if ($addInclusao) {
            echo '<th>Adicinoar</th>';
        }
        echo '</tr>';
        while ($row = $sqlQuery2->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . strtoupper($row['nome_Equipe']) . '</td>';
            echo '<td>' . $row['id_item'] . '</td>';
            echo '<td>' . $row['locacao'] . '</td>';
            echo '<td>' . $row['cod_Item'] . '</td>';
            echo '<td style="width: auto;">' . $row['desc_Item'] . '</td>';
            echo '<td>' . $row['qtd_Estoque'] . '</td>';
            if ($addInclusao) {
                echo '<td><input style="width: 100px;" type="number" name="" id=""></td>';
            }
            echo '</tr>';
        }
        ?>
        <div class="info">
            <?php echo $informar ?>
        </div>

    </div>
</body>

</html>