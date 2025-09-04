<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUser = new User($db);

    // INSTANCIA O OBJETO PENDINGISSUES
    $obPendingIssues = new PendingIssues($db);

    $endpoint = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $endpoint[count($endpoint) - 1];

    $obUser->User_ID = $userId;

    $result = $obUser->getFamilyId();

    $familyId = '';

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $familyId = $Family_ID;
    }

    $obPendingIssues->User_ID = $userId;
    $obPendingIssues->Family_ID = $familyId;
    $obPendingIssues->Pending_Issues_Month_Year = date("m").date("Y");
    $obPendingIssues->Pending_Issues_Done = 0;
    
    $data = json_decode(file_get_contents("php://input"));
    $totalPendingIssues = count($data->pendingIssues);

    // CADASTRA AS PENDÊNCIAS NA BASE
    for($i=0; $i<$totalPendingIssues; $i++) {
        
        $next = $i + 1;

        $obPendingIssues->Pending_Issues_Description = $data->pendingIssues[$i]->pendingIssueDescription;

        $result = $obPendingIssues->create();

        if($result==='fail') {
            $i = $totalPendingIssues;
            http_response_code(500);
            echo json_encode(array('message' => 'Erro interno, por favor tente mais tarde'), JSON_UNESCAPED_UNICODE);
            
        } else if($result==='success' && $next==$totalPendingIssues) {
            http_response_code(200);
            echo json_encode(array('message' => 'Pendência(s) cadastrada(s) com sucesso'), JSON_UNESCAPED_UNICODE);


        }
    }
    

?>