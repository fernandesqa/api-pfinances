<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO PENDINGISSUESHISTORY
    $obPendingIssuesHistory = new PendingIssuesHistory($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 2];

    $obPendingIssuesHistory->User_ID = $userId;

    $result = $obPendingIssuesHistory->readYears();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_years = array();
        $arr_years['years'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_years_item = array(
                'year' => $YEAR
            );
    
            array_push($arr_years['years'], $arr_years_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_years);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }

?>