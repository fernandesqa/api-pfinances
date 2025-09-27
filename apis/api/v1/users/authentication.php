<?php

    require "vendor/autoload.php";
    use \Firebase\JWT\JWT;

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $security = new Security();

    //INSTANCIA O BANCO DE DADOS E A CONEXÃO
    $database = new Database();
    $db = $database->connection();

    //INSTANCIA O OBJETO USERS
    $obUsers = new User($db);

    //INSTANCIA O OBJETO ACCESSTOKEN
    $obAccessToken = new AccessToken($db);

    $data = json_decode(file_get_contents("php://input"));

    $obUsers->User_Email = $data->email;
    $obUsers->User_Password = $data->password;

     //QUERY DE USERS
     $result = $obUsers->getAuthData();

     // OBTEM A QUANTIDADE DE LINHAS
    $num = $result->rowCount();

    if($num > 0) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $id = $row['User_ID'];
        $family_id = $row['Family_ID'];
        $status_id = $row['Status_ID'];
        $role_id = $row['Role_ID'];
        $name = $row['Person_Name'];
        $password2 = $row['User_Password'];
        $first_access = $row['User_First_Access'];
        $reset_requested = $row['User_Reset_Requested'];

        if($obUsers->User_Password == $password2) {
            switch ($status_id) {
                case '1':
                    if($reset_requested=='1') {
                        $obUsers->cancelResetRequest();
                    }
                    $secret_key = $security->get_secret_key();
                    $issuer_claim = $security->get_issuer_claim(); // this can be the servername
                    $audience_claim = $security->get_audience_claim();
                    $issuedat_claim = time(); // issued at
                    $notbefore_claim = $issuedat_claim + 10; //not before in seconds
                    $expire_claim = $issuedat_claim + (60 * 60); // expire time in seconds
                    $token = array(
                        "iss" => $issuer_claim,
                        "aud" => $audience_claim,
                        "iat" => $issuedat_claim,
                        "nbf" => $notbefore_claim,
                        "exp" => $expire_claim,
                        "data" => array(
                            "id" => $id,
                            "familyId" => $family_id,
                            "roleId" => $role_id,
                            "name" => $name,
                            "email" => $obUsers->User_Email
                    ));

                    $obUsers->User_ID = $id;
                    $obUsers->Person_Name = $name;

                    http_response_code(200);
                    $access_token = JWT::encode($token, $secret_key, 'HS256');
                    $access_token = 'Bearer '.$access_token;
                    //SALVA O ACESS TOKEN NA BASE DE DADOS
                    $obAccessToken->User_ID = $id;
                    $obAccessToken->Session_Access_Token = $access_token;
                    $obAccessToken->Session_Expire_At = $expire_claim;
                    $obAccessToken->saveAccessToken();
                    if($first_access==='1') {
                        $first_access = true;
                    } else {
                        $first_access = false;
                    }
                    echo json_encode(
                        array(
                            "message" => "Login realizado com sucesso!",
                            "id" => $id,
                            "familyId" => $family_id,
                            "roleId" => $role_id,
                            "name" => $name,
                            "email" => $obUsers->User_Email,
                            "firstAccess" => $first_access,
                            "accessToken" => $access_token,
                            "expireAt" => $expire_claim
                        ));

                    break;
                
                default:
                    # code...
                    break;
            }
        }
        else{

            http_response_code(401);
            echo json_encode(array("message" => "Dados inválidos!"));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Dados inválidos!"));
    }