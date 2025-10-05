<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO STATEMENT
    $obStatement = new Statement($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 3];
    $monthYear = $data[count($data) - 1];
    
    if(strlen($monthYear)==6) {
        $monthYear = substr($monthYear, 0, 2).'/'.substr($monthYear, 2, 6);
    } else {
        $monthYear = substr($monthYear, 0, 1).'/'.substr($monthYear, 1, 5);
    }

    $obStatement->Family_ID = $familyId;
    $obStatement->Statement_Date = $monthYear;
    
    $result = $obStatement->getStatementByPeriod();

    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY STATEMENT
        $arr_statement = array();
        $arr_statement['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_statement_item = array(
                'author' => $Statement_Author,
                'description' => $Statement_Description,
                'value' => $Statement_Value,
                'date' => $Statement_Date,
                'origin' => $Statement_Origin,
                'destination' => $Statement_Destination,
                'budgetId' => $Budget_ID
            );

            array_push($arr_statement['data'], $arr_statement_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_statement);

    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Nenhum registro encontrado'), JSON_UNESCAPED_UNICODE);
    }

?>