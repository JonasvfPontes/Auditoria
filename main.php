<?php
session_start();
if (isset($_COOKIE['Usuario_Contador']) || !isset($_COOKIE['Permanecer_Logado'])) {
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
        include_once('conexao.php');
        $sqlCode = "SELECT * FROM estoque";
        $sqlQuery = $mysqli->query($sqlCode);

        $sqlCode1 = "SELECT valor FROM config WHERE parametro='alterar_estoque'";
        $valor = $mysqli->query($sqlCode1)->fetch_assoc();
        if ($valor['valor'] == 'on') {
            //Alterar qtde do estoque Ativo
            $_SESSION['AlterarQtdEstoque'] = true;
            $_SESSION['mensagemAlterarQtdEstoque'] = '| Alterar quantidade <form action="processarArquivo.php" method="post"><input type="checkbox" class="alterarQdeEstoque" name="CheckalterarQdeEstoque" id="" checked><button type="submit" name="alterarQtdEstoque" >Salvar</button></form>';
        } else {
            //Alterar qtde do estoque Inativo
            $_SESSION['AlterarQtdEstoque'] = false;
            $_SESSION['mensagemAlterarQtdEstoque'] = '| Não alterar quantidade <form action="processarArquivo.php" method="post"><input type="checkbox" class="alterarQdeEstoque" name="CheckalterarQdeEstoque" id=""><button type="submit" name="alterarQtdEstoque" >Salvar</button></form>';
        }

        if ($sqlQuery->num_rows != 0) {
            //Estoque com informações salvas
            $_SESSION['mensagem_Importacao'] = 'Você possui ' . $sqlQuery->num_rows . ' registros no estoque. <p><a href="VerEstoque.php" target="_blank">&nbsp Clique aqui &nbsp</a></p> para ver a lista ';
        } else {
            //Estoque vazio, não mostrar nenhuma mensagem
            $_SESSION['mensagem_Importacao'] = '';
            $_SESSION['mensagemAlterarQtdEstoque'] = '';
        }
    }
    //--------------------------------------------------
    if (!isset($_SESSION['Visualizar Qtde'])) {
        include_once('conexao.php');
        $sqlCode = "SELECT valor FROM config WHERE parametro='visualizar_qtde'";
        $valor = $mysqli->query($sqlCode)->fetch_assoc();
        if ($valor['valor'] == 'on') {
            $_SESSION['Visualizar Qtde'] = "Visualizar Qtde: (Sim)";
            $_SESSION['CheckBox Visualizar qtde'] = '<input type="checkbox" name="visualizarQtde" id="" checked>';
        } else {
            $_SESSION['Visualizar Qtde'] = "Visualizar Qtde: (Não)";
            $_SESSION['CheckBox Visualizar qtde'] = '<input type="checkbox" name="visualizarQtde" id="">';
        }
    }
    //--------------------------------------------------

    //Conferir se o estoque está vazio
    $sqlCode = "SELECT cod_Item FROM estoque";
    if ($mysqli->query($sqlCode)->num_rows == 0) {
        $_SESSION['aviso contagem'] = null;
    } else {

        //Conferir se todas as peças estão Ok, ou seja, fim do balanço
        $sqlCode = "SELECT cod_Item FROM estoque WHERE continuar_saindo_na_lista = 'SIM'";
        if ($mysqli->query($sqlCode)->num_rows == 0) {
            //todas as peças já bateram com o estoque e o balanço acabou
            $_SESSION['aviso contagem'] = '<strong>[--FIM DO BALANÇO--]</strong>';
        } else {
            //Atribuir contagem atual À SESSION (quantas contagem nós já fizemos no estoque)
            $sqlCode = "SELECT valor FROM config WHERE parametro='num_contagem'";
            $sqlQuery = $mysqli->query($sqlCode);
            $row = $sqlQuery->fetch_assoc(); //pega os valores do resultado da query
            $_SESSION['num contagem atual'] = $row['valor'];

            //Verificar se tem equipes realizando alguma contagem agora
            $sqlCode = "SELECT valor FROM config WHERE parametro='contando'";
            $sqlQuery = $mysqli->query($sqlCode);
            $row = $sqlQuery->fetch_assoc(); //pega os valores do resultado da query
            if ($row['valor'] == 0) { //Siginifica que não há contagem no momento
                $_SESSION['aviso contagem'] = "Liberar contagem &nbsp" . ($_SESSION['num contagem atual'] + 1) . '?&nbsp<button type="submit" name="btn_LiberarContagem">Sim</button>'; //numero da próxima contagem

            } else { //Ainda há grupos realizando contagens no momento
                //conferir se todas as peças já receberam a quantidade, caso já tenham terminado
                //devo atualizar a tabela config
                $nomeColuna = "contagem_" . $_SESSION['num contagem atual'];
                //Selecionar todos os itens da coluna da contagem atual que não foram contados e ainda não estão Ok
                $sqlCode = "SELECT cod_Item FROM estoque WHERE $nomeColuna IS NULL AND lista_atual = 'SIM'";
                $sqlQuery = $mysqli->query($sqlCode);
                if ($sqlQuery->num_rows == 0) {
                    //Todas as peças das listas atuais já foram contadas
                    $_SESSION['aviso contagem'] = "Liberar contagem &nbsp" . ($_SESSION['num contagem atual'] + 1) . '?&nbsp<button type="submit" name="btn_LiberarContagem">Sim</button>'; //numero da próxima contagem
                    $sqlCode = "UPDATE config SET valor = 0 WHERE parametro='contando'";
                    $sqlQuery = $mysqli->query($sqlCode);
                } else {
                    //Pegar quantidade de itens na contagem atual
                    $sqlCode = "SELECT cod_Item FROM estoque WHERE lista_atual = 'SIM'";
                    $qtd_itens_lista_atual = $mysqli->query($sqlCode)->num_rows;

                    //Pegar todas as peças que já foram contadas da lista atual
                    $sqlCode = "SELECT cod_Item FROM estoque WHERE lista_atual = 'SIM' AND $nomeColuna IS NOT NULL";
                    $qtd_itens_ja_contados = $mysqli->query($sqlCode)->num_rows;

                    //Pegar quantidade de itens contados com suceeso na contagem atual (REGRAS ESTÃO NA PAGINA 'LISTAS.php')
                    $sqlCode = "SELECT cod_Item FROM estoque WHERE lista_atual = 'SIM' AND continuar_saindo_na_lista = 'NAO'";
                    $qtd_sucesso = $mysqli->query($sqlCode)->num_rows;


                    $sqlCode = "SELECT cod_Item FROM estoque";
                    $qtd_Contada = $mysqli->query($sqlCode)->num_rows;
                    $info_contagem = '<br>Itens lista Atual: ' . $qtd_itens_lista_atual . ' | Já contados: ' . $qtd_itens_ja_contados . ' | Sucesso: ' . $qtd_sucesso . ' | Falta contar: ' . $qtd_itens_lista_atual - $qtd_itens_ja_contados . ' | Itens Proxima lista: ' . $qtd_itens_lista_atual - $qtd_sucesso - ($qtd_itens_lista_atual - $qtd_itens_ja_contados);
                    $_SESSION['aviso contagem'] = "<strong>[AGUARDANDO O FINAL DA " . $_SESSION['num contagem atual'] . "ª CONTAGEM]</strong>" .  $info_contagem;
                }
            }
        }
    }

    //--------------------------------------------------
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
                    <div class="confi-equipes">
                        <button style="margin: 0px 0px 10px 0px;" onclick="intrucoes()">Instruções</button>
                        <script>
                            function intrucoes() {
                                // Define o conteúdo a ser exibido no pop-up
                                const conteudo = '<h1>Intruções de distribuição das listas</h1><p>Se quiser zerar as locações de alguma equipe, digite 0 em "De" e "Até" da equipe desejada</p>'

                                // Calcula as coordenadas do ponto central da tela
                                const larguraJanela = 500;
                                const alturaJanela = 300;
                                const esquerda = (screen.width - larguraJanela) / 2;
                                const topo = (screen.height - alturaJanela) / 2;
                                // Abre o pop-up no centro da tela
                                const janelaPopup = window.open('', 'NomeJanela', `width=${larguraJanela},height=${alturaJanela},left=${esquerda},top=${topo}`);

                                // Verifica se a janela foi aberta com sucesso
                                if (janelaPopup) {
                                    // Adiciona o conteúdo personalizado ao pop-up
                                    janelaPopup.document.body.innerHTML = conteudo;
                                }
                            }
                        </script>
                        <a href="VisaoEquipes.php"><button type="button">Renomear equipes</button></a>
                        <form action="" method="post">
                            <label for="qtdeEquipes">Num equipes</label>
                            <input type="number" name="qtdeEquipes" id="">
                            <button type="submit">Salvar</button>
                        </form>
                        <form action="VisaoEquipes.php" method="post"><label for="visualizarQtde"><?php echo $_SESSION['Visualizar Qtde'] ?></label><?php echo $_SESSION['CheckBox Visualizar qtde']; ?> <button type="submit" name="btn_VerQtde">Salvar</button></form>
                    </div>
                    ---------------------------------------------------------------------------------
                    <form action="VisaoEquipes.php" method="post"><?php echo $_SESSION['aviso contagem'] ?></form>
                    ---------------------------------------------------------------------------------
                    <form action="processarArquivo.php" method="post" enctype="multipart/form-data">
                        <label>Estoque</label>
                        <input type="file" name="listaEquipe0" id="">
                        <button type="submit" name="enviar_equipe0"><?php echo $_SESSION['enviar_lista0'] ?></button>
                    </form>
                    <div class="mensagemImportacao">
                        <?php echo $_SESSION['mensagem_Importacao'] . $_SESSION['mensagemAlterarQtdEstoque']; ?>
                    </div>
                </div>
                <form action="processarArquivo.php" method="post">
                    <section class="grade-equipes">
                        <?php
                        if (isset($_POST['qtdeEquipes'])) {
                            if ($_POST['qtdeEquipes'] > 0 && $_POST['qtdeEquipes'] <=50){
                                $_SESSION['qtdeEquipes'] = $_POST['qtdeEquipes'];
                            }else{
                                $_SESSION['qtdeEquipes'] = 51;
                            }
                        }
                        //Criei essa SESSÃO lá no cabeçalho
                        if ($_SESSION['qtdeEquipes'] <= 0) {
                            echo '<div class="larguraPageMain">';
                            echo '<h1>Digite a quantidade de equipes que quer formar</h1>';
                            echo '</div">';
                        } elseif ($_SESSION['qtdeEquipes'] > 0) {
                            if ($_SESSION['qtdeEquipes'] > 50) {
                                echo '<div class="larguraPageMain">';
                                echo '<h1>Digite um número de 1 à 50!<br>Me ajude!</h1>';
                                echo '</div">';
                            } else {
                                for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) {

                                    $sqlCode = "SELECT * FROM nome_equipes WHERE id='$i'";
                                    $sqlQuery = $mysqli->query($sqlCode);
                                    if ($sqlQuery->num_rows > 0) {
                                        $NomeEquipe = $sqlQuery->fetch_assoc()['Nome_Equipe'];
                                        $_SESSION['Nome_Equipe' . $i] = $NomeEquipe;
                                    } else {
                                        $_SESSION['Nome_Equipe' . $i] = 'Equipe ' . $i;
                                    }
                                    $_SESSION['enviar_lista' . $i] = $_SESSION['enviar_lista' . $i] ?? 'Enviar';

                                    //Criando painel de cada equipe
                                    echo '<div class="equipe">';
                                    echo '<label>' . strtoupper($_SESSION['Nome_Equipe' . $i]) . '</label>';
                                    echo '<div>';
                                    echo 'De:<input type="text" name="listaEquipe' . $i . 'A" id="" autocomplete="off"> ';
                                    echo 'Até:<input type="text" name="listaEquipe' . $i . 'B" id="" autocomplete="off">';
                                    echo '</div>';
                                    echo '<button type="submit" name="btn_equipe' . $i . '">' . $_SESSION['enviar_lista' . $i] . '</button>';
                                    echo '</div>';
                                    //-----------------------------
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