<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    // INSTANCIA O OBJETO USER
    $obUsers = new User($db);

    // INSTANCIA O OBJETO PENDINGISSUESNOTIFICATION
    $obPendingIssuesNotification = new PendingIssuesNotification($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 1];

    $obUsers->User_ID = $userId;
    $obPendingIssuesNotification->User_ID = $userId;

    //QUERY DE PENDINGISSUESNOTIFICATION
    $result = $obPendingIssuesNotification->read();

    $num = $result->rowCount();

    if($num > 0) {

        // ARRAY NOTIFICATIONS
        $arr_notifications = array();
        $arr_notifications['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $notificationCreation = false;
            $notificationReset = false;

            if($Pending_Issues_Notification_Show_Up_Creation=='1') {
                $notificationCreation = true;
            }

            if($Pending_Issues_Notification_Show_Up_Reset=='1') {
                $notificationReset = true;
            }

            $notifications_item = array(
                'pendingIssuesNotificationCreation' => $notificationCreation,
                'pendingIssuesNotificationReset' => $notificationReset
            );

            array_push($arr_notifications['data'], $notifications_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_notifications);

    } else {
        $result = $obUsers->getUserById();

        $num = $result->rowCount();

        if($num > 0) {
            // CADASTRA O USER NA TABELA PENDING_ISSUES_NOTIFICATION
            $result = $obPendingIssuesNotification->create();

            if($result==='success') {
                
                $result = $obPendingIssuesNotification->read();

                // OBTEM A QUANTIDADE DE LINHAS
                $num = $result->rowCount();

                if($num > 0) {
                    // ARRAY NOTIFICATIONS
                    $arr_notifications = array();
                    $arr_notifications['data'] = array();

                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $notifications_item = array(
                            'pendingIssuesNotificationCreation' => $Pending_Issues_Notification_Show_Up_Creation,
                            'pendingIssuesNotificationUpdate' => $Pending_Issues_Notification_Show_Up_Reset
                        );

                        array_push($arr_notifications['data'], $notifications_item);
                    }

                    //CONVERTE EM JSON
                    http_response_code(200);
                    echo json_encode($arr_notifications);

                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro interno, por favor tente mais tarde'), JSON_UNESCAPED_UNICODE);
                }

            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Erro interno, por favor tente mais tarde'), JSON_UNESCAPED_UNICODE);
            }
        }
    }

?>