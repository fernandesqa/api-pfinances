<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    //INSTANCIA O OBJETO EXPENSE
    $obExpense = new Expense($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 3];
    $monthYear = $data[count($data) - 1];

    $obBudget->Family_ID = $familyId;
    $obBudget->Budget_Month_Year = $monthYear;
    $obExpense->Family_ID = $familyId;
    $obExpense->Expense_Billing_Month_Year = $monthYear;
    
    $result = $obBudget->getBudgetUsageData();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_budgets = array();
        $arr_budgets['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $obExpense->Budget_ID = $Budget_Control_ID;

            $expenseResult = $obExpense->getCategoriesValue();

            $num = $expenseResult->rowCount();

            $arr_categories['categories'] = array();

            if($num>0) {

                if ($num==1) {
                    while($expenseRow = $expenseResult->fetch(PDO::FETCH_ASSOC)) {
                        extract($expenseRow);

                        $arr_categories_item = array(
                            "category" => $Category,
                            "percentage" => floatval($Percentage),
                            "value" => floatval($Value)
                        );

                        array_push($arr_categories['categories'], $arr_categories_item);
                    }
                } else {
                    $expenseResult = $obExpense->getCategoriesValue2();

                    while($expenseRow = $expenseResult->fetch(PDO::FETCH_ASSOC)) {
                        extract($expenseRow);

                        $arr_categories_item = array(
                            "category" => $Category,
                            "percentage" => floatval($Percentage),
                            "value" => floatval($Value)
                        );

                        array_push($arr_categories['categories'], $arr_categories_item);
                    }
                }
                
            }



            $arr_budgets_item = array(
                'description' => $Budget_Description,
                'icon' => $Icon,
                'totalSet' => floatval($Total_Set),
                'totalUsed' => floatval($Total_Used),
                'totalAvailable' => floatval($Total_Available),
                'categories' => $arr_categories['categories']
            );

            array_push($arr_budgets['data'], $arr_budgets_item);
        }
        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_budgets);
    } else {
        //CONVERTE EM JSON
        http_response_code(404);
        echo json_encode(array("message" => "Nenhum registro encontrado"), JSON_UNESCAPED_UNICODE);
    }

?>