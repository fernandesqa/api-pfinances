<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO INVITES
    $invites = new FamilyInvites($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 1];

    $invites->Family_ID = $familyId;

    //QUERY DE INVITES
    $result = $invites->readAll();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY INVITES
        $invites_arr = array();
        $invites_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $inviteUsed = false;

            if($Family_Invite_Used=='1') {
                $inviteUsed = true;
            }

            $invites_item = array(
                'familyInviteCode' => $Family_Invite_Code,
                'familyInviteEmail' => $Family_Invite_Email,
                'familyInviteUsed' => $inviteUsed
            );

            array_push($invites_arr['data'], $invites_item);
        }

        $invites_arr['total'] = $num;

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($invites_arr);

    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Nenhum registro encontrado'), JSON_UNESCAPED_UNICODE);
    }

?>