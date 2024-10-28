<?php include 'conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        <?php include 'mails.php' ?>
    </div>
</div>

<div hidden>
    <?php include 'procNovos.php'; ?>
    <?php include 'procInt.php'; ?>
    <?php include 'mail.php'; ?>
</div>


<!-- Modal e Script para abrir e enviar e-mail continuam os mesmos -->


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


