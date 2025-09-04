<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    $obPendingIssuesHistory = new PendingIssuesHistory($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 4];
    $monthYear = $data[count($data) - 2];

    $obPendingIssuesHistory->User_ID = $userId;
    $obPendingIssuesHistory->Pending_Issues_Month_Year = $monthYear;

    // QUERY DE PENDINGISSUES
    $result = $obPendingIssuesHistory->read();

    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY NOTIFICATIONS
        $arr_pending_issues = array();
        $arr_pending_issues['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $done = false;
            if($Pending_Issues_Done == 1) {
                $done = true;
            }

            $pending_issues_item = array(
                'pendingIssueId' => $Pending_Issues_ID,
                'pendingIssueDescription' => $Pending_Issues_Description,
                'done' => $done
            );

            array_push($arr_pending_issues['data'], $pending_issues_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_pending_issues);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }
?>