<?php
    require __DIR__.'/vendor/autoload.php';

    // Load the .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    

    class Mail {

        private $host;
        private $username;
        private $password;
        private $port;
        private $sender_email;
        private $sender_name;
        public $recipient_email;
        public $recipient_name;

        public function send_email($subject, $body, $altbody) {
           
            $mail = new PHPMailer(true);

            try {
                // Configurações do servidor SMTP
                $mail->isSMTP();                                    // Enviar usando SMTP
                $mail->Host       = $_ENV['SMTP_HOST'];             // Servidor SMTP
                $mail->SMTPAuth   = true;                           // Ativar autenticação SMTP
                $mail->Username   = $_ENV['SMTP_USERNAME'];        // Usuário SMTP
                $mail->Password   = $_ENV['SMTP_PASSWORD'];        // Senha SMTP
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar TLS
                $mail->Port       = $_ENV['TCP_PORT'];              // Porta TCP para conectar

                // Remetente e Destinatário
                $mail->setFrom($_ENV['SENDER_EMAIL'], $_ENV['SENDER_NAME']);
                $mail->addAddress($this->recipient_email, $this->recipient_name);

                // Conteúdo
                $mail->isHTML(true);                                // Definir e-mail como HTML
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $altbody; // Para clientes sem suporte a HTML

                $mail->send();
                echo 'Mensagem enviada com sucesso';
            } catch (Exception $e) {
                echo "Mensagem não pôde ser enviada. Erro: {$mail->ErrorInfo}";
            }
        }

    }
?>