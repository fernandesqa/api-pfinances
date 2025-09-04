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

    // INSTANCIA O OBJETO PENDINGISSUESHISTORY
    $obPendingIssuesHistory = new PendingIssuesHistory($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 2];

    $obPendingIssues->User_ID = $userId;
    $obPendingIssues->Pending_Issues_Month_Year = date('m').date('Y');

    $result = $obPendingIssues->readAll();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obPendingIssuesHistory->Pending_Issues_ID = $Pending_Issues_ID;
        $obPendingIssuesHistory->User_ID = $User_ID;
        $obPendingIssuesHistory->Family_ID = $Family_ID;
        $obPendingIssuesHistory->Pending_Issues_Description = $Pending_Issues_Description;
        $obPendingIssuesHistory->Pending_Issues_Month_Year = $Pending_Issues_Month_Year;
        $obPendingIssuesHistory->Pending_Issues_Done = $Pending_Issues_Done;

        $obPendingIssuesHistory->create();
    }

    $result = $obPendingIssues->reset();

    if($result===false) {
        http_response_code(500);
        echo json_encode(array('message' => 'Erro interno, por favor tente novamente mais tarde'), JSON_UNESCAPED_UNICODE);
        
    } else if($result===true) {
        http_response_code(200);
        echo json_encode(array('message' => 'Atualização realizada com sucesso'), JSON_UNESCAPED_UNICODE);

    }

?>