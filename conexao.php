<?php
header("Content-type: text/html; charset=utf-8");
error_reporting(0); // Não reporta nenhum erro
ini_set('display_errors', 0); // Não exibe erros na tela

// Configurações da conexão
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'danielartdesignofc@gmail.com';
$password = 'qkpl rqct lebw twrc';

// Função para ler o JSON
function readJsonFile($filePath) {
    if (file_exists($filePath)) {
        return json_decode(file_get_contents($filePath), true);
    }
    return [];
}

// Lê o JSON com as informações das pastas
$jsonData = readJsonFile('items.json');

// Tentativa de conexão ao servidor IMAP
$inbox = imap_open($hostname, $username, $password) or die('Não foi possível conectar: ' . imap_last_error());

// Número total de e-mails na caixa de entrada
$emails = imap_search($inbox, 'ALL');

// Início do HTML
?>