<?php

    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: PUT');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO PENDINGISSUESNOTIFICATION
    $obPendingIssuesNotification = new PendingIssuesNotification($db);

    $data = json_decode(file_get_contents("php://input"));

    $obPendingIssuesNotification->User_ID = $data->userId;
    $notificateReset;
    
    if($data->notificateReset==true) {
        $notificateReset = 1;
    } else {
        $notificateReset = 0;
    }
    $obPendingIssuesNotification->Pending_Issues_Notification_Show_Up_Reset = $notificateReset;

    // ATUALIZA O STATUS DE NOTIFICAÇÃO DE CRIAÇÃO DE PENDÊNCIAS
    if($obPendingIssuesNotification->updateNotificationOfReset()) {
        http_response_code(201);
        echo json_encode(
            array('message' => 'Status atualizado com sucesso'), JSON_UNESCAPED_UNICODE
        );
    } else {
        http_response_code(500);
        echo json_encode(
           array('message' => 'Erro ao atualizar status'), JSON_UNESCAPED_UNICODE
       );
    }
?>