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
    $familyId = $data[count($data) - 3];
    $monthYear = $data[count($data) - 1];

    $obBudget->Family_ID = $familyId;
    $obBudget->Budget_Month_Year = $monthYear;
    
    $result = $obBudget->getBudgetUsageData();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_budgets = array();
        $arr_budgets['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $totalAvailable;
            $totalUsed;

            if(floatval($Total_Available)==0) {
                $totalAvailable = floatval($Total_Used);
                $totalUsed = 0;
            } else if(floatval($Total_Used)==0) {
                $totalAvailable = 0;
                $totalUsed = $Total_Available;
            } else {
                $totalUsed = $Total_Used;
                $totalAvailable = $Total_Available;
            }

            $arr_budgets_item = array(
                'description' => $Budget_Description,
                'icon' => $Icon,
                'totalSet' => floatval($Total_Set),
                'totalUsed' => floatval($totalUsed),
                'totalAvailable' => floatval($totalAvailable)
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