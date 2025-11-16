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

    // INSTANCIA O OBJETO REVENUE
    $obRevenue = new Revenue($db);  

    //INSTANCIA O OBJETO SAVINGS_CONTROL
    $obSavingsControl = new SavingsControl($db);

    //INSTANCIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    //INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    // INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $dataUrl = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $dataUrl[count($dataUrl) - 3];
    $userId = $dataUrl[count($dataUrl) - 5];
    $monthYear = $dataUrl[count($dataUrl) - 1];

    $data = json_decode(file_get_contents("php://input"));
    $revenueSourceId = $data->revenueSourceId;
    $revenueSourceDescription = $data->revenueSourceDescription;
    $budgetId = $data->budgetId;
    $budgetRevenueId = $data->budgetRevenueId;
    $budgetDescription = $data->budgetDescription;
    $value = $data->value;

    $obRevenue->Family_ID = $familyId;
    $obRevenue->Revenue_ID = $revenueSourceId;
    $obRevenue->Revenue_Month_Year = $monthYear;

    $obSavingsControl->Savings_Control_ID = $revenueSourceId;

    $obBudget->Family_ID = $familyId;
    $obBudget->Budget_Control_ID = $budgetId;
    $obBudget->Budget_Origin_ID = $budgetRevenueId;
    $obBudget->Budget_Month_Year = $monthYear;

    $personId;

    if(strlen($monthYear)==5) {
        $month = substr($monthYear, 0, 1);
        $year = substr($monthYear, 1, 5);
    } else {
        $month = substr($monthYear, 0, 2);
        $year = substr($monthYear, 2, 6);
    }

    date_default_timezone_set('America/Sao_Paulo');
    $currentDay = date('d');
    $currentMonth = date('m');
    $currentYear = date('Y');
    $statementDate = '';

    if(intval($month)==intval($currentMonth) && intval($year)==intval($currentYear)) {
        if(strlen($month)==1) {
            $statementDate = $currentDay.'/'.'0'.$currentMonth.'/'.$currentYear;    
        } else {
            $statementDate = $currentDay.'/'.$currentMonth.'/'.$currentYear;
        }
    } else {
        if(strlen($month)==1) {
            $statementDate = '01/'.'0'.$month.'/'.$year;    
        } else {
            $statementDate = '01/'.$month.'/'.$year;
        }
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

    $obStatement->Statement_Description = 'Aumento do orçamento "'.$budgetDescription.'"';
    $obStatement->Statement_Value = '+ '.$obGeneralFunctions->convertToMonetary((string)$value);
    $obStatement->Statement_Origin = $revenueSourceDescription;
    $obStatement->Statement_Destination = $budgetDescription;

    //OBTÉM O VALOR ATUAL DA RECEITA OU DA ECONOMIA INFORMADA
    $updatedValue;

    // VERIFICA SE O ID DA FONTE DE RECEITA PERTENCE AO OBJETO SAVINGS_CONTROL
    $result = $obSavingsControl->getSavingsValue();

    $num = $result->rowCount();

    if($num>0) {

        $currentValue;
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $currentValue = $Savings_Control_Value;
        }

        $updatedValue = $currentValue - $value;
        $obSavingsControl->Savings_Control_Value = $updatedValue;

        $result = $obSavingsControl->updateSavingsValue();

    } else {
        // VERIFICA SE O ID DA FONTE DE RECEITA PERTENCE AO OBJETO REVENUE
        $result = $obRevenue->getRevenueCurrentValue();

        $num = $result->rowCount();

        if($num>0) {

            $currentValue;
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $currentValue = $Revenue_Current_Value;
            }

            $updatedValue = $currentValue - $value;
            $obRevenue->Revenue_Current_Value = $updatedValue;

            $result = $obRevenue->updateRevenueCurrentValue();

        }
    }

    // OBTÉM O VALOR ATUAL DO ORÇAMENTO E REALIZA O AUMENTO
    $result = $obBudget->getBudgetValues();

    $currentValue;
    $budgetValue;
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $currentValue = $Budget_Current_Value;
        $budgetValue = $Budget_Value;
    }

    $updatedValue = $currentValue + $value;
    $newValue = $budgetValue + $value;
    $obBudget->Budget_Current_Value = $updatedValue;
    $obBudget->Budget_Value = $newValue;

    $result = $obBudget->updateBudgetValues();

    if($result) {
        //REGISTRA OS DADOS NO EXTRATO
        $result = $obStatement->revenueCreation();

        if($result) {
            http_response_code(200);
            echo json_encode(array('message' => 'Orçamento aumentado com sucesso'), JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
        }
    }





?>