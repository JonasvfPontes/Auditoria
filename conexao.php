<?php
// Informações de conexão com o banco de dados
$hostName = "localhost"; // Normalmente é "localhost" ou o endereço IP do servidor MySQL
$usuario = "root"; // Nome de usuário do MySQL
$senha = ""; // Senha do MySQL
$banco_de_dados = "usuarios"; // Nome do banco de dados que você deseja se conectar

// Criar uma conexão com o banco de dados
$mysqli = new mysqli($hostName, $usuario, $senha, $banco_de_dados);

// Verificar a conexão
if ($mysqli->connect_error) {
    die("Erro de conexão: " . $mysqli->connect_error);
}

?>