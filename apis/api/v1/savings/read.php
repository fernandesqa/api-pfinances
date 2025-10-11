<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO SAVINGS_CONTROL
    $obSavingsControl = new SavingsControl($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $familyId = $data[count($data) - 1];

    $obSavingsControl->Family_ID = $familyId;

    $result = $obSavingsControl->getSavings();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_savings = array();
        $arr_savings['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_savings_item = array(
                'savingsId' => intval($Savings_Control_ID),
                'savingsDescription' => $Savings_Control_Description,
                'currentValue' => floatval($Savings_Control_Value)
            );
    
            array_push($arr_savings['data'], $arr_savings_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_savings);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }
?>