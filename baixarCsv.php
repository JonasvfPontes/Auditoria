<?php
session_start();
include_once('conexao.php');

//Exportar query
$sqlCode2 = $_SESSION['query ver Estoque'];
$sqlQuery2 = $mysqli->query($sqlCode2);

if ($sqlQuery2) {
    // Cria um arquivo temporário
    $nomeArquivoDownload = 'balanco_' . date("Y_m") . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $nomeArquivoDownload);
    $dados = fopen("php://output", "w");

    // Escrever cabeçalho
    $query = "SHOW COLUMNS FROM estoque";
    $result = $mysqli->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $header[] = $row['Field'];
        }
        $result->free();
    }

    fputcsv($dados, $header);

    //Escrever as linhas resultantes da query
    while ($row = $sqlQuery2->fetch_assoc()) {
        fputcsv($dados, $row);
    }
    fclose($dados);
}
