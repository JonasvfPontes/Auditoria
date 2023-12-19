<?php
session_start();
$_SESSION['logado'] = $_SESSION['logado'] ?? null;
if (!$_SESSION['logado']) {
    header('location: index.php');
}
?>
<!DOCTYPE html>
<html lang="pr-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="CSS/styleVerEstoque.css">
    <title>Estoque</title>
</head>

<body>
    <header>
        <div>
            <form action="baixarCsv.php" method="post">
                <button type="submit" style="background-color: rgb(48, 130, 84); color: white; " name="exportar">Exportar csv</button>
            </form>
        </div>
        <div>
            <form action="" method="post">
                <input type="search" name="filtroEstoque" id="">
                <button type="submit">Filtrar</button>
            </form>
        </div>
        <div>
            <form action="" method="post">
                Itens
                <button type="submit" name="naoContados">Não contados</button>
                <button type="submit" name="naProximaLista">Na proxima lista</button>
                <button type="submit" name="inserirItem">Inserir item</button>
            </form>
        </div>
        <button style="background-color: rgb(200, 20, 84); color: white; "><a href="limparBD.php" style="text-decoration: none; color: inherit;">Excluir tudo</a></button>
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

        //Pegar numero da contagem atual
        $sqlCode1 = "SELECT valor FROM config WHERE parametro='num_contagem'";
        $valor = $mysqli->query($sqlCode1)->fetch_assoc();
        $contagem_atual = $valor['valor'];
        $colContagemAtual = 'contagem_' . $contagem_atual;
        //---------------------------------------------------------
        //CONFIGURAR SELECTs DO BANCO DE DADOS
        if (isset($_POST['filtroEstoque'])) {
            $sqlCode2 = "SELECT * FROM estoque WHERE nome_Equipe LIKE '%" . $_POST['filtroEstoque'] . "%' ORDER BY locacao ASC";
            $sqlQuery2 = $mysqli->query($sqlCode2);
            if ($sqlQuery2->num_rows == 0) { //Se o retorno for 0, verificar se o que foi digitado é a referencia de uma peça
                $sqlCode2 = "SELECT * FROM estoque WHERE cod_Item LIKE '%" . $_POST['filtroEstoque'] . "%' ORDER BY locacao ASC";
                $sqlQuery2 = $mysqli->query($sqlCode2);
            }
            $_SESSION['query ver Estoque'] = $sqlCode2;
        } elseif (isset($_POST['naoContados']) && $contagem_atual > 0) {
            $sqlCode2 = "SELECT * FROM estoque WHERE lista_atual = 'SIM' AND $colContagemAtual IS NULL ORDER BY locacao ASC";
            $sqlQuery2 = $mysqli->query($sqlCode2);
            $_SESSION['query ver Estoque'] = $sqlCode2;

        } elseif (isset($_POST['naProximaLista']) && $contagem_atual > 0) {
            $sqlCode2 = "SELECT * FROM estoque WHERE continuar_saindo_na_lista = 'SIM' AND $colContagemAtual IS NOT NULL ORDER BY locacao ASC";
            $sqlQuery2 = $mysqli->query($sqlCode2);
            $_SESSION['query ver Estoque'] = $sqlCode2;

        } elseif (isset($_POST['inserirItem'])) {
            header('location: form_insert_estoque.php');

        } elseif (isset($_POST['alterar_qtde'])) {
            //Percorrer POST procurando itens valor em 'Adicionar' e atualizar na contagem atual
            $chavesPost = array_keys($_POST);
            foreach ($_POST as $condigoItem => $qtde) {
                if (is_numeric($qtde) && $qtde > 0) {                   
                    // Usando prepared statements para evitar injeção de SQL
                    $sqlCode = "UPDATE estoque SET $colContagemAtual = $qtde, qtd_auditada = $qtde WHERE cod_Item = $condigoItem";
                    $sqlQuery = $mysqli->query($sqlCode);
                }
            }
            $sqlCode2 = $_SESSION['query ver Estoque'];
            $sqlQuery2 = $mysqli->query($sqlCode2);
        } else {
            $sqlCode2 = "SELECT * FROM estoque";
            $sqlQuery2 = $mysqli->query($sqlCode2);
            $_SESSION['query ver Estoque'] = $sqlCode2;
        }
        //---------------------------------------------------------
        $informar = $sqlQuery2->num_rows . ' linhas resultantes';
        echo '<form action="" method="post" class="form_estoque">';
        echo '<table style="font-size: 15px;">';
        echo '<tr>';
        echo '<th style="width: 100px;">Nome Equipe</th>';
        echo '<th>Linha</th>';
        echo '<th>Locação</th>';
        echo '<th>Código</th>';
        echo '<th>Descrição</th>';
        echo '<th>Qtd Estoque</th>';
        echo '<th>Qtd Auditada</th>';
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
            echo '<td>' . $row['qtd_auditada'] . '</td>';
            if ($addInclusao) {
                echo '<td><input style="width: 100px;" type="number" name="' . $row['cod_Item'] . '" id=""></td>';
            }
            echo '</tr>';
        }
        echo '<button type="submit" class="alterar_qtde" name="alterar_qtde"> Alterar quantidade</button>';
        echo '</form>';
        ?>
        <div class="info">
            <?php echo $informar ?>
        </div>
    </div>
</body>

</html>