<?php
        // Se encontrar e-mails
        if ($emails) {
            rsort($emails);
            $max_emails = 100;
            $i = 0;

            foreach ($emails as $email_number) {
                if ($i >= $max_emails) break;

                // Lê as informações do e-mail
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $message = imap_fetchbody($inbox, $email_number, 1);
                $message = imap_utf8($message);

                // Extrai o número da pasta do título do e-mail
                preg_match('/\b(\d{4,7})\b/', $overview[0]->subject, $matches);
                $pastaNumero = isset($matches[1]) ? intval($matches[1]) : null;

                // Verifica se o número da pasta está no JSON
                $advogadoInfo = null;
                $titulo = "Processo Novo"; // Define como Processo Novo por padrão

                if ($pastaNumero) {
                    foreach ($jsonData as $data) {
                        if (isset($data['PASTA_CLIENTE']) && intval($data['PASTA_CLIENTE']) === $pastaNumero) {
                            $advogadoInfo = $data;
                            $titulo = "INTIMAÇÃO"; // Se encontrado, define como INTIMAÇÃO
                            break;
                        }
                    }
                }

                // Exibe o card
                echo '<div class="col-md-4 mb-3">';
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($titulo) . ': ' . htmlspecialchars(imap_utf8($overview[0]->subject)) . '</h5>';
                echo '<h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($overview[0]->from) . '</h6>';
                echo '<p class="card-text">' . htmlspecialchars(substr($message, 0, 150)) . '...</p>';
                echo '<p class="card-text"><small class="text-muted">' . htmlspecialchars($overview[0]->date) . '</small></p>';

                if ($advogadoInfo) {
                    echo '<button class="btn btn-primary" data-toggle="modal" data-target="#advogadoModal" data-email="' . htmlspecialchars($advogadoInfo['Email']) . '" data-advogado="' . htmlspecialchars($advogadoInfo['ADVOGADO']) . '">Ver Advogado</button>';
                } else {
                    echo '<button class="btn btn-secondary" disabled>Sem advogado</button>';
                }

                echo '</div></div>';
                echo '</div>';

                $i++;
            }
        }
        ?>