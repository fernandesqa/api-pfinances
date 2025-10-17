<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO BUDGET
    $obBudget = new Budget($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 1];

    $obBudget->Family_ID = $familyId;
    $month;
    $year;
    date_default_timezone_set('America/Sao_Paulo');
    if(date('m')-1 == 0) {
        $month = date('m')-1;
        $year = date('Y')-1;    
    } else {
        $month = date('m')-1;
        $year = date('Y');    
    }
    
    $monthYear = $month.$year;
    $obBudget->Budget_Month_Year = $monthYear;
    
    $result = $obBudget->getPreviousBudgets();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_budgets = array();
        $arr_budgets['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_budgets_item = array(
                'budgetDescription' => $Description,
                'budgetValue' => floatval($Budget_Value)
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