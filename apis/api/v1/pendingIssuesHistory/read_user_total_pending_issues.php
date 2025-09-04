<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    $obPendingIssuesHistory = new PendingIssuesHistory($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 2];

    $obPendingIssuesHistory->User_ID = $userId;

    // QUERY DE PENDINGISSUES
    $result = $obPendingIssuesHistory->readUserTotalPendingIssues();

    $arr_pending_issues = array();
    $arr_pending_issues['total'] = 0;

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $arr_pending_issues['total'] = $Pending_Issues;
    }

    //CONVERTE EM JSON
    http_response_code(200);
    echo json_encode($arr_pending_issues);

?>