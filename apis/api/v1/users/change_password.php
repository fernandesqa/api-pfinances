<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO USER
    $obUser = new User($db);

    $data = json_decode(file_get_contents("php://input"));

    $obUser->User_Email = $data->emailAddress;
    $obUser->User_Password = $data->password;

    $result = $obUser->changePassword();

    if($result) {
        $obUser->cancelResetRequest();
        $arr = array(
            'message' => 'Senha alterada com sucesso'
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