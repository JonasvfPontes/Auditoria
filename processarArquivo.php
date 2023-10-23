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
} else {
    if (isset($_POST['enviar_equipe0'])) { //Equipe0 é a Lista do Estoque
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
                $_SESSION['mensagem_Importacao'] = $linhas_Importadas . ' linhas importadas e ' . $linhas_Nao_Importadas . ' não importadas. <p><a href="VerEstoque.php" target="_blank">(- Clique aqui -)</a></p> para ver a lista';
                $style = '<p style="background-color: rgb(238, 236, 86);">Lista enviada!</p>';
                $_SESSION['enviar_lista0'] = $style; //Deixa apenas os números
                //. preg_replace("/[^0-9]+/", "", $chavesPost[0])
            }
        }
    }
}

echo '</pre>';
header('location: main.php');
