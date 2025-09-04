<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO FAMILYINVITES
    $obFamilyInvites = new FamilyInvites($db);

    $data = json_decode(file_get_contents("php://input"));

    $obFamilyInvites->Family_Invite_Code = $data->inviteCode;
    $obFamilyInvites->Family_Invite_Email = $data->emailAddress;

    //VERIFICA SE O CÓDIGO INFORMADO É VÁLIDO
    $result = $obFamilyInvites->getInviteCode();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        //ATUALIZA A BASE, INDICANDO QUE O CONVITE FOI UTILIZADO
        $result = $obFamilyInvites->update();

        if($result==true) {
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

?>