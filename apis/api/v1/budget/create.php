<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUser = new User($db);

    //INSTANCIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    //INSTACIA O OBJETO BUDGET_CONTROL
    $obBugetControl = new BudgetControl($db);

    //INSTACIA O OBJETO REVENUE
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

    $url = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $url[count($url) - 6];
    $monthYear = $url[count($url) - 2];

    $data = json_decode(file_get_contents("php://input"));

    $obBudget->Budget_Month_Year = $monthYear;

    $obUser->User_ID = $userId;

    $result = $obUser->getPersonIdAndFamilyId();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obBugetControl->Person_ID = $Person_ID;
        $obBugetControl->Family_ID = $Family_ID;
        $obBudget->Person_ID = $Person_ID;
        $obBudget->Family_ID = $Family_ID;
        $personId = $Person_ID;
        $obStatement->Family_ID = $Family_ID;
    }

    $obPerson->Person_ID = $personId;

    $result = $obPerson->readPersonName();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obStatement->Statement_Author = $Person_Name;
    }

    $totalBudgets = count($data->budgets);

    for($i=0; $i<$totalBudgets; $i++) {
        $obBudget->Revenue_ID = $data->budgets[$i]->revenueId;
        $obRevenue->Revenue_ID = $data->budgets[$i]->revenueId;
        $obBugetControl->Budget_Control_Description = $data->budgets[$i]->description;
        $obStatement->Statement_Description = 'Destinação de valor para o orçamento "'.$data->budgets[$i]->description.'"';
        $obStatement->Statement_Value = '- '.$obGeneralFunctions->convertToMonetary((string)$data->budgets[$i]->value);
        $result = $obBugetControl->getBudgetByName();

        $num = $result->rowCount();

        //VERIFICA SE O ORÇAMENTO INFORMADO JÁ EXISTE NA TABELA DE CONTROLE
        if($num > 0) {
            //OBTÉM O VALOR ATUAL DO ORÇAMENTO
            $result = $obRevenue->getRevenueCurrentValue();
            $currentValue;
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $currentValue = $Revenue_Current_Value;
            }

            $updatedValue = $currentValue - $data->budgets[$i]->value;

            $obRevenue->Revenue_Current_Value = $updatedValue; 

            //CONVERTE EM JSON
            http_response_code(200);
            echo json_encode($currentValue);
            echo json_encode($updatedValue);

            //CADASTRA OS DADOS NA TABELA BUDGET
            
        } else {
            //CADASTRA O ORÇAMENTO NA TABELA DE CONTROLE
            $result = $obBugetControl->createBudget();

            if($result) {
                //OBTÉM O VALOR ATUAL DO ORÇAMENTO
                $result = $obRevenue->getRevenueCurrentValue();
                $currentValue;
                 while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $currentValue = $Revenue_Current_Value;
                }

                $updatedValue = $currentValue - $data->budgets[$i]->value;

                $obRevenue->Revenue_Current_Value = $updatedValue; 

                //CONVERTE EM JSON
                http_response_code(200);
                echo json_encode($currentValue);

                //CADASTRA OS DADOS NA TABELA BUDGET

            } else {
                http_response_code(500);
                echo json_encode('Erro interno, por favor tente novamente mais tarde');
            }
            
        }
        
    }

?>