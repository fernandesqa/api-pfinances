<?php

    class Statement {
        private $conn;
        private $table = 'Statement';

        // PROPRIEDADES DA TABELA
        public $Statement_Author;
        public $Statement_Description;
        public $Statement_Value;
        public $Statement_Date;
        public $Statement_Origin;
        public $Statement_Destination;
        public $Family_ID;
        public $Budget_ID;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        public function revenueCreation() {
            $query = 'INSERT INTO '.$this->table.' 
                      (
                        Statement_Author,
                        Statement_Description,
                        Statement_Value,
                        Statement_Date,
                        Family_ID
                      ) 
                      VALUES (
                        :statement_author,
                        :statement_description,
                        :statement_value,
                        :statement_date,
                        :family_id
                      )';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':statement_author', $this->Statement_Author);
            $stmt->bindParam(':statement_description', $this->Statement_Description);
            $stmt->bindParam(':statement_value', $this->Statement_Value);
            $stmt->bindParam(':statement_date', $this->Statement_Date);
            $stmt->bindParam(':family_id', $this->Family_ID);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function savingsCreation() {
            $query = 'INSERT INTO '.$this->table.'
                      VALUES (
                        :statement_author,
                        :statement_description,
                        :statement_value,
                        :statement_date,
                        :statement_origin,
                        :statement_destination,
                        :family_id,
                        :budget_id
                      )';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':statement_author', $this->Statement_Author);
            $stmt->bindParam(':statement_description', $this->Statement_Description);
            $stmt->bindParam(':statement_value', $this->Statement_Value);
            $stmt->bindParam(':statement_date', $this->Statement_Date);
            $stmt->bindParam(':statement_origin', $this->Statement_Origin);
            $stmt->bindParam(':statement_destination', $this->Statement_Destination);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_ID);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function getStatementByPeriod() {
            $query = 'SELECT 
                        Statement_Author,
                        Statement_Description,
                        Statement_Value,
                        Statement_Date,
                        Statement_Origin,
                        Statement_Destination,
                        Budget_ID 
                    FROM '.$this->table.' 
                    WHERE 
                        Family_ID = :family_id 
                    AND 
                        Statement_Date LIKE "%'.$this->Statement_Date.'"';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }

?>