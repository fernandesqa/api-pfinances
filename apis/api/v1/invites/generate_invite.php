<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTACIA O OBJETO USER
    $obUser = new User($db);

    //INSTANCIA O OBJETO FAMILY
    $obFamily = new Family($db);

    //INSTACIA O OBJETO FAMILYINVITES
    $obFamilyInvite = new FamilyInvites($db);

    $data = json_decode(file_get_contents("php://input"));

    $obFamily->Family_ID = $data->familyId;

    $obUser->User_ID = $data->userId;

    $result = $obFamily->readFamilyName();

    // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        // ARRAY INVITES
        $family_arr = array();
        $family_arr['data'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            // GERA O CÓDIGO DO CONVITE
            $inviteCode = strtoupper(substr($data->emailAddress, 0, 3)).date('Y').strtoupper(substr($Family_Name, 0, 3));

        }

        //VERIFICA SE O E-MAIL JÁ ESTÁ CADASTRADO NA BASE
        $obUser->User_Email = $data->emailAddress;
        $result = $obUser->readEmailAddress();

        $num = $result->rowCount();

        if($num==0) {
            //GRAVA OS DADOS DO CONVITE E OS DADOS DO USUÁRIO NA BASE
            $obFamilyInvite->Family_Invite_Email = $data->emailAddress;

            $result = $obFamilyInvite->read();

            $num = $result->rowCount();

            if($num==0) {
                //GRAVA O CÓDIGO DO CONVITE NA TABELA
                $obFamilyInvite->Family_ID = $data->familyId;
                $obFamilyInvite->Family_Invite_Code = $inviteCode;
                
                $result = $obFamilyInvite->create();

                if($result==='success') {
                    $obUser->Family_ID = $data->familyId;
                    $obUser->Status_ID = 1;
                    $obUser->Role_ID = 2;
                    $obUser->User_First_Access = 1;

                    $result = $obUser->createDependent();

                    if($result==='success') {
                        echo json_encode(array('emailAddress' => $data->emailAddress, 'inviteCode' => $inviteCode), JSON_UNESCAPED_UNICODE);
                        //Envia o e-mail com os dados do invite
                        sendEmail($inviteCode);
                    } else {
                        http_response_code(500);
                        echo json_encode(array('message' => 'Erro ao gravar os dados do usuário'), JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro ao gravar os dados do convite'), JSON_UNESCAPED_UNICODE);
                }
            } else {

                //GRAVA OS DADOS DO USUÁRIO NA BASE
                $obUser->Family_ID = $data->familyId;
                $obUser->Status_ID = 1;
                $obUser->Role_ID = 2;
                $obUser->User_First_Access = 1;

                $result = $obUser->createDependent();

                if($result==='success') {
                    echo json_encode(array('emailAddress' => $data->emailAddress, 'inviteCode' => $inviteCode), JSON_UNESCAPED_UNICODE);
                    //Envia o e-mail com os dados do invite
                    sendEmail($inviteCode);

                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Erro ao gravar os dados do usuário'), JSON_UNESCAPED_UNICODE);
                }
            }

        } else {
            http_response_code(403);
            echo json_encode(array('message' => 'E-mail em uso'), JSON_UNESCAPED_UNICODE);
        }
    } else {
        //SEM REGISTROS
        http_response_code(404);
        echo json_encode(array('message' => 'Família não encontrada'), JSON_UNESCAPED_UNICODE);
    }

    function sendEmail($inviteCode) {

        //INSTANCIA O BANCO DE DADOS E A CONEXÃO
        $database = new Database();
        $db = $database->connection();

        //INSTANCIA O OBJETO MAIL
        $mail = new Mail();

        $data = json_decode(file_get_contents("php://input"));

        $mail->recipient_email = $data->emailAddress;
        $mail->recipient_name = $data->name;

        //INSTACIA O OBJETO USER
        $obUser = new User($db);

        $obUser->User_ID = $data->userId;

        //INSTANCIA O OBJETO PERSON
        $obPerson = new Person($db);

        //INSTANCIA O OBJETO FAMILY
        $obFamily = new Family($db);

        $obFamily->Family_ID = $data->familyId;

        $subject = 'Convite de acesso';

        $result = $obUser->getPersonIdAndFamilyId();

        // OBTEM A QUANTIDADE DE LINHAS
        $num = $result->rowCount();

        if($num > 0) {

            $personId = '';

            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $personId = $Person_ID;
            }

            $obPerson->Person_ID = $personId;

            $result = $obPerson->readPersonName();

            // OBTEM A QUANTIDADE DE LINHAS
            $num = $result->rowCount();

            if($num > 0) {
                $senderName = '';
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $senderName = $Person_Name;
                }

                $result = $obFamily->readFamilyName();

                // OBTEM A QUANTIDADE DE LINHAS
                $num = $result->rowCount();

                if($num > 0) {
                    $familyName = '';
                    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);

                        $familyName = $Family_Name;
                    }

                    $body = 'Olá '.$mail->recipient_name.',<br>'.$senderName.' 
                    te convida para fazer parte da família <b>'.$familyName.'</b> no portal Pfinances.
                    <p>Seguem abaixo os seus dados e instruções para realizar o primeiro acesso no sistema:</p><br>
                    <p><b>E-mail de login:</b> '.$data->emailAddress.'</p>
                    <p><b>Código do Convite:</b> '.$inviteCode.'</p>
                    <p><b>Página de login do portal:</b> <a href="https://pfinances.com.br/app/login">https://pfinances.com.br/app/login</a></p>
                    <br><b>Siga os seguintes passos:</b><br>
                    <ol><li>Acesse a página de login</li>
                    <li>Acesse a página de primeiro acesso pelo botão <b>Primeiro Acesso</b></li>
                    <li>Selecione a opção: <b>Recebi o convite de um familiar</b></li>
                    <li>Informe o código do convite informado acima</li>
                    <li>Informe o e-mail de login informado acima</li>
                    <li>Valide o convite e conclua o cadastro</li></ol>
                    <br><p>Seja muito bem vindo(a)!</p><br>
                    <p>Atenciosamente,</p>
                    <p>Equipe Pfinances</p><br><img src="https://pfinances.com.br/img/logo-pfinances.png" alt="logo" />';
                    
                    $altbody = 'Olá '.$mail->recipient_name.','.$senderName.' 
                    te convida para fazer parte da família '.$familyName.' no portal Pfinances.
                    Seguem abaixo os seus dados e instruções para realizar o primeiro acesso no sistema:
                    E-mail de login: '.$data->emailAddress.'
                    Código do Convite: '.$inviteCode.'
                    Página do Primeiro Acesso: https://pfinances.com.br/app/login
                    Siga os seguintes passos:
                    1. Acesse a página de login
                    2. Acesse a página de primeiro acesso pelo botão Primeiro Acesso
                    3. Selecione a opção: Recebi o convite de um familiar
                    4. Informe o código do convite informado acima
                    5. Informe o e-mail de login informado acima
                    6. Valide o convite e conclua o cadastro
                    Seja muito bem vindo(a)!
                    Atenciosamente,
                    Equipe Pfinances';
                    $mail->send_email($subject, $body, $altbody);
                }
            }
        }
    }

?>