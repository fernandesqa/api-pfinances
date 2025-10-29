<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO REVENUE
    $obRevenue = new Revenue($db);

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

    $obRevenue->Family_ID = $familyId;
    $obRevenue->Revenue_Month_Year = $monthYear;

    $result = $obRevenue->readCurrentMonthTotalRevenue();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_revenues = array();
        $arr_revenues['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_revenues_item = array(
                'month' => $month,
                'year' => $year,
                'value' => $Revenue_Value
            );
    
            array_push($arr_revenues['data'], $arr_revenues_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_revenues);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }
?>