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

    $data = json_decode(file_get_contents("php://input"));

    $person->Person_Name = $data->name;
    $dateTime = $dateTime = date("Y-m-d H:i:s");
    $person->Person_Creation_Date_Time = $dateTime;

    $user->User_Email = $data->emailAddress;
    $user->User_Password = $data->password;

    //VERIFICA SE O E-MAIL INFORMADO EXISTE BASE
    $result = $user->readEmailAddress();

    $num = $result->rowCount();

    if($num > 0) {
        //VERIFICA SE O PRIMEIRO ACESSO JÁ FOI REALIZADO
        $result = $user->checkFirstAccess();
        $userFirstAccess = '';

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $userFirstAccess = $User_First_Access;
        }

        if($userFirstAccess == '1') {
            // CRIA UMA NOVA PESSOA
            $result = $person->create();

            if($result=='success') {
                // OBTEM O ID DA PESSOA
                $result = $person->readPersonId();

                $num = $result->rowCount();

                $personId = 0;

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $personId = $Person_ID;
                    
                }

                $user->Person_ID = $personId;

                //ATUALIZA OS DADOS DO DEPENDENTE PARA CONCLUIR O PRIMEIRO ACESSO.
                $result = $user->saveDataDependentFirstAccess();

                if($result==true) {
                    http_response_code(200);
                    echo json_encode(array('message' => 'Primeiro acesso realizado'), JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro interno, por favor tente mais tarde'), JSON_UNESCAPED_UNICODE);
                }

            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Erro ao salvar o nome da pessoa'), JSON_UNESCAPED_UNICODE);
            }

        } else {
            http_response_code(409);
            echo json_encode(array('message' => 'Primeiro acesso já realizado'), JSON_UNESCAPED_UNICODE);
        }

    } else {
        //SEM REGISTROS
        http_response_code(400);
        echo json_encode(array('message' => 'Dados inválidos'), JSON_UNESCAPED_UNICODE);
    }
?>