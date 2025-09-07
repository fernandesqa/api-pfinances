<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    $obPendingIssues = new PendingIssues($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 1];

    $obPendingIssues->User_ID = $userId;

    // QUERY DE PENDINGISSUES
    $result = $obPendingIssues->readUserPendingIssues();

    $arr_pending_issues = array();
    $arr_pending_issues['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $pending_issues_item = array(
                'pendingIssueId' => $Pending_Issues_ID,
                'pendingIssueDescription' => $Pending_Issues_Description
            );

        array_push($arr_pending_issues['data'], $pending_issues_item);
    }

    //CONVERTE EM JSON
    http_response_code(200);
    echo json_encode($arr_pending_issues);


?>