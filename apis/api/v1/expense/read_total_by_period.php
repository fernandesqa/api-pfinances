<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO REVENUE
    $obExpense = new Expense($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 3];
    $monthYear = $data[count($data) - 1];

    $month;
    $year;

    if(strlen($monthYear)==6) {
        $month = substr($monthYear, 0, 2);
        $year = substr($monthYear, 2, 6);
    } else {
        $month = substr($monthYear, 0, 1);
        $year = substr($monthYear, 1, 5);
    }

    $obExpense->Family_ID = $familyId;
    $obExpense->Expense_Billing_Month_Year = $monthYear;

    $result = $obExpense->readCurrentMonthTotalExpense();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_expenses = array();
        $arr_expenses['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_expenses_item = array(
                'month' => $month,
                'year' => $year,
                'value' => $Expense_Value
            );
    
            array_push($arr_expenses['data'], $arr_expenses_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_expenses);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }
?>