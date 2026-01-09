<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO BUDGET
    $obBudgetControl = new BudgetControl($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 1];

    $obBudgetControl->Family_ID = $familyId;
    
    $result = $obBudgetControl->getAllBudgets();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_budgets = array();
        $arr_budgets['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_budgets_item = array(
                'budgetId' => intval($Budget_Control_ID),
                'budgetDescription' => $Budget_Control_Description,
                'budgetValue' => floatval($Budget_Control_Original_Value)
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