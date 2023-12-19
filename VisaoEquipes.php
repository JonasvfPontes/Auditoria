<?php
session_start();
if ($_SESSION['logado'] == false ?? null) {
    header("location: index.php");
}

include_once('conexao.php');
if (isset($_POST['btn_VerQtde'])) { //Configurar visão das listas das equipes
    if ($_POST['visualizarQtde'] == 'on') {
        $novoValor = 'on';
        $_SESSION['Visualizar Qtde'] = "Visualizar Qtde: (Sim)";
        $_SESSION['CheckBox Visualizar qtde'] = '<input type="checkbox" name="visualizarQtde" id="" checked>';
    } else {
        $novoValor = '';
        $_SESSION['Visualizar Qtde'] = "Visualizar Qtde: (Não)";
        $_SESSION['CheckBox Visualizar qtde'] = '<input type="checkbox" name="visualizarQtde" id="">';
    }
    //Salvar alteração no banco de dados
    $sqlCode = "UPDATE config SET valor = '$novoValor' WHERE parametro='visualizar_qtde'";
    $sqlQuery = $mysqli->query($sqlCode);
    header('location: main.php');
}

//Verificar se tem algo no estoque, se não, informar que essa ação não será válida já que não há nada para ser atualizado 
//no banco de dados
$sqlCode = "SELECT * FROM estoque";
$sqlQuery = $mysqli->query($sqlCode);
if ($sqlQuery->num_rows == 0) { //Se o número de linhas for igual a 0, é porque o estoque está vazio
    echo "<script>alert('VOCÊ AINDA NÃO PODE CONFIGURAR OS NOMES DAS EQUIPES OU LIBERAR CONTAGENS POIS SEU ESTOQUE AINDA ESTÁ VAZIO!')</script>";
} else {
    //Verificar cada input se tem algo escrito,  se sim substituír nome da equipe com o que tiver no input e salvar no banco de dados
    for ($ii = 1; $ii <= $_SESSION['qtdeEquipes']; $ii++) {
        if (isset($_POST["Eqp" . $ii])) {
            if (strlen($_POST["Eqp" . $ii]) > 0) { //Se no input atual tiver algum nome escrito
                $nomeAtualEquipe = $_SESSION['Nome_Equipe' . $ii];
                $novoNome = $_POST["Eqp" . $ii];
                $_SESSION['Nome_Equipe' . $ii] = $novoNome; //Substituir valor na SESSION pelo nome escrito

                //Verificar se ID já existe no banco de dados
                $sqlCode = "SELECT * FROM nome_equipes WHERE id='$ii'";
                $sqlQuery = $mysqli->query($sqlCode);
                if ($sqlQuery->num_rows > 0) { //Se numero de linhas for maior que 0, é porque o ID já existe
                    $sqlCode = "UPDATE nome_equipes SET Nome_Equipe = '$novoNome' WHERE id='$ii'"; //Atualizar nome atual no BD pelo valor atual no POST
                    $sqlQuery = $mysqli->query($sqlCode);
                    
                } else { //Se o número de linhas for igual a 0, então incluir ID no BD já com o nome atual no POST
                    $senha = bin2hex(random_bytes(2));
                    $sqlCode = "INSERT INTO nome_equipes (id, Nome_Equipe, senha) VALUES ($ii, '$novoNome', '$senha')";
                    $sqlQuery = $mysqli->query($sqlCode);
                }

                //Atualizar nome equipe no BD Estoque
                $sqlCode = "UPDATE estoque SET nome_Equipe = '$novoNome' WHERE nome_Equipe = '$nomeAtualEquipe'"; //Atualizar nome atual no BD pelo valor atual no POST
                $sqlQuery = $mysqli->query($sqlCode);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/styleNomearEquipes.css">
    <title>Configurações</title>
</head>

<body>
    <div class="paginaToda">
        <header><a href="main.php"><button>Voltar</button></a></header>
        <form action="" method="post">
            <section>
                <?php
                //var_dump($_POST);
                if (isset($_POST['btn_LiberarContagem']) && $sqlQuery->num_rows > 0) {
                    echo '<DIV style="text-align: center;">';
                    echo '<h2>Após iniciar as contagens você deve esperar até que todas as equipes terminem de contar,<br><br>Posso liberar as listas?</h2>';
                    echo '<form action="" method="post"><button type="submit" name="btn_sim" style="padding:0.5rem 1rem;">Sim</button></form>';
                    echo '</DIV>';
                    exit;
                } elseif (isset($_POST["btn_sim"])) {
                    //atualizar campo 'lista_atual' para 'NAO' caso a contagem tenha obtido sucesso
                    $sqlCode = "UPDATE estoque SET lista_atual='NAO' WHERE continuar_saindo_na_lista = 'NAO'"; //Mudar contando para 1
                    $sqlQuery = $mysqli->query($sqlCode);

                    $sqlCode = "UPDATE config SET valor=1 WHERE parametro = 'contando'"; //Mudar contando para 1
                    $sqlQuery = $mysqli->query($sqlCode);

                    $_SESSION['num contagem atual'] = $_SESSION['num contagem atual'] + 1; //Mudar num_contagem para a contagem atual
                    $sqlCode = "UPDATE config SET valor=" . $_SESSION['num contagem atual'] . " WHERE parametro = 'num_contagem'";
                    $sqlQuery = $mysqli->query($sqlCode);

                    $nomeColunm = "contagem_" . $_SESSION['num contagem atual'];
                    $nomeEquipeColunm = "nome_contagem_" . $_SESSION['num contagem atual'];
                    $sqlCode = "ALTER TABLE estoque ADD $nomeColunm INT(10) NULL, ADD $nomeEquipeColunm CHAR(50) NULL"; //Incluir coluna que irá receber as quantidades e os nomes das equipes da contagem atual
                    $sqlQuery = $mysqli->query($sqlCode);
                    header('location: main.php');
                } else {
                    for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) {
                        echo '<div class="eqp">';
                        if ($_SESSION['Nome_Equipe' . $i] == ('Equipe ' . $i)) {
                            $lista = explode(" ", $_SESSION['Nome_Equipe' . $i]); //explode seria equivalente a um split
                            echo strtoupper($lista[0]) . ' ' . str_pad($lista[1], 2, '0', STR_PAD_LEFT) . ' <input type="text" autocomplete="off" name="Eqp' . $i . '" id="">';
                        } else {
                            //pegar senha do usuário
                            $sqlCode = "SELECT senha FROM nome_equipes WHERE id='$i'"; //Atualizar nome atual no BD pelo valor atual no POST
                            $sqlQuery = $mysqli->query($sqlCode)->fetch_assoc();
                            $senha = $sqlQuery['senha'];
                            echo strtoupper($_SESSION['Nome_Equipe' . $i]. ' |Senha: ' . $senha ). ' <input type="text" name="Eqp' . $i . '" id="">';
                        }
                        echo '</div>';
                    }
                }
                ?>
            </section>
            <div class="salvar">
                <button type="submit">Salvar Tudo</button>
            </div>
        </form>
    </div>
</body>

</html>