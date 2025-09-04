<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO USER
    $user = new User($db);

    //INSTACIA O OBJETO PERSON
    $person = new Person($db);

    //INSTACIA O OBJETO FAMILY
    $family = new Family($db);

    $data = json_decode(file_get_contents("php://input"));

    $person->Person_Name = $data->name;
    $dateTime = $dateTime = date("Y-m-d H:i:s");
    $person->Person_Creation_Date_Time = $dateTime;
    $family->Family_Name = $data->familyName;

    // QUERY DE USER
    $user->User_Email = $data->emailAddress;
    $result = $user->readEmailAddress();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num == 0) {

        // CRIA UMA NOVA PESSOA
        $result = $person->create();

        if($result === 'success') {
            // CRIA UMA NOVA FAMILIA
            $result = $family->create();

            if($result === 'success') {
                // OBTEM O ID DA PESSOA
                $result = $person->readPersonId();

                $num = $result->rowCount();

                $personId = 0;

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $personId = $Person_ID;
                    
                }

                // OBTEM O ID DA FAMILIA
                $result = $family->readFamilyId();

                $num = $result->rowCount();

                $familyId = 0;

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $familyId = $Family_ID;
                    
                }

                $user->Person_ID = $personId;
                $user->Family_ID = $familyId;
                $user->Status_ID = 1;
                $user->Role_ID = 1;
                $user->User_Email = $data->emailAddress;
                $user->User_Password = $data->password;
                $user->User_First_Access = 0;

                // CRIA UM NOVO USUÁRIO
                $result = $user->create();

                if($result === 'success') {
                    http_response_code(200);
                    echo json_encode(array('message' => 'Primeiro acesso realizado'), JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro interno, por favor tente mais tarde'), JSON_UNESCAPED_UNICODE); 
                }

            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Erro ao salvar o nome da familia'), JSON_UNESCAPED_UNICODE);    
            }
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Erro ao salvar o nome da pessoa'), JSON_UNESCAPED_UNICODE);
        }

    } else {
        // PRIMEIRO ACESSO JÁ REALIZADO
        http_response_code(409);
        echo json_encode(array('message' => 'Primeiro acesso já realizado'), JSON_UNESCAPED_UNICODE);
    }
?>