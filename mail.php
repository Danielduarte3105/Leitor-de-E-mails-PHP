<?php
// Define o caminho para o arquivo JSON
$jsonFilePath = 'procInt.json';

// Lê o conteúdo do arquivo JSON
$jsonContent = file_get_contents($jsonFilePath);
$data = json_decode($jsonContent, true);

// Verifica se a decodificação foi bem-sucedida
if ($data === null) {
    die('Erro ao decodificar o JSON.');
}

// Define o destinatário
$destinatarioFixo = 'daniel.silva@sfa.adv.br'; // Altere para o endereço de e-mail desejado

// Configurações do servidor de e-mail
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Seu Nome <danielartdesignofc@gmail.com>" . "\r\n"; // Altere para seu e-mail

// Percorre os itens do JSON e envia e-mails
foreach ($data as $id => $item) {
    // Verifica se o status é 'enviado'
    if (isset($item['status']) && $item['status'] === 'Enviado') {
        echo "E-mail já enviado para: $destinatarioFixo. Ignorando...\n";
        continue; // Ignora este item
    }

    // Adiciona o campo 'status'
    $item['status'] = 'Enviado'; // Define o status que você deseja adicionar

    // Configura o e-mail
    $to = $destinatarioFixo; // Usa o destinatário fixo definido no código
    $subject = $item['titulo'];
    $message = $item['mensagem'];

    // Envia o e-mail
    if (mail($to, $subject, $message, $headers)) {
        echo "E-mail enviado para: $to\n";
    } else {
        echo "Falha ao enviar e-mail para: $to\n";
    }

    // Atualiza o JSON com o novo campo 'status'
    $data[$id] = $item;
}

// Atualiza o arquivo JSON com os novos dados
file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT));
?>
