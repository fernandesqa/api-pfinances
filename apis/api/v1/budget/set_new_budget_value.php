<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUser = new User($db);

    // INSTANCIA O OBJETO PESSOA
    $obPerson = new Person($db);

    //INSTANCIA O OBJETO BUDGET
    $obBudgetControl = new BudgetControl($db);

    //INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    // INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $dataUrl = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $dataUrl[count($dataUrl) - 1];
    $userId = $dataUrl[count($dataUrl) - 3];

    $data = json_decode(file_get_contents("php://input"));
    $budgetId = $data->budgetId;
    $budgetDescription = $data->budgetDescription;
    $value = $data->value;

    $obBudgetControl->Family_ID = $familyId;
    $obBudgetControl->Budget_Control_ID = $budgetId;
    $obBudgetControl->Budget_Control_Original_Value = $value;

    $personId;

    date_default_timezone_set('America/Sao_Paulo');
    $currentDay = date('d');
    $currentMonth = date('m');
    $currentYear = date('Y');
    $statementDate = '';

    if(strlen($currentMonth)==1) {
        $statementDate = $currentDay.'/'.'0'.$currentMonth.'/'.$currentYear;    
    } else {
        $statementDate = $currentDay.'/'.$currentMonth.'/'.$currentYear;
    }
    
    $obStatement->Statement_Date = $statementDate;
    $obStatement->Family_ID = $familyId;
    $obStatement->Budget_ID = $budgetId;

    $obUser->User_ID = $userId;

    $result = $obUser->getPersonIdAndFamilyId();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $personId = $Person_ID;
    }

    $obPerson->Person_ID = $personId;

    $result = $obPerson->readPersonName();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obStatement->Statement_Author = $Person_Name;
    }

    $obStatement->Statement_Description = 'Alteração de valor do orçamento "'.$budgetDescription.'"';
    $obStatement->Statement_Value = '+ '.$obGeneralFunctions->convertToMonetary((string)$value);
    $obStatement->Statement_Origin = '';
    $obStatement->Statement_Destination = '';


    $result = $obBudgetControl->setNewBudgetValue();

    if($result) {
        //REGISTRA OS DADOS NO EXTRATO
        $result = $obStatement->revenueCreation();

        if($result) {
            http_response_code(200);
            echo json_encode(array('message' => 'Valor alterado com sucesso'), JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
        }
    }





?>