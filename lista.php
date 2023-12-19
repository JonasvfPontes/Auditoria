<?php
session_start();
if ($_SESSION['logado'] == false ?? null) {
    header("location: index.php");
}

//Tratamento dos dados enviados no POST
$vazios = 0;
include_once('conexao.php');

//ATRIBUIR TRUE ENQUANTO AINDA HOUVER PEÇAS NÃO AUDITADAS NA LISTA ATUAL
$sqlCode = "SELECT valor FROM config WHERE parametro='num_contagem'";
$sqlQuery = $mysqli->query($sqlCode);
$row = $sqlQuery->fetch_assoc();
$_SESSION['num contagem atual'] = $row['valor'];

$ColunmContagem = "contagem_" . $_SESSION['num contagem atual'];
$sqlCode = "SELECT lista_atual FROM estoque WHERE lista_atual = 'SIM' AND $ColunmContagem IS NULL";
$sqlQuery = $mysqli->query($sqlCode);
if ($sqlQuery->num_rows > 0) {
    //se for maior que zero, significa que ainda há peças com valos NULL no estoque
    $_SESSION['permitirAlteracao'] = true;
} else {
    //Significa que toas as peças foram auditadas e não há mais NULL na contagem atual
    $_SESSION['permitirAlteracao'] = false;
}
//---------------------------------------------------------------------------

