<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO USER
    $obUser = new User($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $emailAddress = $data[count($data) - 1];

    $obUser->User_Email = $emailAddress;

    $result = $obUser->getResetRequested();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $userResetRequested = false;
            if($User_Reset_Requested=='1') {
                $userResetRequested = true;
            }

            $user_arr = array(
                'resetRequested' => $userResetRequested
            );
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($user_arr);
    } else {
    
        $userResetRequested = false;

        $user_arr = array(
            'resetRequested' => $userResetRequested
        );

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($user_arr);

    }

?>