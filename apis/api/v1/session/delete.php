<?php
     //HEADERS
     header('Access-Control-Allow-Origin: *');
     header('Content-Type: application/json');
     header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
 
     //INSTANCIA O BANCO DE DADOS E A CONEXÃO
     $database = new Database();
     $db = $database->connection();
 
     //INSTACIA O OBJETO USERS
     $session = new Session($db);

     //OBTÉM O ID DO USUÁRIO
     $data = explode('/', $_SERVER['REQUEST_URI']);
     $session->User_ID = $data[count($data) - 1];

     //EXCLUI O USUÁRIO
     if($session->delete()) {
         http_response_code(201);
         echo json_encode(
             array('message' => 'Sessão Excluída Com Sucesso'), JSON_UNESCAPED_UNICODE
         );
     } else {
        http_response_code(500);
        echo json_encode(
            array('message' => 'Erro ao Excluir Sessão'), JSON_UNESCAPED_UNICODE
        );
     }
