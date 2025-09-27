<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO USER
    $obUser = new User($db);

    //INSTANCIA O OBJETO MAIL
    $mail = new Mail();

    $data = json_decode(file_get_contents("php://input"));

    $obUser->User_Email = $data->emailAddress;

    $result = $obUser->requestReset();

    if($result) {
        
        $result = $obUser->getPersonNameByEmail();

        $num = $result->rowCount();

        $name = '';

        if($num > 0) {
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $name = $Person_Name;
            
            }

            $subject = 'Redefinição de Senha';
            $mail->recipient_email = $data->emailAddress;
            $mail->recipient_name = $name;

            $body = '<p>Olá, '.$name.'</p>
                    <p>Houve um pedido para alterar sua senha!</p>
                    <p>Se você não fez esta solicitação, ignore este e-mail...</p>
                    <p>Caso contrário, clique neste <a href="http://pfinances.com.br/app/usuarios/redefinir-senha/'.$data->emailAddress.'" >Link</a> para alterar sua senha.</p>
                    <p>Atenciosamente,</p>
                    <p>Equipe Pfinances</p><br><img src="https://pfinances.com.br/img/logo-pfinances.png" alt="logo" />';

            $altbody = 'Olá, '.$name.'
                        Houve um pedido para alterar sua senha!
                        Se você não fez esta solicitação, ignore este e-mail...
                        Caso contrário, acesse esse endereço http://pfinances.com.br/app/usuarios/redefinir-senha/'.$data->emailAddress.' para alterar sua senha.
                        Atenciosamente,
                        Equipe Pfinances';

            $mail->send_email($subject, $body, $altbody);
        }

        $arr = array(
            'message' => 'solicitação realizada com sucesso'
        );

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr);
    } else {
        $arr = array(
            'message' => 'Erro interno, por favor tente novamente mais tarde'
        );

        //CONVERTE EM JSON
        http_response_code(500);
        echo json_encode($arr);
    }

?>