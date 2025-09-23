<?php

    include_once __DIR__.'/Database.php';
    include_once __DIR__.'/security.php';
    include_once __DIR__.'/mail.php';
    include_once __DIR__.'/models/Invites.php'; 
    include_once __DIR__.'/models/Person.php';
    include_once __DIR__.'/models/Family.php';
    include_once __DIR__.'/models/User.php';
    include_once __DIR__.'/models/AccessToken.php';
    include_once __DIR__.'/generalFunctions.php';
    include_once __DIR__.'/models/Session.php';
    include_once __DIR__.'/models/FamilyInvites.php';
    include_once __DIR__.'/models/PendingIssuesNotification.php';
    include_once __DIR__.'/models/PendingIssues.php';
    include_once __DIR__.'/models/PendingIssuesHistory.php';
    include_once __DIR__.'/models/Revenue.php';
    require __DIR__.'/vendor/autoload.php';

    // Load the .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    $obAccessToken = new AccessToken($db);

    //OBTÉM O PREFIX DA URL DA API
    if($_ENV['ENVIRONMENT']=='DEV') {
        $prefixApi = $_ENV['DEV_URL'];
    } else {
        $prefixApi = $_ENV['PROD_URL'];
    }
    
    $request = $_SERVER['REQUEST_URI'];

    $generalFunctions = new generalFunctions();

    switch($request) {
        case $prefixApi.'/invite' :
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST' :
                    $data = json_decode(file_get_contents("php://input"));
                    if($data->role === 'holder') {
                        require __DIR__ . '/api/v1/invites/validate_system_invite.php';
                    } else if ($data->role === 'dependent') {
                        require __DIR__ . '/api/v1/invites/validate_family_invite.php';
                    } else {
                        http_response_code(400);
                        echo json_encode(
                            array('message' => 'Dado inválido no campo role'), JSON_UNESCAPED_UNICODE
                        );
                    }
                    break;
            }
            break;
        case $prefixApi.'/generate-invite' :
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST' :
                    $data = json_decode(file_get_contents("php://input"));
                    if($data->userId && $data->familyId && $data->name && $data->emailAddress) {
                        //VALIDA O ACCESSTOKEN E ENTÃO PROCURA PELO USUÁRIO INFORMADO
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $data->userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                require __DIR__ . '/api/v1/invites/generate_invite.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(
                            array('message' => 'É obrigatório informar os campos userId, familyId, name e emailAddress'), JSON_UNESCAPED_UNICODE
                        );
                    } 
                    break;
            }
            break;
        case $prefixApi.'/holder-first-access' :
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST' :
                    $data = json_decode(file_get_contents("php://input"));
                    if($data->name && $data->emailAddress && $data->password && $data->familyName) {
                        require __DIR__ . '/api/v1/firstAccess/create_holder.php';
                    } else {
                        http_response_code(400);
                        echo json_encode(
                            array('message' => 'Os campos name, emailAddress, password e familyName são obrigatórios'), JSON_UNESCAPED_UNICODE
                        );
                    }
                    break;

            }
            break;

        case $prefixApi.'/dependent-first-access' :
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST' :
                    $data = json_decode(file_get_contents("php://input"));
                    if($data->name && $data->emailAddress && $data->password) {
                        require __DIR__ . '/api/v1/firstAccess/create_dependent.php';
                    } else {
                        http_response_code(400);
                        echo json_encode(
                            array('message' => 'Os campos name, emailAddress e password são obrigatórios'), JSON_UNESCAPED_UNICODE
                        );
                    }
                    break;

            }
            break;

        case $prefixApi.'/auth' :
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST' :
                    require __DIR__ . '/api/v1/users/authentication.php';
                    break;
                
                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/session') :
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'DELETE' :
                    //OBTÉM O ID DO USUÁRIO
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $id = $data[count($data) - 1];

                    if($id != 'session' && $id != '/' && $id != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO ENCERRA A SESSÃO
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $id;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                //EXCLUI UMA SESSÃO
                                require __DIR__ .'/api/v1/session/delete.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('Id is required');
                    }
                    
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }

            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/family-invites/users/'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 3];
                    $familyId = $data[count($data) - 1];
                    
                    if($userId != 'family-invites' && 
                    $userId != 'users' && 
                    $userId != 'families' && 
                    $userId != '/' && 
                    $userId != '' &&
                    $familyId != 'family-invites' && 
                    $familyId != 'users' && 
                    $familyId != 'families' && 
                    $familyId != '/' && 
                    $familyId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                //EXCLUI UMA SESSÃO
                                require __DIR__ .'/api/v1/invites/read_all.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and familyId are required');
                    }
                    break;
                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/users'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 3];
                    $familyId = $data[count($data) - 1];
                    
                    if($userId != 'users' && 
                    $userId != 'families' && 
                    $userId != '/' && 
                    $userId != '' &&
                    $familyId != 'users' && 
                    $familyId != 'families' && 
                    $familyId != '/' && 
                    $familyId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                //EXCLUI UMA SESSÃO
                                require __DIR__ .'/api/v1/users/read.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and familyId are required');
                    }
                    break;
                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;
        
        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-notification/users'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 1];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA O STATUS DE NOTIFICAÇÕES DE PENDÊNCIAS DO USUÁRIO
                                require __DIR__ .'/api/v1/pendingIssuesNotification/read.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-notification-creation'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'PATCH':
                    $data = json_decode(file_get_contents("php://input"));
                    if(isset($data->userId) && isset($data->notificateCreation)) {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $data->userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // ATUALIZA O STATUS DE NOTIFICAÇÃO DE CRIAÇÃO DE PENDÊNCIAS DO USUÁRIO
                                require __DIR__ .'/api/v1/pendingIssuesNotification/update_notification_of_creation.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode('The fields userId and notificateCreation are required');
                    }
                    break;
                
                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/create-pending-issues/users'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 1];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CADASTRA AS PENDÊNCIAS DO USUÁRIO
                                require __DIR__ .'/api/v1/pendingIssues/create.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/users') && str_contains($_SERVER['REQUEST_URI'], '/current-month'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 2];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssues/read_current_month.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/users') && str_contains($_SERVER['REQUEST_URI'], '/total'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 2];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssues/read_user_total_pending_issues.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/users') && str_contains($_SERVER['REQUEST_URI'], '/update-status'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'PATCH':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 2];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        $data = json_decode(file_get_contents("php://input"));
                        if($data->pendingIssues[0]->pendingIssueId && $data->pendingIssues[0]->done==true || $data->pendingIssues[0]->done==false) {
                            //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                            $all_headers = getallheaders();
                            $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                            if($authorizationHeaderInformed) {
                                $accessToken = $generalFunctions->getAccessToken($all_headers);
                                $obAccessToken->User_ID = $userId;
                                $obAccessToken->Session_Access_Token = $accessToken;
                                if($obAccessToken->isTokenValid()) {
                                    // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                    require __DIR__ .'/api/v1/pendingIssues/update_status.php';
                                } else {
                                    http_response_code(401);
                                    echo json_encode('Invalid token');    
                                }
                            } else {
                                http_response_code(400);
                                echo json_encode('x-api-key header is required');
                            }
                        } else {
                            http_response_code(401);
                            echo json_encode('The fields pendingIssueId and done are required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('The variable path userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/users') && str_contains($_SERVER['REQUEST_URI'], '/update-description'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'PUT':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 2];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        $data = json_decode(file_get_contents("php://input"));
                        if($data->pendingIssues[0]->pendingIssueId && $data->pendingIssues[0]->pendingIssueDescription && $data->pendingIssues[0]->done==true || $data->pendingIssues[0]->done==false) {
                            //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                            $all_headers = getallheaders();
                            $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                            if($authorizationHeaderInformed) {
                                $accessToken = $generalFunctions->getAccessToken($all_headers);
                                $obAccessToken->User_ID = $userId;
                                $obAccessToken->Session_Access_Token = $accessToken;
                                if($obAccessToken->isTokenValid()) {
                                    // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                    require __DIR__ .'/api/v1/pendingIssues/update.php';
                                } else {
                                    http_response_code(401);
                                    echo json_encode('Invalid token');    
                                }
                            } else {
                                http_response_code(400);
                                echo json_encode('x-api-key header is required');
                            }
                        } else {
                            http_response_code(401);
                            echo json_encode('The fields pendingIssueId, pendingIssueDescription and done are required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('The variable path userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/users') && str_contains($_SERVER['REQUEST_URI'], '/delete'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'DELETE':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 4];
                    $pendingIssueId = $data[count($data) - 2];
                    if($userId != 'users' && 
                        $userId != '/' && 
                        $userId != '' && 
                        $pendingIssueId != 'pending-issues-id' && 
                        $pendingIssueId != '/' && $pendingIssueId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssues/delete.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('The variables path userId and pendingIssueId are required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;
        
        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/users'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 1];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssues/read_user_pending_issues.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-notification-reset'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'PATCH':
                    $data = json_decode(file_get_contents("php://input"));
                    if(isset($data->userId) && isset($data->notificateReset)) {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $data->userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // ATUALIZA O STATUS DE NOTIFICAÇÃO DE CRIAÇÃO DE PENDÊNCIAS DO USUÁRIO
                                require __DIR__ .'/api/v1/pendingIssuesNotification/update_notification_of_reset.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode('The fields userId and notificateReset are required');
                    }
                    break;
                
                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/reset-pending-issues'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'PUT':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 1];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // ATUALIZA O STATUS DE NOTIFICAÇÃO DE CRIAÇÃO DE PENDÊNCIAS DO USUÁRIO
                                require __DIR__ .'/api/v1/pendingIssues/reset.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode('The variable path userId is required');
                    }
                    break;
                
                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-history/users') && str_contains($_SERVER['REQUEST_URI'], '/total'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 2];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssuesHistory/read_user_total_pending_issues.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-history/users') && str_contains($_SERVER['REQUEST_URI'], '/years') && !str_contains($_SERVER['REQUEST_URI'], '/months'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 2];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssuesHistory/read_years.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId is required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-history/users') && str_contains($_SERVER['REQUEST_URI'], '/years/') && str_contains($_SERVER['REQUEST_URI'], '/months'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 4];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssuesHistory/read_months.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and year are required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues-history/users') && str_contains($_SERVER['REQUEST_URI'], '/data'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 4];
                    if($userId != 'users' && $userId != '/' && $userId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CONSULTA AS PENDÊNCIAS DO USUÁRIO NO MÊS E ANO ATUAL
                                require __DIR__ .'/api/v1/pendingIssuesHistory/read_month_year.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and monthYear are required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/revenues/users/') && str_contains($_SERVER['REQUEST_URI'], '/periods') && str_contains($_SERVER['REQUEST_URI'], '/create'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 4];
                    $monthYear = $data[count($data) - 2];
                    if($userId != 'users' && 
                        $userId != '/' && 
                        $userId != '' &&
                        $monthYear != 'periods' &&
                        $monthYear != '/' &&
                        $monthYear != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CADASTRA A RECEITA
                                require __DIR__ .'/api/v1/revenue/create.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and monthYear are required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/revenues/users/') && str_contains($_SERVER['REQUEST_URI'], '/families') && str_contains($_SERVER['REQUEST_URI'], '/last-month'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 4];
                    $familyId = $data[count($data) - 2];
                    if($userId != 'users' && 
                        $userId != '/' && 
                        $userId != '' &&
                        $familyId != 'families' &&
                        $familyId != '/' &&
                        $familyId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CADASTRA A RECEITA
                                require __DIR__ .'/api/v1/revenue/read_last_month.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and familyId are required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/revenues/users/') && str_contains($_SERVER['REQUEST_URI'], '/families') && str_contains($_SERVER['REQUEST_URI'], '/current-month'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $data = explode('/', $_SERVER['REQUEST_URI']);
                    $userId = $data[count($data) - 4];
                    $familyId = $data[count($data) - 2];
                    if($userId != 'users' && 
                        $userId != '/' && 
                        $userId != '' &&
                        $familyId != 'families' &&
                        $familyId != '/' &&
                        $familyId != '') {
                        //VALIDA O ACCESSTOKEN E ENTÃO BUSCA OS CONVITES
                        $all_headers = getallheaders();
                        $authorizationHeaderInformed = $generalFunctions->xApiKeyHeaderInformed($all_headers);
                        if($authorizationHeaderInformed) {
                            $accessToken = $generalFunctions->getAccessToken($all_headers);
                            $obAccessToken->User_ID = $userId;
                            $obAccessToken->Session_Access_Token = $accessToken;
                            if($obAccessToken->isTokenValid()) {
                                // CADASTRA A RECEITA
                                require __DIR__ .'/api/v1/revenue/read_current_month.php';
                            } else {
                                http_response_code(401);
                                echo json_encode('Invalid token');    
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode('x-api-key header is required');
                        }
                    } else {
                        http_response_code(401);
                        echo json_encode('userId and familyId are required');
                    }
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        case str_contains($_SERVER['REQUEST_URI'], $prefixApi.'/pending-issues/send-email'):
            //VERIFICA O MÉTODO ENVIADO NA REQUEST
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    require __DIR__ .'/api/v1/pendingIssues/send_email_pending_issues.php';
                    break;

                default:
                    http_response_code(403);
                    echo json_encode('Method not supported');
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(
                array('message' => 'url não encontrada'), JSON_UNESCAPED_UNICODE
            );
            break;
    }

?>