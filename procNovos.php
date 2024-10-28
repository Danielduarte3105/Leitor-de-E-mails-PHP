<?php
// Configurações do IMAP
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'danielartdesignofc@gmail.com';
$password = 'qkpl rqct lebw twrc';

// Conecta ao servidor de e-mail
$inbox = imap_open($hostname, $username, $password) or die('Não foi possível conectar: ' . imap_last_error());

if (!$inbox) {
    die('Erro ao conectar ao IMAP: ' . imap_last_error());
}

// Busca todos os e-mails (removido o filtro de "UNSEEN")
$emails = imap_search($inbox, 'ALL'); // Modificado para buscar todos os e-mails

if ($emails === false) {
    die('Nenhum email encontrado, e/ou erro de busca: ' . imap_last_error());
}

if (empty($emails)) {
    echo 'Nenhum e-mail encontrado.';
    exit;
}

// Lê os itens existentes do arquivo items.json
$itensExistentes = [];
if (file_exists('items.json')) {
    $itensExistentes = json_decode(file_get_contents('items.json'), true);
}

// Cria um array para armazenar os processos novos
$novosProcessos = [];

// Lê os processos já salvos do arquivo procNovos.json, se existir
$processosSalvos = [];
if (file_exists('procNovos.json')) {
    $processosSalvos = json_decode(file_get_contents('procNovos.json'), true);
}

// Processa cada e-mail
foreach ($emails as $email_number) {
    // Lê as informações do e-mail
    $overview = imap_fetch_overview($inbox, $email_number, 0);
    $message = imap_fetchbody($inbox, $email_number, 1);
    $message = imap_utf8($message);

    // Extrai o número da pasta do título do e-mail
    preg_match('/\b(\d{4,7})\b/', $overview[0]->subject, $matches);
    $numeroPasta = isset($matches[1]) ? intval($matches[1]) : null;

    // Log do que está sendo processado
    echo "Processando e-mail: " . htmlspecialchars($overview[0]->subject) . "<br>";
    echo "Número da pasta: " . ($numeroPasta ? $numeroPasta : 'Nenhum número encontrado') . "<br>";

    // Verifica se o número da pasta está presente em items.json
    $processoExistente = false;
    if ($numeroPasta) {
        foreach ($itensExistentes as $item) {
            if (isset($item['PASTA_CLIENTE']) && intval($item['PASTA_CLIENTE']) === $numeroPasta) {
                $processoExistente = true;
                break;
            }
        }
    }

    // Se não for um processo existente, salva os dados
    if (!$processoExistente) {
        // Cria um hash md5 do título para evitar duplicatas
        $hashTitulo = md5($overview[0]->subject);
        
        // Verifica se o processo já foi salvo
        if (!array_key_exists($hashTitulo, $processosSalvos)) {
            // Salva os dados do processo no array
            $novosProcessos[$hashTitulo] = [
                'titulo' => $overview[0]->subject,
                'remetente' => $overview[0]->from,
                'data' => $overview[0]->date,
                'mensagem' => substr($message, 0, 15000) . '...',
                'numeroPasta' => $numeroPasta
            ];
        }
    }
}

// Se houver novos processos, salva no arquivo procNovos.json
if (!empty($novosProcessos)) {
    // Mescla os processos novos com os já salvos
    $processosSalvos = array_merge($processosSalvos, $novosProcessos);

    // Salva o JSON atualizado
    if (file_put_contents('procNovos.json', json_encode($processosSalvos, JSON_PRETTY_PRINT))) {
        echo "Arquivo procNovos.json atualizado com sucesso.<br>";
    } else {
        echo "Erro ao atualizar o arquivo procNovos.json.<br>";
    }
} else {
    echo "Nenhum novo processo encontrado para salvar.";
}

// Fecha a conexão com o IMAP
imap_close($inbox);
?>
