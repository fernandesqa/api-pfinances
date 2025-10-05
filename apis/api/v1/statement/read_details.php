<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO STATEMENT_DETAILS
    $obStatementDetails = new StatementDetails($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 5];
    $monthYear = $data[count($data) - 3];
    $budgetId = $data[count($data) - 1];
    
    if(strlen($monthYear)==6) {
        $monthYear = substr($monthYear, 0, 2).'/'.substr($monthYear, 2, 6);
    } else {
        $monthYear = substr($monthYear, 0, 1).'/'.substr($monthYear, 1, 5);
    }

    $obStatementDetails->Family_ID = $familyId;
    $obStatementDetails->Budget_ID = $budgetId;
    $obStatementDetails->Statement_Details_Date = $monthYear;

    $result = $obStatementDetails->getStatementDetailsByPeriod();

    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY STATEMENT_DETAILS
        $arr_statement_details = array();
        $arr_statement_details['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_statement_details_item = array(
                'author' => $Statement_Details_Author,
                'description' => $Statement_Details_Description,
                'value' => $Statement_Details_Value,
                'date' => $Statement_Details_Date
            );

            array_push($arr_statement_details['data'], $arr_statement_details_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_statement_details);
    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Nenhum registro encontrado'), JSON_UNESCAPED_UNICODE);
    }

?>