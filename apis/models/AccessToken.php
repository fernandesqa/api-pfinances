<?php
    class AccessToken {

        private $conn;
        private $table = 'Session';

        //PROPIEDADES DA TABELA SESSION
        public $User_ID;
        public $Session_Access_Token;
        public $Session_Expire_At;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        public function saveAccessToken() {

            $this->saveOrUpdateAccessToken();

        }

        private function saveOrUpdateAccessToken() {
            $result = $this->read();

            // OBTEM A QUANTIDADE DE LINHAS
            $num = $result->rowCount();

            if($num > 0) {
                //ATUALIZA O ACCESSTOKEN E HORA DE EXPIRAÇÃO
                $result = $this->update();
                if($result == 'success') {
                    return 'success';
                } else {
                    return 'fail';
                }
            } else {
                // REGISTRA A SESSÃO
                $result = $this->save();
                if($result == 'success') {
                    return 'success';
                } else {
                    return 'fail';
                }
            }
        }

        public function isTokenValid() {
            
            $isTokenValid = false;
            $result = $this->readTokenInfo();
            // OBTEM A QUANTIDADE DE LINHAS
            $num = $result->rowCount();
            if($num > 0) {
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $access_token_arr = array(
                        'User_ID' => $User_ID,
                        'Session_Access_Token' => $Session_Access_Token,
                        'Session_Expire_At' => $Session_Expire_At
                    );
                }

                // VERIFICA SE O TOKEN INFORMADO É VÁLIDO, E SE O MESMO NÃO EXPIROU.
                $now = time();
                if($access_token_arr['Session_Access_Token'] == $this->Session_Access_Token && $access_token_arr['Session_Expire_At'] > $now) {
                    return $isTokenValid = true;
                } else {
                    return $isTokenValid = false;
                }
                    
            } else {
                return $isTokenValid = false;
                
            }

        }

        public function deleteAccessToken($userId) {
            $this->User_ID = $userId;

            if($this->delete()) {
                return 'success';
            } else {
                return 'fail';
            }
        }

        private function read() {
            $query = 'SELECT 
                    User_ID
                FROM 
                    ' .$this->table. '
                WHERE User_ID = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA O DADO
            $stmt->bindValue(':user_id', $this->User_ID, PDO::PARAM_INT);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        private function readTokenInfo() {
            $query = 'SELECT 
                    User_ID,
                    Session_Access_Token,
                    Session_Expire_At
                FROM 
                    ' .$this->table. '
                WHERE User_ID = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA O DADO
            $stmt->bindValue(':user_id', $this->User_ID, PDO::PARAM_INT);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        private function save() {

            $query = 'INSERT INTO '.$this->table. '
                    VALUES (:user_id, :access_token, :expire_at)';

                //PREPARA A QUERY
                $stmt = $this->conn->prepare($query);

                //LIGA OS DADOS
                $stmt->bindParam(':user_id', $this->User_ID);
                $stmt->bindParam(':access_token', $this->Session_Access_Token);
                $stmt->bindParam(':expire_at', $this->Session_Expire_At);
                
                //EXECUTA A QUERY
                if($stmt->execute()) {
                    return "success";
                }
                //EXIBE ERRO SE ALGO DER ERRADO
                printf("Error: %s.\n", $stmt->error);
                return "fail";
        }

        private function update() {

            $query = 'UPDATE '.$this->table. '
                    SET 
                        Session_Access_Token = :access_token, 
                        Session_Expire_At = :expire_at
                    WHERE
                        User_ID = :user_id';

                //PREPARA A QUERY
                $stmt = $this->conn->prepare($query);

                //LIGA OS DADOS
                $stmt->bindParam(':access_token', $this->Session_Access_Token);
                $stmt->bindParam(':expire_at', $this->Session_Expire_At);
                $stmt->bindParam(':user_id', $this->User_ID);
                
                //EXECUTA A QUERY
                if($stmt->execute()) {
                    return true;
                }
                //EXIBE ERRO SE ALGO DER ERRADO
                printf("Error: %s.\n", $stmt->error);
                return false;
        }

        private function delete() {

            $query = 'DELETE FROM '.$this->table. ' WHERE User_ID = :user_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

?>