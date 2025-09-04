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
    $familyId = $data[count($data) - 2];
    $monthYear = '';
    
    if(date('m')-1==0) {
        $monthYear = '12'.date('Y')-1;
    } else {
        $monthYear = date('m')-1 .date('Y');
    }

    $obRevenue->Family_ID = $familyId;
    $obRevenue->Revenue_Month_Year = $monthYear;

    $result = $obRevenue->read();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_revenues = array();
        $arr_revenues['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_revenues_item = array(
                'description' => $Revenue_Description,
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