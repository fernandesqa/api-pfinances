<?php

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    $mail = new Mail();

    $monthYear = date('m').date('Y');

    $pendingIssues = new PendingIssues($db);

    $pendingIssues->Pending_Issues_Month_Year = $monthYear;
    
    $arr_users = array();
    $arr_users['data'] = array();

    $arr_pending_issues = array();

    $arr_users_data = array();
    $arr_users_data['data'] = array();

    //Consulta o id de usuários com pendências não concluídas no mês atual
    $result = $pendingIssues->readUsersWithPendingIssuesNotDone();


    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $users_item = array(
                'userId' => $User_ID
            );

        array_push($arr_users['data'], $users_item);
    }

    //Se houver pelo menos 1 usuário com pendências em aberto, então segue com o envio de e-mail
    if(count($arr_users['data']) > 0) {
        //Consulta o nome, e-mail e pendendências em aberto dos usuários
        for ($i=0; $i < count($arr_users['data']); $i++) {

            $pendingIssues->User_ID = $arr_users['data'][$i]['userId'];
            $result = $pendingIssues->readNameAndEmail();

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $users_data_item = array(
                        'userId' => $User_ID,
                        'name' => $Person_Name,
                        'email' => $User_Email
                    );

                    array_push($arr_users_data['data'], $users_data_item);
            }

            $list = $pendingIssues->readPendingIssuesList();
            $pendingIssues->$User_ID = $arr_users['data'][$i]['userId'];

            while($row = $list->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $pending_issue_item = array(
                        'userId' => $User_ID,
                        'description' => $Pending_Issues_Description
                    );  

                array_push($arr_pending_issues, $pending_issue_item);     
            }
        }

        //Organiza as pendências por usuário e envia o e-mail aos usuários
        for($i=0; $i < count($arr_users_data['data']); $i++) {
            $arr_list = array();
            for ($j=0; $j < count($arr_pending_issues); $j++) {
                if ($arr_users_data['data'][$i]['userId']==$arr_pending_issues[$j]['userId']) {
                    $list_item = '<li>'.$arr_pending_issues[$j]['description'].'</li>';
                    array_push($arr_list, $list_item);
                }
            }

            $mail->recipient_email = $arr_users_data['data'][$i]['email'];
            $mail->recipient_name = $arr_users_data['data'][$i]['name'];

            $subject = 'Pendências não concluídas';

            for ($k=0; $k < count($arr_list); $k++) {
                if($k==0 && $k+1 == count($arr_list)){
                    $pendingIssuesList = '<ul>'.$arr_list[$k].'</ul>';
                }else if($k==0) {
                    $pendingIssuesList = '<ul>'.$arr_list[$k];        
                }else if($k+1 == count($arr_list)) {
                    $pendingIssuesList = $pendingIssuesList .= $arr_list[$k].'</ul>'; 
                } else {
                    $pendingIssuesList = $pendingIssuesList .= $arr_list[$k];
                }
            }
            $body = '<b>Olá, '.$arr_users_data['data'][$i]['name'].'</b><br>Segue abaixo as suas pendências ainda não concluídas: <br>'.$pendingIssuesList.'<br><img src="https://pfinances.com.br/img/logo-pfinances.png" alt="logo" />';
            $altbody = 'Olá, '.$arr_users_data['data'][$i]['name'].'Segue abaixo as suas pendências ainda não concluídas: ';

            $result = $mail->send_email($subject, $body, $altbody);

            //CONVERTE EM JSON
            http_response_code(200);
            echo json_encode($result);
        }
    }
?>