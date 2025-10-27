<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO EXPENSE
    $obExpense = new Expense($db);

    //INSTANCIA O OBJETO FIXED_EXPENSE
    $obFixedExpense = new FixedExpense($db);

    //INSTANCIA O OBJETO USER
    $obUser = new User($db);

    // INSTANCIA O OBJETO PESSOA
    $obPerson = new Person($db);

    // INSTANCIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    // INSTANCIA O OBJETO STATEMENT_DETAILS
    $obStatementDetails = new StatementDetails($db);

    // INSTANCIA O OBJETO GENERAL_FUNCTIONS
    $obGeneralFunctions = new generalFunctions();

    $personId;

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 4];
    $familyId = $data[count($data) - 2];

    $obUser->User_ID = $userId;

    $result = $obUser->getPersonIdAndFamilyId();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $personId = $Person_ID;
    }

    $obExpense->Person_ID = $personId;
    $obExpense->Family_ID = $familyId;
    $obFixedExpense->Person_ID = $personId;
    $obFixedExpense->Family_ID = $familyId;
    $obStatementDetails->Family_ID = $familyId;
    $obBudget->Family_ID = $familyId;
    $obPerson->Person_ID = $personId;

    $result = $obPerson->readPersonName();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obStatementDetails->Statement_Details_Author = $Person_Name;
    }

    $finalResult = false;

    $data = json_decode(file_get_contents("php://input"));

    $totalExpenses = count($data->expenses);

    for($i=0; $i<$totalExpenses; $i++) {

        $expenseId;

        if($data->expenses[$i]->fixedExpense==false) {
            $result = $obExpense->totalExpenses();

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $expenseId = $Total +1;
            }

            $obExpense->Expense_ID = $expenseId;

        } else {
            $obFixedExpense->Budget_ID = $data->expenses[$i]->budgetId;
            $obFixedExpense->Expense_Category_ID = $data->expenses[$i]->categoryId;
            $obFixedExpense->Fixed_Expense_Month_Year = $data->expenses[$i]->billingMonthYear;
            $obFixedExpense->Fixed_Expense_End_Month_Year = $data->expenses[$i]->lastBillingMonthYear;
            $obFixedExpense->Fixed_Expense_Value = $data->expenses[$i]->value;
            $obFixedExpense->Fixed_Expense_Description = $data->expenses[$i]->description;

            $result = $obFixedExpense->createFixedExpense();

            if($result) {

                $result = $obFixedExpense->getFixedExpenseId();

                $num = $result->rowCount();

                if($num>0) {
                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $expenseId = $Fixed_Expense_ID;
                    }

                    $obExpense->Expense_ID = $expenseId;

                } else {
                    $finalResult = false;    
                }

            } else {
                $finalResult = false;
            }
        }

        $statementDate = '';
        $obBudget->Budget_Month_Year = $data->expenses[$i]->billingMonthYear;
        $obExpense->Budget_ID = $data->expenses[$i]->budgetId;
        $obStatementDetails->Budget_ID = $data->expenses[$i]->budgetId;
        $obBudget->Budget_Control_ID = $data->expenses[$i]->budgetId;
        $obExpense->Expense_Category_ID = $data->expenses[$i]->categoryId;
        $installmentsExpense;
        $currentMonth = date('m');
        $currentYear = date('Y');
        $month = '';
        $year = '';

        if($data->expenses[$i]->installmentsExpense==false) {
            $installmentsExpense = 0;
            $statementDate = explode(' ', $data->expenses[$i]->date)[0];
        } else {
            $installmentsExpense = 1;

            if(strlen($data->expenses[$i]->billingMonthYear)==5) {
                $month = substr($data->expenses[$i]->billingMonthYear, 0, 1);
                $year = substr($data->expenses[$i]->billingMonthYear, 1, 5);
            } else {
                $month = substr($data->expenses[$i]->billingMonthYear, 0, 2);
                $year = substr($data->expenses[$i]->billingMonthYear, 2, 6);
            }
            if(intval($month) <= intval($currentMonth) && intval($year) <= intval($currentYear)) {
                $statementDate = explode(' ', $data->expenses[$i]->date)[0];
            }
        }
        $obExpense->Expense_Installments_Expense = $installmentsExpense;
        $obExpense->Expense_Date = $data->expenses[$i]->date;
        $obStatementDetails->Statement_Details_Date = $statementDate;
        $obExpense->Expense_Billing_Month_Year = $data->expenses[$i]->billingMonthYear;
        $obExpense->Expense_Value = $data->expenses[$i]->value;
        $obStatementDetails->Statement_Details_Value = '- '.$obGeneralFunctions->convertToMonetary((string)$data->expenses[$i]->value);
        $obExpense->Expense_Description = $data->expenses[$i]->description;
        $obStatementDetails->Statement_Details_Description = $data->expenses[$i]->description;
        
        if(intval($month) <= intval($currentMonth)) {
            $currentValue;
            $updatedValue;
            $result = $obBudget->getBudgetCurrentValue();

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $currentValue = $Budget_Current_Value;
            }

            $updatedValue = $currentValue - $data->expenses[$i]->value;
            $obBudget->Budget_Current_Value = $updatedValue;

            $result = $obExpense->createExpense();
            if($result) {
                // ATUALIZA O VALOR DO ORÇAMENTO
                $result = $obBudget->updateBudgetCurrentValue();

                if($result) {
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
            $finalResult = true;
        }
    }

    if($finalResult) {
        http_response_code(200);
        echo json_encode(array('message' => 'Despesa(s) registrada(s) com sucesso'), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
    }

?>