<?php

    class RevenueAndSavingsControl {
        private $conn;
        private $table = 'Revenue_And_Savings_Control';

        public $Person_ID;
        public $Family_ID;
        public $Revenue_ID;
        public $Savings_ID;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //CADASTRA O ID DA RECEITA
        public function createRevenueId() {
            $query = 'INSERT INTO '.$this->table.'
                      (
                        Person_ID,
                        Family_ID,
                        Revenue_ID
                      )
                      VALUES (
                        :person_id,
                        :family_id,
                        :revenue_id
                      )';

             //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':revenue_id', $this->Revenue_ID);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        //CADASTRA O ID DO REGISTRO DE ECONOMIA
        public function createSavingId() {
            $query = 'INSERT INTO '.$this->table.'
                      (
                        Person_ID,
                        Family_ID,
                        Savings_ID
                      )
                      VALUES (
                        :person_id,
                        :family_id,
                        :savings_id
                      )';

             //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':savings_id', $this->Savings_ID);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function getLastRevenueId() {
            $query = 'SELECT 
                        Revenue_ID
                      FROM 
                        '.$this->table.' 
                      ORDER BY 
                        Revenue_ID 
                      DESC LIMIT 1';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
            
        }

        // OBTÉM O ULTIMO ID DO REGISTRO DE ECONOMIA CADASTRADO
        public function getLastSavingsId() {
            $query = 'SELECT 
                        Savings_ID
                      FROM 
                        '.$this->table.' 
                      ORDER BY 
                        Savings_ID 
                      DESC LIMIT 1';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
            
        }

        // OBTÉM O TOTAL DE LINHAS DA TABELA
        public function countRows() {
            $query = 'SELECT 
                        COUNT(*) as Total
                      FROM 
                        '.$this->table.'';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }
?>