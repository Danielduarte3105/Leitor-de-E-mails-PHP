<?php
header("Content-type: text/html; charset=utf-8");
error_reporting(0); // Não reporta nenhum erro
ini_set('display_errors', 0); // Não exibe erros na tela
// Configurações da conexão
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX'; // Captura apenas a caixa de entrada padrão
$username = 'Gmail'; // Seu e-mail
$password = 'Senha APP'; // Sua senha (você pode precisar de uma senha de app para o Gmail)

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
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Captura de E-mails</title>
</head>
<body>
<div class="container mt-5">
    <h1>Últimos E-mails</h1>
    <div class="row">
        <?php
        // Se encontrar e-mails
        if ($emails) {
            // Ordena os e-mails de forma decrescente (últimos e-mails primeiro)
            rsort($emails);
            $max_emails = 10; // Limita a 10 e-mails por vez
            $i = 0;

            foreach ($emails as $email_number) {
                if ($i >= $max_emails) break;

                // Lê as informações do e-mail (assunto, data, remetente)
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $message = imap_fetchbody($inbox, $email_number, 1);
                $message = imap_utf8($message); // Corrige a codificação da mensagem

                // Extraí o número da pasta do título do e-mail
                preg_match('/\b(\d{4,7})\b/', $overview[0]->subject, $matches);
                $pastaNumero = isset($matches[1]) ? intval($matches[1]) : null; // Converter para inteiro para comparação

                // Verifica se o número da pasta está no JSON
                $advogadoInfo = null;
                if ($pastaNumero) {
                    foreach ($jsonData as $data) {
                        // Compare os números da pasta
                        if (isset($data['PASTA_CLIENTE']) && intval($data['PASTA_CLIENTE']) === $pastaNumero) { // Também converter para inteiro
                            $advogadoInfo = $data;
                            break;
                        }
                    }
                }

                // Exibe o resumo do e-mail em um card
                echo '<div class="col-md-4 mb-3">'; // Coluna com espaçamento inferior
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars(imap_utf8($overview[0]->subject)) . '</h5>';
                echo '<h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($overview[0]->from) . '</h6>';
                echo '<p class="card-text">' . htmlspecialchars(substr($message, 0, 150)) . '...</p>'; // Mostra uma prévia da mensagem
                echo '<p class="card-text"><small class="text-muted">' . htmlspecialchars($overview[0]->date) . '</small></p>';

                if ($advogadoInfo) {
                    echo '<button class="btn btn-primary" data-toggle="modal" data-target="#advogadoModal" data-email="' . htmlspecialchars($advogadoInfo['E-mail']) . '" data-advogado="' . htmlspecialchars($advogadoInfo['ADVOGADO']) . '">Ver Advogado</button>';
                } else {
                    echo '<button class="btn btn-secondary" disabled>Sem advogado</button>';
                }

                echo '</div></div>'; 
                echo '</div>'; 

                $i++;
            }
        }
        ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="advogadoModal" tabindex="-1" role="dialog" aria-labelledby="advogadoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="advogadoModalLabel">Informações do Advogado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Advogado:</strong> <span id="advogadoName"></span></p>
                <p><strong>E-mail:</strong> <span id="advogadoEmail"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="sendEmailBtn">Enviar E-mail</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Configura o modal quando um botão é clicado
    $('#advogadoModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botão que acionou o modal
        var advogadoEmail = button.data('email'); // Extrai o email
        var advogadoName = button.data('advogado'); // Extrai o nome do advogado

        // Atualiza o conteúdo do modal
        var modal = $(this);
        modal.find('#advogadoName').text(advogadoName);
        modal.find('#advogadoEmail').text(advogadoEmail);

        // Adiciona a função para o botão de enviar e-mail
        modal.find('#sendEmailBtn').off('click').on('click', function() {
            window.location.href = 'mailto:' + advogadoEmail; // Abre o cliente de e-mail
        });
    });

    // Função para recarregar e-mails a cada 5 minutos
    setInterval(function() {
        location.reload();
    }, 300000); // 5 minutos em milissegundos
</script>
</body>
</html>

<?php
// Fecha a conexão IMAP
imap_close($inbox);
?>
