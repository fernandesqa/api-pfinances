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

    //INSTANCIA O OBJETO SAVINGS_CONTROL
    $obSavingsControl = new SavingsControl($db);

    // INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    // INSTANCIA O OBJETO PESSOA
    $obPerson = new Person($db);

    // INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $personId;
    date_default_timezone_set('America/Sao_Paulo');
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $statementDate = $day.'/'.$month.'/'.$year;

    $obStatement->Statement_Date = $statementDate;

    $url = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $url[count($url) - 6];
    $familyId = $url[count($url) - 4];
    $monthYear = $url[count($url) - 2];

    $obBugetControl->Family_ID = $familyId;
    $obBudget->Family_ID = $familyId;
    $obStatement->Family_ID = $familyId;
    $obRevenue->Family_ID = $familyId;

    $data = json_decode(file_get_contents("php://input"));

    $obBudget->Budget_Month_Year = $monthYear;
    $obRevenue->Revenue_Month_Year = $monthYear;

    $obUser->User_ID = $userId;

    $result = $obUser->getPersonIdAndFamilyId();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obBugetControl->Person_ID = $Person_ID;
        $obBudget->Person_ID = $Person_ID;
        $personId = $Person_ID;
    }

    $obPerson->Person_ID = $personId;

    $result = $obPerson->readPersonName();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obStatement->Statement_Author = $Person_Name;
    }

    $totalBudgets = count($data->budgets);

    $finalResult = false;

    for($i=0; $i<$totalBudgets; $i++) {
        if($data->budgets[$i]->revenue) {
            $obBudget->Budget_Origin_ID = $data->budgets[$i]->revenueId;
            $obRevenue->Revenue_ID = $data->budgets[$i]->revenueId;
        } else {
            $obBudget->Budget_Origin_ID = $data->budgets[$i]->savingsId;
            $obSavingsControl->Savings_Control_ID = $data->budgets[$i]->savingsId;
        }
        
        $obBudget->Budget_Value = $data->budgets[$i]->value;
        $obBudget->Budget_Current_Value = $data->budgets[$i]->value;
        $obBugetControl->Budget_Control_Description = $data->budgets[$i]->description;
        $obStatement->Statement_Description = 'Destinação de valor para o orçamento "'.$data->budgets[$i]->description.'"';
        $obStatement->Statement_Value = '- '.$obGeneralFunctions->convertToMonetary((string)$data->budgets[$i]->value);
        $result = $obBugetControl->getBudgetByName();

        $num = $result->rowCount();

        //VERIFICA SE O ORÇAMENTO INFORMADO JÁ EXISTE NA TABELA DE CONTROLE
        if($num > 0) {

            //OBTÉM O ID DO BUDGET
            $budgetId;
            $result = $obBugetControl->getBudgetId();

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $budgetId = $Budget_Control_ID;
            }

            $obStatement->Budget_ID = $budgetId;
            $obBudget->Budget_Control_ID = $budgetId;

            //OBTÉM O VALOR ATUAL DA RECEITA OU DA ECONOMIA INFORMADA
            $updatedValue;

            if($data->budgets[$i]->revenue) {
                $result = $obRevenue->getRevenueCurrentValue();
                $currentValue;
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $currentValue = $Revenue_Current_Value;
                }

                $updatedValue = $currentValue - $data->budgets[$i]->value;
                $obRevenue->Revenue_Current_Value = $updatedValue;
                $obRevenue->Revenue_ID = $data->budgets[$i]->revenueId;
                $revenueDescription;
                $result = $obRevenue->getRevenueDescription();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $revenueDescription = $Revenue_Description;
                }

                $obStatement->Statement_Origin = $revenueDescription;

            } else {
                $result = $obSavingsControl->getSavingsValue();

                $currentValue;
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $currentValue = $Savings_Control_Value;
                }

                $updatedValue = $currentValue - $data->budgets[$i]->value;

                $obSavingsControl->Savings_Control_Value = $updatedValue;
                $obSavingsControl->Savings_Control_ID = $data->budgets[$i]->savingsId;

                $savingsDescription;
                $result = $obSavingsControl->getSavingsDescription();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $savingsDescription = $Savings_Control_Description;
                }

                $obStatement->Statement_Origin = $savingsDescription;
            }
                
            $obStatement->Statement_Destination = $data->budgets[$i]->description;

            //CADASTRA OS DADOS NA TABELA BUDGET
            $result = $obBudget->setBudgetValue();

            if($result) {
                //ATUALIZA O VALOR DA RECEITA OU DO REGISTRO DE ECONOMIA
                if($data->budgets[$i]->revenue) {
                    $result = $obRevenue->updateRevenueCurrentValue();

                    if($result) {
                        //REGISTRA OS DADOS NO EXTRATO
                        $result = $obStatement->savingsCreation();

                        if($result) {
                            $finalResult = true;
                        } else {
                            $finalResult = false;
                        }
                    } else {
                        $finalResult = false;
                    }
                } else {
                    $result = $obSavingsControl->updateSavingsValue();

                    if($result) {
                        //REGISTRA OS DADOS NO EXTRATO
                        $result = $obStatement->savingsCreation();

                        if($result) {
                            $finalResult = true;
                        } else {
                            $finalResult = false;
                        }
                    } else {
                        $finalResult = false;
                    }
                }
            } else {
                $finalResult = false;
            }
            
        } else {
            //CADASTRA O ORÇAMENTO NA TABELA DE CONTROLE
            $result = $obBugetControl->createBudget();

            if($result) {
                //OBTÉM O ID DO BUDGET CRIADO
                $budgetId;
                $result = $obBugetControl->getBudgetId();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $budgetId = $Budget_Control_ID;
                }

                $obStatement->Budget_ID = $budgetId;
                $obBudget->Budget_Control_ID = $budgetId;

                //OBTÉM O VALOR ATUAL DA RECEITA OU DA ECONOMIA INFORMADA
                $updatedValue;

                if($data->budgets[$i]->revenue) {
                    $result = $obRevenue->getRevenueCurrentValue();
                    $currentValue;
                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $currentValue = $Revenue_Current_Value;
                    }

                    $updatedValue = $currentValue - $data->budgets[$i]->value;
                    $obRevenue->Revenue_Current_Value = $updatedValue;
                    $obRevenue->Revenue_ID = $data->budgets[$i]->revenueId;
                    $revenueDescription;
                    $result = $obRevenue->getRevenueDescription();

                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $revenueDescription = $Revenue_Description;
                    }

                    $obStatement->Statement_Origin = $revenueDescription;

                } else {

                    $result = $obSavingsControl->getSavingsValue();

                    $currentValue;
                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $currentValue = $Savings_Control_Value;
                    }

                    $updatedValue = $currentValue - $data->budgets[$i]->value;

                    $obSavingsControl->Savings_Control_Value = $updatedValue;
                    $obSavingsControl->Savings_Control_ID = $data->budgets[$i]->savingsId;

                    $savingsDescription;
                    $result = $obSavingsControl->getSavingsDescription();

                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $savingsDescription = $Savings_Control_Description;
                    }

                    $obStatement->Statement_Origin = $savingsDescription;
                }

                $obStatement->Statement_Destination = $data->budgets[$i]->description;
                

                //CADASTRA OS DADOS NA TABELA BUDGET
                $result = $obBudget->setBudgetValue();

                if($result) {
                    //ATUALIZA O VALOR DA RECEITA OU DO REGISTRO DE ECONOMIA
                    if($data->budgets[$i]->revenue) {
                        $result = $obRevenue->updateRevenueCurrentValue();

                        if($result) {
                            //REGISTRA OS DADOS NO EXTRATO
                            $result = $obStatement->savingsCreation();

                            if($result) {
                                $finalResult = true;
                            } else {
                                $finalResult = false;
                            }
                        } else {
                            $finalResult = false;
                        }
                    } else {
                        $result = $obSavingsControl->updateSavingsValue();

                        if($result) {
                            //REGISTRA OS DADOS NO EXTRATO
                            $result = $obStatement->savingsCreation();

                            if($result) {
                                $finalResult = true;
                            } else {
                                $finalResult = false;
                            }
                        } else {
                            $finalResult = false;
                        }
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
        echo json_encode(array('message' => 'Orçamento cadastrado com sucesso'), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
    }

?>