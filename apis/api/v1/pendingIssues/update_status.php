<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: PUT');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO PENDINGISSUES
    $obPendingIssues = new PendingIssues($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 2];

    $obPendingIssues->User_ID = $userId;
    $obPendingIssues->Pending_Issues_Month_Year = date('m').date('Y');

    $data = json_decode(file_get_contents("php://input"));
    $totalPendingIssues = count($data->pendingIssues);

    // CADASTRA AS PENDÊNCIAS NA BASE
    for($i=0; $i<$totalPendingIssues; $i++) {
        
        $next = $i + 1;

        $obPendingIssues->Pending_Issues_ID = $data->pendingIssues[$i]->pendingIssueId;

        $done = false;

        if($data->pendingIssues[$i]->done==true) {
            $done = true;
        }

        $obPendingIssues->Pending_Issues_Done = $done;

        $result = $obPendingIssues->updateSatus();

        if($result===false) {
            $i = $totalPendingIssues;
            http_response_code(500);
            echo json_encode(array('message' => 'Erro interno, por favor tente mais tarde'), JSON_UNESCAPED_UNICODE);
            
        } else if($result===true && $next==$totalPendingIssues) {
            http_response_code(200);
            echo json_encode(array('message' => 'Atualização realizada com sucesso'), JSON_UNESCAPED_UNICODE);

        }
    }

?>