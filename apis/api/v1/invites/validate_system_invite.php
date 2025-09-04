<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO INVITES
    $invites = new Invites($db);

    //QUERY DE INVITES
    $result = $invites->readSystem();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        //ALTERA O STATUS PARA INFORMAR QUE O CONVITE FOI UTILIZADO
        if($invites->updateSystem()) {
            //CONVERTE EM JSON
            http_response_code(200);
            echo json_encode(array('message' => 'Convite validado'), JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Erro ao atualizar dado'), JSON_UNESCAPED_UNICODE);
        }

    } else {
        //SEM REGISTROS
        http_response_code(400);
        echo json_encode(array('message' => 'Dados inválidos'), JSON_UNESCAPED_UNICODE);
    }