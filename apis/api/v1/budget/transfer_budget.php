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
    $obBudget = new Budget($db);

    //INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    // INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $dataUrl = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $dataUrl[count($dataUrl) - 1];
    $userId = $dataUrl[count($dataUrl) - 3];

    $data = json_decode(file_get_contents("php://input"));
    $budgetWithdrawRevenueId = $data->budgetWithdrawRevenueId;
    $budgetWithdrawId = $data->budgetWithdrawId;
    $budgetWithdrawDescription = $data->budgetWithdrawDescription;
    $budgetWithdrawMonthYear = $data->budgetWithdrawMonthYear;
    $budgetDestinationRevenueId = $data->budgetDestinationRevenueId;
    $budgetDestinationId = $data->budgetDestinationId;
    $budgetDestinationDescription = $data->budgetDestinationDescription;
    $budgetDestinationMonthYear = $data->budgetDestinationMonthYear;
    $value = $data->value;
    
    $obBudget->Budget_Origin_ID = $budgetWithdrawRevenueId;
    $obBudget->Budget_Control_ID = $budgetWithdrawId;
    $obBudget->Family_ID = $familyId;
    $obBudget->Budget_Month_Year = $budgetWithdrawMonthYear;

    $budgetMonth;
    $budgetYear;
    if(strlen($budgetDestinationMonthYear)==5) {
        $budgetMonth = substr($budgetDestinationMonthYear, 0, 1);
        $budgetYear = substr($budgetDestinationMonthYear, 1, 5);
    } else {
        $budgetMonth = substr($budgetDestinationMonthYear, 0, 2);
        $budgetYear = substr($budgetDestinationMonthYear, 2, 6);
    }

    date_default_timezone_set('America/Sao_Paulo');
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $statementDate;

    $monthFormat;

    if(strlen($budgetMonth)==1) {
        $monthFormat = '0'.$budgetMonth;
    } else {
        $monthFormat = $budgetMonth;
    }

    if(intval($budgetMonth)==$month && intval($budgetYear)==$year) {
        $statementDate = $day.'/'.$month.'/'.$year;
    } else {
        $statementDate = '01/'.$monthFormat.'/'.$budgetYear;
    }
    

    $obStatement->Statement_Date = $statementDate;
    $obStatement->Family_ID = $familyId;
    $obStatement->Budget_ID = $budgetDestinationId;
    $obStatement->Statement_Origin = $budgetWithdrawDescription;
    $obStatement->Statement_Destination = $budgetDestinationDescription;
    $obStatement->Statement_Description = 'Transferência entre orçamentos';
    $obStatement->Statement_Value = '+ '.$obGeneralFunctions->convertToMonetary((string)$value);

    $obUser->User_ID = $userId;

    $personId;

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

    //Obtém o valor atual do orçamento que fará a transferência
    $result = $obBudget->getBudgetValues();

    $budgetWithdrawCurrentValue = 0;

     while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $budgetWithdrawCurrentValue = $Budget_Current_Value;
        
    }

    $budgetWithdrawUpdatedValue = $budgetWithdrawCurrentValue - $value;

    //Atualiza o valor atual no orçamento que realiza a transferência
    $obBudget->Budget_Current_Value = $budgetWithdrawUpdatedValue;

    $result = $obBudget->updateWithdrawBudget();

    $finalResult;
    if($result) {
        //Obtém os valores do orçamento que receberá o valor
        $obBudget->Budget_Origin_ID = $budgetDestinationRevenueId;
        $obBudget->Budget_Control_ID = $budgetDestinationId;
        $obBudget->Budget_Month_Year = $budgetDestinationMonthYear;

        $result = $obBudget->getBudgetValues();

        $budgetDestinationCurrentValue = 0;
        $budgetDestinationValue = 0;

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $budgetDestinationCurrentValue = $Budget_Current_Value;
            $budgetDestinationValue = $Budget_Value;
            
        }

        $budgetDestinationUpdatedCurrentValue = $budgetDestinationCurrentValue + $value;
        $budgetDestinationUpdatedValue = $budgetDestinationValue + $value;

        $obBudget->Budget_Current_Value = $budgetDestinationUpdatedCurrentValue;
        $obBudget->Budget_Value = $budgetDestinationUpdatedValue;

        //Atualiza os valores do orçamento que recebe o valor
        $result = $obBudget->updateDestinationBudget();

        if($result) {
            //REGISTRA OS DADOS NO EXTRATO
            $result = $obStatement->revenueCreation();
            
            if($result) {
                $finalResult = true;
            } else {
                $finalResult = false;
            }

        } else {
            $finalResult = false;
        }

    } else {
        $finalResult = false;
    }

    if($finalResult) {
        http_response_code(200);
        echo json_encode(array('message' => 'Transferência realizada com sucesso'), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
    }

?>