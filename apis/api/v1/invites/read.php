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
    $result = $invites->read();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY INVITES
        $invites_arr = array();
        $invites_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $invites_item = array(
                'Role_Description' => $Role_Description
            );

            array_push($invites_arr['data'], $invites_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($invites_arr);

    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Nenhum registro encontrado'), JSON_UNESCAPED_UNICODE);
    }