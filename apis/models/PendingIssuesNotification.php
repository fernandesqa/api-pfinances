<?php

    class PendingIssuesNotification {
        private $conn;
        private $table = 'Pending_Issues_Notification';

        // PROPRIEDADES DA TABELA
        public $User_ID;
        public $Pending_Issues_Notification_Show_Up_Creation;
        public $Pending_Issues_Notification_Show_Up_Reset;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // CONSULTA OS DADOS DA TABELA
        public function read() {
            $query = 'SELECT 
                        Pending_Issues_Notification_Show_Up_Creation,
                        Pending_Issues_Notification_Show_Up_Reset
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        // INSERE UM NOVO USER NA TABELA
        public function create() {
            $query = 'INSERT INTO '.$this->table.' 
                    VALUES (:user_id, 1, 0)';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }

        // ATUALIZA A NOTIFICAÇÃO DE CRIAÇÃO DE PENDÊNCIAS
        public function updateNotificationOfCreation() {
            $query = 'UPDATE '.$this->table.' 
                    SET Pending_Issues_Notification_Show_Up_Creation = :notification_creation 
                    WHERE User_ID = :user_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':notification_creation', $this->Pending_Issues_Notification_Show_Up_Creation);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        // ATUALIZA A NOTIFICAÇÃO DE RESET DE PENDÊNCIAS
        public function updateNotificationOfReset() {
            $query = 'UPDATE '.$this->table.' 
                    SET Pending_Issues_Notification_Show_Up_Reset = :notification_reset 
                    WHERE User_ID = :user_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':notification_reset', $this->Pending_Issues_Notification_Show_Up_Reset);

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