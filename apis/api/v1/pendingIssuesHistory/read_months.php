<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO PENDINGISSUESHISTORY
    $obPendingIssuesHistory = new PendingIssuesHistory($db);

    $data = explode('/', $_SERVER['REQUEST_URI']);
    $userId = $data[count($data) - 4];
    $year = $data[count($data) - 2];

    $obPendingIssuesHistory->User_ID = $userId;
    $obPendingIssuesHistory->Pending_Issues_Month_Year = $year;

    $result = $obPendingIssuesHistory->readMonths();

    $num = $result->rowCount();

    if($num > 0) {
        $arr_months = array();
        $arr_months['months'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $monthName = '';

            switch($MONTH) {
                case '01':
                    $monthName = 'Janeiro';
                    break;
                case '02':
                    $monthName = 'Fevereiro';
                    break;
                case '03':
                    $monthName = 'Março';
                    break;
                case '04':
                    $monthName = 'Abril';
                    break;
                case '05':
                    $monthName = 'Maio';
                    break;
                case '06':
                    $monthName = 'Junho';
                    break;
                case '07':
                    $monthName = 'Julho';
                    break;
                case '08':
                    $monthName = 'Agosto';
                    break;
                case '09':
                    $monthName = 'Setembro';
                    break;
                case '10':
                    $monthName = 'Outubro';
                    break;
                case '11':
                    $monthName = 'Novembro';
                    break;
                default:
                    $monthName = 'Dezembro';
                    break;

            }

            $arr_months_item = array(
                'month' => $monthName
            );
    
            array_push($arr_months['months'], $arr_months_item);
        }

        //CONVERTE EM JSON
        http_response_code(200);
        echo json_encode($arr_months);
    } else {
        http_response_code(404);
        echo json_encode('Nenhum registro encontrado');
    }

?>