<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    $obPendingIssues = new PendingIssues($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 2];

    $obPendingIssues->User_ID = $userId;
    $obPendingIssues->Pending_Issues_Month_Year = date('m').date('Y');

    // QUERY DE PENDINGISSUES
    $result = $obPendingIssues->read();

    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY NOTIFICATIONS
        $arr_pending_issues = array();
        $arr_pending_issues['data'] = array();
        $arr_pending_issues['pendings'] = 0;
        $arr_pending_issues['total'] = 0;

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

        $result = $obPendingIssues->readTotalPendingIssuesNotDone();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_pending_issues['pendings'] = $Pending_Issues;
        }

        $result = $obPendingIssues->readTotalPendingIssues();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $arr_pending_issues['total'] = $Pending_Issues;
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_pending_issues);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }

    
?>