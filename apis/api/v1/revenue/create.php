<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUser = new User($db);
    
    // INSTANCIA O OBJETO REVENUE
    $obRevenue = new Revenue($db);

    // INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    // INSTANCIA O OBJETO PESSOA
    $obPerson = new Person($db);

    // INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $personId;
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $statementDate = (string)$day.'/'.$month.'/'.$year;

    $obStatement->Statement_Date = $statementDate;

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 4];
    $monthYear = $data[count($data) - 2];

    $obUser->User_ID = $userId;

    $result = $obUser->getPersonIdAndFamilyId();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obRevenue->Person_ID = $Person_ID;
        $obRevenue->Family_ID = $Family_ID;
        $personId = $Person_ID;
        $obStatement->Family_ID = $Family_ID;
    }

    $obPerson->Person_ID = $personId;

    $result = $obPerson->readPersonName();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obStatement->Statement_Author = $Person_Name;
    }
    

    $obRevenue->Revenue_Month_Year = $monthYear;
    $data = json_decode(file_get_contents("php://input"));
    $totalRevenues = count($data->revenues);

    // CADASTRA AS PENDÊNCIAS NA BASE
    for($i=0; $i<$totalRevenues; $i++) {
        
        $next = $i + 1;

        $obRevenue->Revenue_Value = $data->revenues[$i]->value;
        $obRevenue->Revenue_Current_Value = $data->revenues[$i]->value;
        $obRevenue->Revenue_Description = $data->revenues[$i]->description;

        $obStatement->Statement_Description = 'Inclusão da receita "'.$data->revenues[$i]->description.'"';
        $obStatement->Statement_Value = '+ '.$obGeneralFunctions->convertToMonetary((string)$data->revenues[$i]->value);

        $result = $obRevenue->create();

        if($result==='fail') {
            $i = $totalRevenues;
            http_response_code(500);
            echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
            
        } else if($result==='success' && $next==$totalRevenues) {

            //REGISTRA O CADASTRO DA RECEITA NO EXTRATO DO SISTEMA
            $result = $obStatement->revenueCreation();

            if($result) {
                http_response_code(200);
                echo json_encode(array('message' => 'Receita(s) cadastrada(s) com sucesso'), JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Erro ao gravar o extrato da operação'), JSON_UNESCAPED_UNICODE);
            }
            


        }
    }




?>