<?php

    class Session {
        private $conn;
        private $table = 'Session';

        //ROPRIEDADES DA TABELA SESSION
        public $User_ID;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //EXCLUI UMA SESSÃO
        public function delete() {

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