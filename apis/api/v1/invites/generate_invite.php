<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO USER
    $obUser = new User($db);

    //INSTANCIA O OBJETO FAMILY
    $obFamily = new Family($db);

    //INSTACIA O OBJETO FAMILYINVITES
    $obFamilyInvite = new FamilyInvites($db);

    $data = json_decode(file_get_contents("php://input"));

    $obFamily->Family_ID = $data->familyId;

    $result = $obFamily->readFamilyName();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY INVITES
        $family_arr = array();
        $family_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            // GERA O CÓDIGO DO CONVITE
            $inviteCode = strtoupper(substr($data->emailAddress, 0, 3)).date('Y').strtoupper(substr($Family_Name, 0, 3));

        }

        //VERIFICA SE O E-MAIL JÁ ESTÁ CADASTRADO NA BASE
        $obUser->User_Email = $data->emailAddress;
        $result = $obUser->readEmailAddress();

        $num = $result->rowCount();

        if($num==0) {
            //GRAVA OS DADOS DO CONVITE E OS DADOS DO USUÁRIO NA BASE
            $obFamilyInvite->Family_Invite_Email = $data->emailAddress;

            $result = $obFamilyInvite->read();

            $num = $result->rowCount();

            if($num==0) {
                //GRAVA O CÓDIGO DO CONVITE NA TABELA
                $obFamilyInvite->Family_ID = $data->familyId;
                $obFamilyInvite->Family_Invite_Code = $inviteCode;
                
                $result = $obFamilyInvite->create();

                if($result==='success') {
                    $obUser->Family_ID = $data->familyId;
                    $obUser->Status_ID = 1;
                    $obUser->Role_ID = 2;
                    $obUser->User_First_Access = 1;

                    $result = $obUser->createDependent();

                    if($result==='success') {
                        echo json_encode(array('emailAddress' => $data->emailAddress, 'inviteCode' => $inviteCode), JSON_UNESCAPED_UNICODE);
                    } else {
                        http_response_code(500);
                        echo json_encode(array('message' => 'Erro ao gravar os dados do usuário'), JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro ao gravar os dados do convite'), JSON_UNESCAPED_UNICODE);
                }
            } else {

                //GRAVA OS DADOS DO USUÁRIO NA BASE
                $obUser->Family_ID = $data->familyId;
                $obUser->Status_ID = 1;
                $obUser->Role_ID = 2;
                $obUser->User_First_Access = 1;

                $result = $obUser->createDependent();

                if($result==='success') {
                    echo json_encode(array('emailAddress' => $data->emailAddress, 'inviteCode' => $inviteCode), JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro ao gravar os dados do usuário'), JSON_UNESCAPED_UNICODE);
                }
            }

        } else {
            http_response_code(403);
            echo json_encode(array('message' => 'E-mail em uso'), JSON_UNESCAPED_UNICODE);
        }
    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Família não encontrada'), JSON_UNESCAPED_UNICODE);
    }


?>