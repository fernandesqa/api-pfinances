<?php

    class StatementDetails {
        private $conn;
        private $table = 'Statement_Details';

        public $Family_ID;
        public $Budget_ID;
        public $Statement_Details_Author;
        public $Statement_Details_Description;
        public $Statement_Details_Value;
        public $Statement_Details_Date;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //CADASTRA O DETALHAMENTO DO EXTRATO
        public function createStatementDetails() {
            $query = 'INSERT INTO '.$this->table.' 
                      VALUES(
                        :family_id,
                        :budget_id,
                        :author,
                        :description,
                        :value,
                        :date
                      )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_ID);
            $stmt->bindParam(':author', $this->Statement_Details_Author);
            $stmt->bindParam(':description', $this->Statement_Details_Description);
            $stmt->bindParam(':value', $this->Statement_Details_Value);
            $stmt->bindParam(':date', $this->Statement_Details_Date);

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