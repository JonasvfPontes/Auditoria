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
if ($sqlQuery->num_rows == 0){//Se o número de linhas for igual a 0, é porque o estoque está vazio
    echo "<script>alert('VOCÊ AINDA NÃO PODE CONFIGURAR OS NOMES DAS EQUIPES POIS SEU ESTOQUE AINDA ESTÁ VAZIO!')</script>";
}else{    
    //Verificar cada input se tem algo escrito,  se sim substituír nome da equipe com o que tiver no input e salvar no banco de dados
    for ($ii = 1; $ii <= $_SESSION['qtdeEquipes']; $ii++) {
        if (isset($_POST["Eqp" . $ii])) {
            if (strlen($_POST["Eqp" . $ii]) > 0) { //Se no imput atual tiver algum nome escrito
                $nomeAtualEquipe = $_SESSION['Nome_Equipe' . $ii];
                $novoValor = $_POST["Eqp" . $ii];
                $_SESSION['Nome_Equipe' . $ii] = $novoValor; //Substituir valor na SESSION pelo nome escrito
    
                //Verificar se ID já existe no banco de dados
                $sqlCode = "SELECT * FROM nome_equipes WHERE id='$ii'";
                $sqlQuery = $mysqli->query($sqlCode);
                if ($sqlQuery->num_rows > 0) { //Se numero de linhas for maior que 0, é porque o ID já existe
                    $sqlCode = "UPDATE nome_equipes SET Nome_Equipe = '$novoValor' WHERE id='$ii'"; //Atualizar nome atual no BD pelo valor atual no POST
                    $sqlQuery = $mysqli->query($sqlCode);
                } else { //Se o número de linhas for igual a 0, então incluir ID no BD já com o nome atual no POST
                    $sqlCode = "INSERT INTO nome_equipes (id, Nome_Equipe) VALUES ($ii, '$novoValor')";
                    $sqlQuery = $mysqli->query($sqlCode);
                }
    
                //Atualizar nome equipe no BD Estoque
                $sqlCode = "UPDATE estoque SET nome_Equipe = '$novoValor' WHERE nome_Equipe = '$nomeAtualEquipe'"; //Atualizar nome atual no BD pelo valor atual no POST
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
    <title>Nomear Equipes</title>
</head>

<body>
    <div class="paginaToda">
        <header><a href="main.php"><button>Voltar</button></a></header>
        <form action="" method="post">
            <section>
                <?php
                for ($i = 1; $i <= $_SESSION['qtdeEquipes']; $i++) {
                    echo '<div class="eqp">';
                    if ($_SESSION['Nome_Equipe' . $i] == ('Equipe ' . $i)) {
                        $lista = explode(" ", $_SESSION['Nome_Equipe' . $i]); //explode seria equivalente a um split
                        echo strtoupper($lista[0]) . ' ' . str_pad($lista[1], 2, '0', STR_PAD_LEFT) . ' <input type="text" name="Eqp' . $i . '" id="">';
                    } else {
                        echo str_pad(strtoupper($_SESSION['Nome_Equipe' . $i]), 9, "_") . ' <input type="text" name="Eqp' . $i . '" id="">';
                    }
                    echo '</div>';
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