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
    $familyId = $data[count($data) - 1];

    $obUser->Family_ID = $familyId;

    //QUERY
    $result = $obUser->getUsers();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY USER
        $user_arr = array();
        $user_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $userFirstAccess = false;
            $role = 'Dependente';

            if($User_First_Access=='1') {
                $userFirstAccess = true;
            }

            if($Role_ID=='1') {
                $role = 'Titular';
            }

            $user_item = array(
                'name' => $Person_Name,
                'emailAddress' => $User_Email,
                'role' => $role,
                'firstAccess' => $userFirstAccess
            );

            array_push($user_arr['data'], $user_item);
        }

        $user_arr['total'] = $num;

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($user_arr);

    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Nenhum registro encontrado'), JSON_UNESCAPED_UNICODE);
    }
?>