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

    //INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    //INSTANCIA O OBJETO STATEMENT_DETAILS
    $obStatementDetails = new StatementDetails($db);

    //INSTANCIA O OBJETO PESSOA
    $obPerson = new Person($db);

    //INSTANCIA O OBJETO GENERALFUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    //INSTANCIA O OBJETO EXPENSE
    $obExpense = new Expense($db);

    //INSTANCIA O OBJETO FIXED_EXPENSE
    $obFixedExpense = new FixedExpense($db);

    $personId;

    $url = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $url[count($url) - 6];
    $familyId = $url[count($url) - 4];
    $monthYear = $url[count($url) - 2];

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

    $obBugetControl->Family_ID = $familyId;
    $obBudget->Family_ID = $familyId;
    $obStatement->Family_ID = $familyId;
    $obStatementDetails->Family_ID = $familyId;
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
        $obStatementDetails->Statement_Details_Author = $Person_Name;
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
        $obBugetControl->Budget_Control_Original_Value = $data->budgets[$i]->value;
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
            $obStatementDetails->Budget_ID = $budgetId;
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
                        $result = $obStatement->revenueCreation();

                        if($result) {
                            //VERIFICA SE HÁ DESPESAS FIXAS VINCULADAS AO ORÇAMENTO
                            $obFixedExpense->Budget_ID = $budgetId;
                            $obFixedExpense->Family_ID = $familyId;

                            $dateTime;
                            $month;
                            $year;

                            if(strlen($monthYear)==5) {
                                $month = substr($monthYear, 0, 1);
                                $year = substr($monthYear, 1, 5);
                            } else {
                                $month = substr($monthYear, 0, 2);
                                $year = substr($monthYear, 2, 6);
                            }

                            if(intval($month)==intval(date('m'))) {
                                $day = date('d');
                                $time = date('H:i:s');
                                if(strlen($month)==1){
                                    $month = '0'.$month;
                                }
                                $date = $day.'/'.$month.'/'.$year;
                                $dateTime = $day.'/'.$month.'/'.$year.' '.$time;
                                $obStatementDetails->Statement_Details_Date = $day.'/'.$month.'/'.$year;
                            } else {
                                $day = '01';
                                $time = date('H:i:s');
                                if(strlen($month)==1){
                                    $month = '0'.$month;
                                }
                                $date = $day.'/'.$month.'/'.$year;
                                $dateTime = $day.'/'.$month.'/'.$year.' '.$time;
                                $obStatementDetails->Statement_Details_Date = $day.'/'.$month.'/'.$year;
                            }

                            $result = $obFixedExpense->getFixedExpense();

                            $num = $result->rowCount();

                            $arr_fixed_expenses = array();
                            $arr_fixed_expenses['data'] = array();

                            if($num>0) {
                                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                    extract($row);

                                    $arr_item_fixed_expenses = array(
                                        "Fixed_Expense_ID" => $Fixed_Expense_ID,
                                        "Person_ID" => $Person_ID,
                                        "Family_ID" => $Family_ID,
                                        "Budget_ID" => $Budget_ID,
                                        "Expense_Category_ID" => $Expense_Category_ID,
                                        "Expense_Installments_Expense" => 0,
                                        "Expense_Date" => $dateTime,
                                        "Expense_Billing_Month_Year" => $monthYear,
                                        "Expense_Value" => $Fixed_Expense_Value,
                                        "Statement_Details_Value" => '- '.$obGeneralFunctions->convertToMonetary((string)$Fixed_Expense_Value),
                                        "Expense_Description" => $Fixed_Expense_Description,
                                        "Statement_Details_Description" => $Fixed_Expense_Description
                                    );

                                    array_push($arr_fixed_expenses['data'], $arr_item_fixed_expenses);
                                }

                                for($j=0; $j<count($arr_fixed_expenses['data']); $j++) {
                                    $obExpense->Expense_ID = $arr_fixed_expenses['data'][$j]["Fixed_Expense_ID"];
                                    $obExpense->Person_ID = $arr_fixed_expenses['data'][$j]["Person_ID"];
                                    $obExpense->Family_ID = $arr_fixed_expenses['data'][$j]["Family_ID"];
                                    $obExpense->Budget_ID = $arr_fixed_expenses['data'][$j]["Budget_ID"];
                                    $obExpense->Expense_Category_ID = $arr_fixed_expenses['data'][$j]["Expense_Category_ID"];
                                    $obExpense->Expense_Installments_Expense = $arr_fixed_expenses['data'][$j]["Expense_Installments_Expense"];
                                    $obExpense->Expense_Date = $arr_fixed_expenses['data'][$j]["Expense_Date"];
                                    $obExpense->Expense_Billing_Month_Year = $arr_fixed_expenses['data'][$j]["Expense_Billing_Month_Year"];
                                    $obExpense->Expense_Value = $arr_fixed_expenses['data'][$j]["Expense_Value"];
                                    $obStatementDetails->Statement_Details_Value = $arr_fixed_expenses['data'][$j]["Statement_Details_Value"];
                                    $obExpense->Expense_Description = $arr_fixed_expenses['data'][$j]["Expense_Description"];
                                    $obStatementDetails->Statement_Details_Description = $arr_fixed_expenses['data'][$j]["Statement_Details_Description"];

                                    //VERIFICA SE AS DESPESAS FIXAS JÁ FORAM CADASTRADAS NO ATUAL PERÍODO
                                    $result = $obExpense->getFixedExpenseByPeriod();

                                    $num = $result->rowCount();

                                    if($num==0) {
                                        $currentValue;
                                        $updatedValue;
                                        $result = $obBudget->getBudgetCurrentValue();

                                        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                            extract($row);

                                            $currentValue = $Budget_Current_Value;
                                        }

                                        $updatedValue = $currentValue - $obExpense->Expense_Value;
                                        $obBudget->Budget_Current_Value = $updatedValue;

                                        $result = $obExpense->createExpense();

                                        if($result) {
                                            // ATUALIZA O VALOR DO ORÇAMENTO
                                            $result = $obBudget->updateBudgetCurrentValue();
                                            if($result) {
                                                //REGISTRA OS DADOS NO EXTRATO
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
                                    }
                                }
                            }

                            //VERIFICA SE HÁ DESPESAS PARCELADAS ATIVAS NO MÊS E ANO ATUAL
                            $result = $obFixedExpense->getInstallmentsExpenses();

                            $time = date('H:i:s');

                            $num = $result->rowCount();

                            $arr_installments_expenses = array();
                            $arr_installments_expenses['data'] = array();

                            if($num>0) {
                                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                    extract($row);

                                    $arr_item_installments_expenses = array(
                                        "Last_Installments_Expense" => $Fixed_Expense_End_Month_Year,
                                        "Fixed_Expense_ID" => $Fixed_Expense_ID,
                                        "Person_ID" => $Person_ID,
                                        "Family_ID" => $Family_ID,
                                        "Budget_ID" => $Budget_ID,
                                        "Expense_Category_ID" => $Expense_Category_ID,
                                        "Expense_Installments_Expense" => 1,
                                        "Expense_Date" => $date.' '.$time,
                                        "Expense_Billing_Month_Year" => $monthYear,
                                        "Expense_Value" => $Fixed_Expense_Value,
                                        "Statement_Details_Value" => '- '.$obGeneralFunctions->convertToMonetary((string)$Fixed_Expense_Value),
                                        "Expense_Description" => $Fixed_Expense_Description,
                                        "Statement_Details_Description" => $Fixed_Expense_Description
                                    );

                                    array_push($arr_installments_expenses['data'], $arr_item_installments_expenses);
                                }

                                for($j=0; $j<count($arr_installments_expenses['data']); $j++) {
                                    $lastInstallmentsExpense = $arr_installments_expenses['data'][$j]["Last_Installments_Expense"];
                                    $obExpense->Expense_ID = $arr_installments_expenses['data'][$j]["Fixed_Expense_ID"];
                                    $obExpense->Person_ID = $arr_installments_expenses['data'][$j]["Person_ID"];
                                    $obExpense->Family_ID = $arr_installments_expenses['data'][$j]["Family_ID"];
                                    $obExpense->Budget_ID = $arr_installments_expenses['data'][$j]["Budget_ID"];
                                    $obExpense->Expense_Category_ID = $arr_installments_expenses['data'][$j]["Expense_Category_ID"];
                                    $obExpense->Expense_Installments_Expense = $arr_installments_expenses['data'][$j]["Expense_Installments_Expense"];
                                    $obExpense->Expense_Date = $arr_installments_expenses['data'][$j]["Expense_Date"];
                                    $obExpense->Expense_Billing_Month_Year = $arr_installments_expenses['data'][$j]["Expense_Billing_Month_Year"];
                                    $obExpense->Expense_Value = $arr_installments_expenses['data'][$j]["Expense_Value"];
                                    $obStatementDetails->Statement_Details_Value = $arr_installments_expenses['data'][$j]["Statement_Details_Value"];
                                    $obExpense->Expense_Description = $arr_installments_expenses['data'][$j]["Expense_Description"];
                                    $obStatementDetails->Statement_Details_Description = $arr_installments_expenses['data'][$j]["Statement_Details_Description"];

                                    //VERIFICA SE AS DESPESAS PARCELADAS JÁ FORAM CADASTRADAS NO ATUAL PERÍODO
                                    $result = $obExpense->getFixedExpenseByPeriod();

                                    $num = $result->rowCount();

                                    if($num==0) {
                                        $lastMonth;
                                        $lastYear;
                                        $month;
                                        $year;
                                        if(strlen($lastInstallmentsExpense)==5) {
                                            $lastMonth = substr($lastInstallmentsExpense, 0, 1);
                                            $lastYear = substr($lastInstallmentsExpense, 1, 5);
                                        } else {
                                            $lastMonth = substr($lastInstallmentsExpense, 0, 2);
                                            $lastYear = substr($lastInstallmentsExpense, 2, 6);
                                        }
                                        
                                        if(strlen($monthYear)==5) {
                                            $month = substr($monthYear, 0, 1);
                                            $year = substr($monthYear, 1, 5);
                                        } else {
                                            $month = substr($monthYear, 0, 2);
                                            $year = substr($monthYear, 2, 6);
                                        }

                                        if(intval($lastMonth)>=intval($month) && intval($lastYear)>=intval($year) || intval($lastMonth)<intval($month) && intval($lastYear)>=intval($year)) {
                                            $currentValue;
                                            $updatedValue;
                                            $result = $obBudget->getBudgetCurrentValue();

                                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                extract($row);

                                                $currentValue = $Budget_Current_Value;
                                            }

                                            $updatedValue = $currentValue - $obExpense->Expense_Value;
                                            $obBudget->Budget_Current_Value = $updatedValue;

                                            $result = $obExpense->createExpense();

                                            if($result) {
                                                // ATUALIZA O VALOR DO ORÇAMENTO
                                                $result = $obBudget->updateBudgetCurrentValue();
                                                if($result) {
                                                    //REGISTRA OS DADOS NO EXTRATO
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
                                        }
                                    }
                                }
                            }
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
                            //VERIFICA SE HÁ DESPESAS FIXAS VINCULADAS AO ORÇAMENTO
                            $obFixedExpense->Budget_ID = $budgetId;
                            $obFixedExpense->Family_ID = $familyId;

                            $dateTime;
                            $month;
                            $year;

                            if(strlen($monthYear)==5) {
                                $month = substr($monthYear, 0, 1);
                                $year = substr($monthYear, 1, 5);
                            } else {
                                $month = substr($monthYear, 0, 2);
                                $year = substr($monthYear, 2, 6);
                            }

                            if(intval($month)==intval(date('m'))) {
                                $day = date('d');
                                $time = date('H:i:s');
                                if(strlen($month)==1){
                                    $month = '0'.$month;
                                }
                                $date = $day.'/'.$month.'/'.$year;
                                $dateTime = $day.'/'.$month.'/'.$year.' '.$time;
                                $obStatementDetails->Statement_Details_Date = $day.'/'.$month.'/'.$year;
                            } else {
                                $day = '01';
                                $time = date('H:i:s');
                                if(strlen($month)==1){
                                    $month = '0'.$month;
                                }
                                $date = $day.'/'.$month.'/'.$year;
                                $dateTime = $day.'/'.$month.'/'.$year.' '.$time;
                                $obStatementDetails->Statement_Details_Date = $day.'/'.$month.'/'.$year;
                            }

                            $result = $obFixedExpense->getFixedExpense();

                            $num = $result->rowCount();

                            $arr_fixed_expenses = array();
                            $arr_fixed_expenses['data'] = array();

                            if($num>0) {
                                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                    extract($row);

                                    $arr_item_fixed_expenses = array(
                                        "Fixed_Expense_ID" => $Fixed_Expense_ID,
                                        "Person_ID" => $Person_ID,
                                        "Family_ID" => $Family_ID,
                                        "Budget_ID" => $Budget_ID,
                                        "Expense_Category_ID" => $Expense_Category_ID,
                                        "Expense_Installments_Expense" => 0,
                                        "Expense_Date" => $dateTime,
                                        "Expense_Billing_Month_Year" => $monthYear,
                                        "Expense_Value" => $Fixed_Expense_Value,
                                        "Statement_Details_Value" => '- '.$obGeneralFunctions->convertToMonetary((string)$Fixed_Expense_Value),
                                        "Expense_Description" => $Fixed_Expense_Description,
                                        "Statement_Details_Description" => $Fixed_Expense_Description
                                    );

                                    array_push($arr_fixed_expenses['data'], $arr_item_fixed_expenses);
                                }

                                for($j=0; $j<count($arr_fixed_expenses['data']); $j++) {
                                    $obExpense->Expense_ID = $arr_fixed_expenses['data'][$j]["Fixed_Expense_ID"];
                                    $obExpense->Person_ID = $arr_fixed_expenses['data'][$j]["Person_ID"];
                                    $obExpense->Family_ID = $arr_fixed_expenses['data'][$j]["Family_ID"];
                                    $obExpense->Budget_ID = $arr_fixed_expenses['data'][$j]["Budget_ID"];
                                    $obExpense->Expense_Category_ID = $arr_fixed_expenses['data'][$j]["Expense_Category_ID"];
                                    $obExpense->Expense_Installments_Expense = $arr_fixed_expenses['data'][$j]["Expense_Installments_Expense"];
                                    $obExpense->Expense_Date = $arr_fixed_expenses['data'][$j]["Expense_Date"];
                                    $obExpense->Expense_Billing_Month_Year = $arr_fixed_expenses['data'][$j]["Expense_Billing_Month_Year"];
                                    $obExpense->Expense_Value = $arr_fixed_expenses['data'][$j]["Expense_Value"];
                                    $obStatementDetails->Statement_Details_Value = $arr_fixed_expenses['data'][$j]["Statement_Details_Value"];
                                    $obExpense->Expense_Description = $arr_fixed_expenses['data'][$j]["Expense_Description"];
                                    $obStatementDetails->Statement_Details_Description = $arr_fixed_expenses['data'][$j]["Statement_Details_Description"];

                                    //VERIFICA SE AS DESPESAS FIXAS JÁ FORAM CADASTRADAS NO ATUAL PERÍODO
                                    $result = $obExpense->getFixedExpenseByPeriod();

                                    $num = $result->rowCount();

                                    if($num==0) {
                                        $currentValue;
                                        $updatedValue;
                                        $result = $obBudget->getBudgetCurrentValue();

                                        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                            extract($row);

                                            $currentValue = $Budget_Current_Value;
                                        }

                                        $updatedValue = $currentValue - $obExpense->Expense_Value;
                                        $obBudget->Budget_Current_Value = $updatedValue;

                                        $result = $obExpense->createExpense();

                                        if($result) {
                                            // ATUALIZA O VALOR DO ORÇAMENTO
                                            $result = $obBudget->updateBudgetCurrentValue();
                                            if($result) {
                                                //REGISTRA OS DADOS NO EXTRATO
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
                                    }
                                }
                            }

                            //VERIFICA SE HÁ DESPESAS PARCELADAS ATIVAS NO MÊS E ANO ATUAL
                            $result = $obFixedExpense->getInstallmentsExpenses();

                            $time = date('H:i:s');

                            $num = $result->rowCount();

                            $arr_installments_expenses = array();
                            $arr_installments_expenses['data'] = array();

                            if($num>0) {
                                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                    extract($row);

                                    $arr_item_installments_expenses = array(
                                        "Last_Installments_Expense" => $Fixed_Expense_End_Month_Year,
                                        "Fixed_Expense_ID" => $Fixed_Expense_ID,
                                        "Person_ID" => $Person_ID,
                                        "Family_ID" => $Family_ID,
                                        "Budget_ID" => $Budget_ID,
                                        "Expense_Category_ID" => $Expense_Category_ID,
                                        "Expense_Installments_Expense" => 1,
                                        "Expense_Date" => $date.' '.$time,
                                        "Expense_Billing_Month_Year" => $monthYear,
                                        "Expense_Value" => $Fixed_Expense_Value,
                                        "Statement_Details_Value" => '- '.$obGeneralFunctions->convertToMonetary((string)$Fixed_Expense_Value),
                                        "Expense_Description" => $Fixed_Expense_Description,
                                        "Statement_Details_Description" => $Fixed_Expense_Description
                                    );

                                    array_push($arr_installments_expenses['data'], $arr_item_installments_expenses);
                                }

                                for($j=0; $j<count($arr_installments_expenses['data']); $j++) {
                                    $lastInstallmentsExpense = $arr_installments_expenses['data'][$j]["Last_Installments_Expense"];
                                    $obExpense->Expense_ID = $arr_installments_expenses['data'][$j]["Fixed_Expense_ID"];
                                    $obExpense->Person_ID = $arr_installments_expenses['data'][$j]["Person_ID"];
                                    $obExpense->Family_ID = $arr_installments_expenses['data'][$j]["Family_ID"];
                                    $obExpense->Budget_ID = $arr_installments_expenses['data'][$j]["Budget_ID"];
                                    $obExpense->Expense_Category_ID = $arr_installments_expenses['data'][$j]["Expense_Category_ID"];
                                    $obExpense->Expense_Installments_Expense = $arr_installments_expenses['data'][$j]["Expense_Installments_Expense"];
                                    $obExpense->Expense_Date = $arr_installments_expenses['data'][$j]["Expense_Date"];
                                    $obExpense->Expense_Billing_Month_Year = $arr_installments_expenses['data'][$j]["Expense_Billing_Month_Year"];
                                    $obExpense->Expense_Value = $arr_installments_expenses['data'][$j]["Expense_Value"];
                                    $obStatementDetails->Statement_Details_Value = $arr_installments_expenses['data'][$j]["Statement_Details_Value"];
                                    $obExpense->Expense_Description = $arr_installments_expenses['data'][$j]["Expense_Description"];
                                    $obStatementDetails->Statement_Details_Description = $arr_installments_expenses['data'][$j]["Statement_Details_Description"];

                                    //VERIFICA SE AS DESPESAS PARCELADAS JÁ FORAM CADASTRADAS NO ATUAL PERÍODO
                                    $result = $obExpense->getFixedExpenseByPeriod();

                                    $num = $result->rowCount();

                                    if($num==0) {
                                        $lastMonth;
                                        $lastYear;
                                        $month;
                                        $year;
                                        if(strlen($lastInstallmentsExpense)==5) {
                                            $lastMonth = substr($lastInstallmentsExpense, 0, 1);
                                            $lastYear = substr($lastInstallmentsExpense, 1, 5);
                                        } else {
                                            $lastMonth = substr($lastInstallmentsExpense, 0, 2);
                                            $lastYear = substr($lastInstallmentsExpense, 2, 6);
                                        }
                                        
                                        if(strlen($monthYear)==5) {
                                            $month = substr($monthYear, 0, 1);
                                            $year = substr($monthYear, 1, 5);
                                        } else {
                                            $month = substr($monthYear, 0, 2);
                                            $year = substr($monthYear, 2, 6);
                                        }

                                        if(intval($lastMonth)>=intval($month) && intval($lastYear)>=intval($year) || intval($lastMonth)<intval($month) && intval($lastYear)>=intval($year)) {
                                            $currentValue;
                                            $updatedValue;
                                            $result = $obBudget->getBudgetCurrentValue();

                                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                extract($row);

                                                $currentValue = $Budget_Current_Value;
                                            }

                                            $updatedValue = $currentValue - $obExpense->Expense_Value;
                                            $obBudget->Budget_Current_Value = $updatedValue;

                                            $result = $obExpense->createExpense();

                                            if($result) {
                                                // ATUALIZA O VALOR DO ORÇAMENTO
                                                $result = $obBudget->updateBudgetCurrentValue();
                                                if($result) {
                                                    //REGISTRA OS DADOS NO EXTRATO
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
                                        }
                                    }
                                }
                            } else {
                                $finalResult = true;
                            }
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