<?php

use LDAP\Result;

session_start();
//Alterar nome do botão para LISTA ENVIADA
$chavesPost = array_keys($_POST);

echo '<pre>';
var_dump($_POST);

if ($chavesPost[0] == 'separador') {
    //significa que nenhum POST foi enviado do primeiro form do main    
    include_once('conexao.php');

    //Salvar configuração do separador do arquivo no banco de dados
    if (!empty($_POST['separador'])) {
        $novoValor = $_POST['separador'];
        $sqlCode = "UPDATE config SET valor = '$novoValor' WHERE parametro='separador'";
        $sqlQuery = $mysqli->query($sqlCode);
        $_SESSION['separador'] = $_POST['separador'];
    }

    //Salvar configuração do cabeçalho do arquivo no banco de dados
    $temcabecalho = $_POST['temcabecalho'] ?? null;
    if ($_SESSION['temcabecalho'] != $temcabecalho) {
        $novoValor = $_POST['temcabecalho'] ?? null;
        $sqlCode = "UPDATE config SET valor = '$novoValor' WHERE parametro='temcabecalho'";
        $sqlQuery = $mysqli->query($sqlCode);
        $_SESSION['temcabecalho'] = $novoValor;
    }
} elseif ($chavesPost[0] == 'CheckalterarQdeEstoque' || $chavesPost[0] == 'alterarQtdEstoque') {
    include_once('conexao.php');

    //Salvar configuração do modo de visualização do estoque
    $_POST['CheckalterarQdeEstoque'] = $_POST['CheckalterarQdeEstoque'] ?? null;
    if ($_POST['CheckalterarQdeEstoque'] != null) {
        $novoValor = $_POST['CheckalterarQdeEstoque'];
        $sqlCode = "UPDATE config SET valor = '$novoValor' WHERE parametro='alterar_estoque'";
        $sqlQuery = $mysqli->query($sqlCode);
        $_SESSION['mensagemAlterarQtdEstoque'] = '| Alterar quantidade <form action="processarArquivo.php" method="post"><input type="checkbox" class="alterarQdeEstoque" name="CheckalterarQdeEstoque" id="" checked><button type="submit" name="alterarQtdEstoque" >Salvar</button></form>';
        $_SESSION['AlterarQtdEstoque'] = true;
    } else {
        $novoValor = null;
        $sqlCode = "UPDATE config SET valor = '$novoValor' WHERE parametro='alterar_estoque'";
        $sqlQuery = $mysqli->query($sqlCode);
        $_SESSION['mensagemAlterarQtdEstoque'] = '| Não alterar quantidade <form action="processarArquivo.php" method="post"><input type="checkbox" class="alterarQdeEstoque" name="CheckalterarQdeEstoque" id=""><button type="submit" name="alterarQtdEstoque" >Salvar</button></form>';
    }
} elseif ($chavesPost[0] == 'enviar_equipe0') { //Equipe0 é a Lista do Estoque, siginifica que acabei de enviar um estoque novo
    $indiceLista = "listaEquipe0";
    $lista = $_FILES[$indiceLista]; //cada input de cada equipe no form é um indice de um dicinário no PHP
    //------------------------------------------------------------------
    if (!empty($lista['name'])) { //Verifica se a lista não está vazia
        if ($lista['type'] != 'text/csv') { //Verifica se a lista não é do tipo text/CSV
            $style = '<p style="background-color: rgb(192, 37, 37); Color: white;">Apenas arquivo txt/csv</p>';
            $_SESSION['enviar_lista' . preg_replace("/[^0-9]+/", "", $chavesPost[0])] = $style; //retorna uma mensagem de erro ao usuario
        } else {
            //Verificar se precisa ignorar a primeira linha caso houver cabeçalho
            if ($_SESSION['aviso_checktemcabecalho'] == "Tem cabeçalho") {
                $pularLinha = true;
            } else {
                $pularLinha = false;
            }

            //Lendo dados
            $linhas = fopen($lista['tmp_name'], "r");
            $dados = array();
            //Ordenar dados do menor para o maior considerando a locação
            //----------------------------------------------------------------------
            while ($linha = fgetcsv($linhas, 0, $_SESSION['separador'])) {
                if ($pularLinha) {
                    //pular linha recebe false para não pular nos próximos registros
                    $pularLinha = false;
                } else {
                    $dados[] = $linha;
                }
            }
            fclose($linhas);
            function compararPorLocacao($a, $b)
            {
                $locacaoA = $a[2]; // Supondo que o campo 'Locação' está na terceira posição (índice 2).
                $locacaoB = $b[2]; // Supondo que o campo 'Locação' está na terceira posição (índice 2).
                return strcmp($locacaoA, $locacaoB);
            }
            usort($dados, 'compararPorLocacao'); // Ordenar o array de acordo com o campo 'Locação'
            //----------------------------------------------------------------------
            //----------------------------------------------------------------------
            include_once('conexao.php');
            //Primeiro limpar os dados que estiverem no banco de dados que forem da equipe atual
            $sqlCode = "DELETE FROM estoque WHERE nome_Equipe = '" . $indiceLista . "'";
            $sqlQuery = $mysqli->query($sqlCode);
            $linhas_Importadas = 0;
            $linhas_Nao_Importadas = 0;
            $sqlCode = "INSERT INTO estoque (cod_Item, desc_Item, locacao, qtd_Estoque, qtd_Contada, nome_Equipe) VALUES (?, ?, ?, ?, ?, ?)";
            foreach ($dados as $linha) {
                //Verificar se vou precisar converter em UTF-8                
                //----------------------------------------------------------------------
                $codItem = $linha[0] ?? NULL;
                $descItem = $linha[1] ?? NULL;
                $locacao = $linha[2] ?? NULL;
                $qtd_Estoque = $linha[3] ?? NULL;
                $qtd_Contada = NULL;

                $sqlQuery = $mysqli->prepare($sqlCode);
                $sqlQuery->bind_param('sssiis', $codItem, $descItem, $locacao, $qtd_Estoque, $qtd_Contada, $indiceLista);
                if ($sqlQuery->execute()) {
                    $linhas_Importadas++;
                } else {
                    $linhas_Nao_Importadas++;
                }
            }
            //----------------------------------------------------------------------
            //mensagem de Sucesso
            if ($_SESSION['AlterarQtdEstoque']) {
                //Alterar qtde do estoque Ativo
                $_SESSION['mensagemAlterarQtdEstoque'] = '| Alterar quantidade <form action="processarArquivo.php" method="post"><input type="checkbox" class="alterarQdeEstoque" name="CheckalterarQdeEstoque" id="" checked><button type="submit" name="alterarQtdEstoque" >Salvar</button></form>';
            } else {
                //Alterar qtde do estoque Inativo
                $_SESSION['mensagemAlterarQtdEstoque'] = '| Não alterar quantidade <form action="processarArquivo.php" method="post"><input type="checkbox" class="alterarQdeEstoque" name="CheckalterarQdeEstoque" id=""><button type="submit" name="alterarQtdEstoque" >Salvar</button></form>';
            }
            $_SESSION['mensagem_Importacao'] = $linhas_Importadas . ' linhas importadas e ' . $linhas_Nao_Importadas . ' não importadas. <p><a href="VerEstoque.php" target="_blank">(- Clique aqui -)</a></p> para ver a lista ';
            $style = '<p style="background-color: rgb(238, 236, 86);">Lista enviada!</p>';
            $_SESSION['enviar_lista0'] = 'Lista enviada!'; //Deixa apenas os números
            //. preg_replace("/[^0-9]+/", "", $chavesPost[0])
        }
    }
} else { //Esse trecho é a parte quando o ADM envia as locações de cada equipe 'De' e 'Até'
    echo '<br><br>';
    print_r($_SESSION);
    for ($i = 0; $i <= $_SESSION['qtdeEquipes']; $i++) {
        if (isset($_POST['btn_equipe' . $i])) { //Procurando qual lista foi enviada no POST
            $numEquipe = $i;
            $nome_Equipe = 'listaEquipe' . $numEquipe; //Nome dos campos que veio no POST
        }
    }
    //Verificar se algum dos dois campos de locação está vazio
    if (!strlen($_POST[$nome_Equipe . 'A']) > 0 || !strlen($_POST[$nome_Equipe . 'B']) > 0) {
        if (strlen($_POST[$nome_Equipe . 'A']) == 0 && strlen($_POST[$nome_Equipe . 'B']) == 0) {
            //caso não tenha nada nos campos, não fazer nada
        } 
        else {
            $_SESSION['enviar_lista' . $numEquipe] = '<p style="color: RED;"><strong>FALTA INFORMAÇÃO</strong></p>';
        }
    } elseif ($_POST[$nome_Equipe . 'B'] < $_POST[$nome_Equipe . 'A']) {
        $_SESSION['enviar_lista' . $numEquipe] = '<p style="color: RED;"><strong>"Até" menor que "De"</strong></p>';
    } else { //fazer update do banco de dados
        include_once('conexao.php');
        $numLinhasJaUsadas = 0;
        if ($_POST[$nome_Equipe . 'A'] == 0 && $_POST[$nome_Equipe . 'B'] == 0) {
            //zerar todas as locações com o nome da dupla
            $sqlCode = "UPDATE estoque SET nome_Equipe = 'listaEquipe0' WHERE nome_Equipe = '" . $_SESSION['Nome_Equipe' . $numEquipe] . "'";
            $_SESSION['enviar_lista' . $numEquipe] = '<p style="color: GREEN;"><strong>Qtde de linhas (' . $numLinhas . ')</strong></p>';
        } else {
            //atualizar estoque com o nome da equipe que foi enviado no POST com o valor da SESSION
            $sqlCode = "UPDATE estoque SET nome_Equipe = '" . $_SESSION['Nome_Equipe' . $numEquipe] . "' WHERE locacao >= '" . $_POST[$nome_Equipe . "A"] . "' AND locacao <='" . $_POST[$nome_Equipe . "B"] . "' AND nome_equipe = 'listaEquipe0' ";

            //Verificar se tinha alguma linhas já em uso por outra equipe
            $sqlCodeComparar = "SELECT * FROM estoque WHERE locacao >= '" . $_POST[$nome_Equipe . "A"] . "' AND locacao <='" . $_POST[$nome_Equipe . "B"] . "' AND nome_equipe <> 'listaEquipe0' ";
            $sqlQueryComparar = $mysqli->query($sqlCodeComparar);
            if ($sqlQueryComparar->num_rows > 0) {
                $numLinhasJaUsadas = $sqlQueryComparar->num_rows;
            }
        }
        $sqlQuery = $mysqli->query($sqlCode);
        //Pegar qtde de linhas dessa equipe
        $sqlCode = "SELECT * FROM estoque WHERE nome_Equipe = '" . $_SESSION['Nome_Equipe' . $numEquipe] . "'";
        $sqlQuery = $mysqli->query($sqlCode);
        $numLinhas = $sqlQuery->num_rows;

        if ($numLinhasJaUsadas > 0) {
            $_SESSION['enviar_lista' . $numEquipe] = '<p style="color: RED;"><strong>' . $numLinhasJaUsadas . ' linhas já em uso (' . $numLinhas . ')</strong></p>';
        } else {
            $_SESSION['enviar_lista' . $numEquipe] = '<p style="color: GREEN;"><strong>Qtde de linhas (' . $numLinhas . ')</strong></p>';
        }
    }
}

echo '</pre>';
header('location: main.php');
