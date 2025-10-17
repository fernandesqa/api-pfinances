<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUser = new User($db);

    // INSTANCIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    // INSTANCIA O OBJETO STATEMENT_DETAILS
    $obStatementDetails = new StatementDetails($db);

    // INSTANCIA O OBJETO SAVINGS
    $obSavings = new Savings($db);

    // INSTANCIA O OBJETO SAVINGS_CONTROL
    $obSavingsControl = new SavingsControl($db);

    //INSTANCIA O OBJETO REVENUE_AND_SAVINGS_CONTROL
    $obRevenueAndSavings = new RevenueAndSavingsControl($db);

    // INSTANCIA O OBJETO PESSOA
    $obPerson = new Person($db);

    // INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $personId;

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 6];
    $familyId = $data[count($data) - 4];
    $monthYear = $data[count($data) - 2];

    $obStatementDetails->Family_ID = $familyId;
    $obSavingsControl->Family_ID = $familyId;
    $obSavings->Family_ID = $familyId;
    $obRevenueAndSavings->Family_ID = $familyId;
    $obBudget->Family_ID = $familyId;
    $obBudget->Budget_Month_Year = $monthYear;

    $obUser->User_ID = $userId;

    $result = $obUser->getPersonIdAndFamilyId();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obSavingsControl->Person_ID = $Person_ID;
        $obSavings->Person_ID = $Person_ID;
        $obRevenueAndSavings->Person_ID = $Person_ID;
        $personId = $Person_ID;
    }

    $obPerson->Person_ID = $personId;

    $result = $obPerson->readPersonName();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obStatementDetails->Statement_Details_Author = $Person_Name;
    }

    $data = json_decode(file_get_contents("php://input"));
    $totalSavings = count($data->savings);

    $finalResult = false;

    //CADASTRA OS REGISTROS DE ECONOMIA NA BASE
    for($i=0; $i<$totalSavings; $i++) {
        $statementDate = $data->savings[$i]->date;
        $obStatementDetails->Statement_Details_Date = $statementDate;
        $obStatementDetails->Budget_ID = $data->savings[$i]->budgetId;
        $obStatementDetails->Statement_Details_Description = $data->savings[$i]->description;
        $obStatementDetails->Statement_Details_Value = '+ '.$obGeneralFunctions->convertToMonetary((string)$data->savings[$i]->value);
        $obSavings->Savings_Date = $data->savings[$i]->date;
        $obSavingsControl->Savings_Control_Description = $data->savings[$i]->description;
        $obSavingsControl->Savings_Control_Value = $data->savings[$i]->value;
        $obSavings->Budget_ID = $data->savings[$i]->budgetId;
        $obSavings->Savings_Value = $data->savings[$i]->value;
        $obBudget->Budget_Control_ID = $data->savings[$i]->budgetId;

        $lastSavingsId;
        $lastRevenueId;
        $newId;

        $resultRevenueAndSavings = $obRevenueAndSavings->getLastRevenueId();

        while($row = $resultRevenueAndSavings->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $lastRevenueId = $Revenue_ID;
        }

        $resultRevenueAndSavings = $obRevenueAndSavings->getLastSavingsId();

        $num = $resultRevenueAndSavings->rowCount();

        if($num > 0) {
            while($row = $resultRevenueAndSavings->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $lastSavingsId = $Savings_ID;
            }

            if($lastSavingsId > $lastRevenueId) {
                $newId = $lastSavingsId + 1;
            } else {
                $newId = $lastRevenueId + 1;
            }
        } else {
            $newId = $lastRevenueId + 1;
        }

        $obRevenueAndSavings->Savings_ID = $newId;
        $obSavingsControl->Savings_Control_ID = $newId;
        $obSavings->Savings_Control_ID = $newId;

        $currentValue;
        $updatedValue;
        $result = $obBudget->getBudgetCurrentValue();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $currentValue = $Budget_Current_Value;
        }

        $updatedValue = $currentValue - $data->savings[$i]->value;
        $obBudget->Budget_Current_Value = $updatedValue;

        $result = $obSavingsControl->getSavingsId();

        $num = $result->rowCount();

        if($num > 0) {
            $savingsId;

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $savingsId = $Savings_Control_ID;
            }

            $obSavingsControl->Savings_Control_ID = $savingsId;
            $obRevenueAndSavings->Savings_ID = $savingsId;
            $obSavings->Savings_Control_ID = $savingsId;

            $savingsValue;
            $result = $obSavingsControl->getSavingsValue();

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $savingsValue = $Savings_Control_Value;
            }
            $savingsUpdatedValue = $savingsValue + $data->savings[$i]->value;
            $obSavingsControl->Savings_Control_Value = $savingsUpdatedValue;

            //ATUALIZA O VALOR DO REGISTRO DE ECONOMIA NA TABELA DE CONTROLE
            $result = $obSavingsControl->updateSavingsValue();

            if($result) {
                //CADASTRA O REGISTRO DE ECONOMIA DO MÊS E ANO INFORMADO
                $result = $obSavings->createSavings();

                if($result) {
                    // ATUALIZA O VALOR DO ORÇAMENTO
                    $result = $obBudget->updateBudgetCurrentValue();

                    if($result) {
                        // REGISTRA OS DADOS NO EXTRATO
                        $result = $obStatementDetails->createStatementDetails();

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

            } else {
                $finalResult = false;
            }
            
        } else {
            // CADASTRA O ID DO REGISTRO NA TABELA DE CONTROLE
            $result = $obRevenueAndSavings->createSavingId();

            if($result) {
                // CADASTRA OS DADOS NA TABELA DE CONTROLE
                $result = $obSavingsControl->createSavings();

                if($result) {
                    //CADASTRA O REGISTRO DE ECONOMIA DO MÊS E ANO INFORMADO
                    $result = $obSavings->createSavings();

                    if($result) {
                        // ATUALIZA O VALOR DO ORÇAMENTO
                        $result = $obBudget->updateBudgetCurrentValue();

                        if($result) {
                            // REGISTRA OS DADOS NO EXTRATO
                            $result = $obStatementDetails->createStatementDetails();

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
                } else {
                    $finalResult = false;
                }
            } else {
                $finalResult = false;
            }
        }
    }

    if($finalResult) {
        http_response_code(200);
        echo json_encode(array('message' => 'Economia cadastrada com sucesso'), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
    }
?>