if (isset($_POST['btn_SALVAR']) && $_SESSION['permitirAlteracao']) {
    foreach ($_POST as $codigo => $qtde) {
        if (!empty($qtde) && $codigo != 'btn_SALVAR') { //Verifica se tem algum valor não vazio
            $ColunmContagem = "contagem_" . $_SESSION['num contagem atual'];
            $colunmNomeEquipe = "nome_contagem_" . $_SESSION['num contagem atual'];
            $nomeEquipe = $_SESSION['Nome Equipe Atual'];
            $sqlCode = "UPDATE estoque SET $ColunmContagem = $qtde, qtd_auditada = $qtde, $colunmNomeEquipe = '$nomeEquipe' WHERE cod_Item = '$codigo'";
            $sqlQuery = $mysqli->query($sqlCode);

            //Verificar se o item ainda deve sair na próxima lista
            $ColunmContagemAnterior = "contagem_" . $_SESSION['num contagem atual'] - 1;
            $sqlCode = "SELECT * FROM estoque WHERE cod_Item = '$codigo'";
            $row = $mysqli->query($sqlCode)->fetch_assoc();

            if (($_SESSION['num contagem atual'] == 1 || $_SESSION['num contagem atual'] == 2)  && ($row['qtd_Estoque'] == $qtde)) {
                //Se for a primeiro ou segunda contagem e bater com o estoque, não sair na proxima lista
                //preencher na coluna 'continuar_saindo_na_lista' com 'NAO'
                $sqlCode = "UPDATE estoque SET continuar_saindo_na_lista = 'NAO' WHERE cod_Item = '$codigo'";
                $mysqli->query($sqlCode);
            } elseif ($_SESSION['num contagem atual'] >= 3 && ($row['qtd_Estoque'] == $qtde || $row[$ColunmContagemAnterior] == $qtde)) {
                //Se a contagem atual bater com o estoque ou bater com a contagem anterior
                //preencher campo 'continuar_saindo_na_lista' com NAO
                $sqlCode = "UPDATE estoque SET continuar_saindo_na_lista = 'NAO' WHERE cod_Item = '$codigo'";
                $mysqli->query($sqlCode);
            } else {
                //se nada bater então configurar peça para sair na próxima lista
                //essa parte é importante porque o contador pode já ter salvado uma quantidade correta e depois ter
                //encontrado a mesma peça em outro local, sendo assim o campo 'continuar_saindo_na_lista' deve voltar 
                //a ter o valor de 'SIM'
                $sqlCode = "UPDATE estoque SET continuar_saindo_na_lista = 'SIM' WHERE cod_Item = '$codigo'";
                $mysqli->query($sqlCode);
            }
        }
    }
    //Atualizat cabeçalho
    $emoticons = ['🙂', '🙈', '😄', '😜', '🤗', '😘', '👍', '🤙', '😎', '👌', '👽'];
    $texto = $_SESSION['cabecalho da lista'];
    while ($_SESSION['cabecalho da lista'] == $texto) {
        $index_emoticon = random_int(0, 10);
        $texto = '<h2>' . $_SESSION['num contagem atual'] . "ª Contagem " . $emoticons[$index_emoticon] . "</h2>";
    }
    $_SESSION['cabecalho da lista'] = $texto;
    //---------------------------------------------------------
    
    //conferir se esse envio finalizou a contagem atual
    $ColunmContagem = "contagem_" . $_SESSION['num contagem atual'];
    $sqlCode = "SELECT lista_atual FROM estoque WHERE lista_atual = 'SIM' AND $ColunmContagem IS NULL";
    $sqlQuery = $mysqli->query($sqlCode);
    if ($sqlQuery->num_rows > 0) {
        //se for maior que zero, significa que ainda há peças com valos NULL no estoque
        $_SESSION['permitirAlteracao'] = true;
    } else {
        //Significa que toas as peças foram auditadas e não há mais NULL na contagem atual
        $_SESSION['permitirAlteracao'] = false;
    }
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
        <div id="mensagem" style="display: none;">Sucesso!</div>
        <form action="" method="post">
            <?php

            //Verificar se a contagem foi liberada pelo usuario adm
            $sqlCode = "SELECT valor FROM config WHERE parametro='contando'";
            $sqlQuery = $mysqli->query($sqlCode);
            $row = $sqlQuery->fetch_assoc(); //pega os valores do resultado da query
            if ($row['valor'] == 0) {
                echo '<div class="aviso-de-lista-vazia">';
                echo '<br>Calma meu anjo, ainda não está na hora<br><br>';
                echo '<img src="imgs/Calma.png" class="imgCalma" alt="lista-vazia">';
                echo '</div>';
            } else {
                $sqlCode = "SELECT valor FROM config WHERE parametro='num_contagem'";
                $sqlQuery = $mysqli->query($sqlCode);
                $row = $sqlQuery->fetch_assoc();
                $_SESSION['num contagem atual'] = $row['valor'];



                $chavePost = array_keys($_POST); //Verifica qual equipe foi enviada no POST
                if ($_SESSION['Nome Equipe Atual'] == null) {
                    $_SESSION['Nome Equipe Atual'] = $_SESSION['Nome_Equipe' . substr($chavePost[0], -1)];
                    $nomeEquipe = $_SESSION['Nome Equipe Atual']; //Pegar o valor da SESSION referente ao número do POST que foi enviado

                    //pegar ID usuario
                    $sqlCode1 = "SELECT * FROM nome_equipes WHERE Nome_Equipe='$nomeEquipe'";
                    $_SESSION['ID Lista Atual'] = $mysqli->query($sqlCode1)->fetch_assoc()['id'];
                } else {
                    $nomeEquipe = $_SESSION['Nome Equipe Atual'];
                }

                $sqlCode = "SELECT * FROM estoque  WHERE nome_Equipe = '$nomeEquipe' AND lista_atual = 'SIM'";
                $sqlQuery = $mysqli->query($sqlCode);
                $sqlCode1 = "SELECT valor FROM config WHERE parametro='visualizar_qtde'";
                $valor = $mysqli->query($sqlCode1)->fetch_assoc();
                if ($valor['valor'] == 'on') {
                    $visualizarQtde = true;
                } else {
                    $visualizarQtde = false;
                }

                //montando tabela com as informações do BD
                if ($sqlQuery->num_rows > 0) {
                    echo $_SESSION['cabecalho da lista'];

                    //Verificar se o usuário é CONTADOR e se está na lista correta, se sim, mostrar botão salvar
                    if ($_SESSION['usuario contador']) {
                        if ($_SESSION['ID Usuario Logado'] == $_SESSION['ID Lista Atual'] && $_SESSION['permitirAlteracao']) {
                            echo '<input type="submit" class="btn_terminei" name="btn_SALVAR" value="Salvar 💾">';
                        } elseif (!$_SESSION['permitirAlteracao']) {
                            echo '<h3 style="color: green;">[FIM DESSA CONTAGEM]</h3>';
                        } else {
                            echo '<h3>[Essa não é a sua lista  😓 ]</h3>';
                        }
                    } else {
                        echo '<br><h3>[Você não é um contador 😉 ]<h3>';
                    }

                    echo '<table style="font-size: 12px;">';
                    echo '<tr>';
                    echo '<th>Linha</th>';
                    echo '<th>Código</th>';
                    echo '<th>Descrição</th>';
                    echo '<th>Locação</th>';
                    if ($visualizarQtde) {
                        echo '<th>Qtd Estoque</th>';
                    }
                    echo '<th>Qtd Contada</th>';
                    echo '</tr>';

                    $linha = 1;
                    while ($row = $sqlQuery->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $linha . '</td>';
                        echo '<td>' . $row['cod_Item'] . '</td>';
                        echo '<td>' . $row['desc_Item'] . '</td>';
                        echo '<td>' . $row['locacao'] . '</td>';
                        if ($visualizarQtde) {
                            echo '<td>' . $row['qtd_Estoque'] . '</td>';
                        }
                        $qtde = $row['contagem_' . $_SESSION['num contagem atual']];
                        echo '<td><input type="number" value="' . $qtde . '" name="' . $row['cod_Item'] . '" onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="inputCelula"></td>';
                        echo '</tr>';
                        $linha++;
                    }
                    echo '</table>';
                    echo "</p>";
                } else {
                    //lista vazia
                    echo '<div class="aviso-de-lista-vazia">';
                    echo '<br>Não há nada aqui <br>...<br>';
                    echo '<img src="imgs/sozinho.png" class="imgSozinho" alt="lista-vazia">';
                    echo '</div>';
                }
            }
            ?>
        </form>
    </main>
    <footer>
        <a href="pageContadores.php">
            <input type="button" value="Voltar">
        </a>
    </footer>
</body>

</html>