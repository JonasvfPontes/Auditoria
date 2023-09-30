<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Balanço Dão Silveira</title>


</head>
<body>
    <?php 
        include("conexao.php")
    ?>
    <header id="cabecalho">
        <img src="imgs\grp-daosilveira.png" alt="logo Dao Silveira" id="logoDaoSilveira">
    </header>
    <form action="conexao.php" method="post">
        <section class="grade-equipes">
            <div class="equipe">
                <label>Equipe 1</label>        
                <input type="file" name="listagem" id="">
            </div>
            <div class="equipe">
                <label>Equipe 2</label>        
                <input type="file" name="listagem" id="">
            </div>
            <div class="equipe">
                <label>Equipe 3</label>        
                <input type="file" name="listagem" id="">
            </div>
            <div class="equipe">
                <label>Equipe 4</label>        
                <input type="file" name="listagem" id="">
            </div>
        </section>
        <button type="submit" class="btnEnviar">Enviar</button>
    </form>
</body>
</html>
