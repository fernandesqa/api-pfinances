<?php

    class Revenue {
        private $conn;
        private $table = 'Revenue';

        // PROPRIEDADES DA TABELA
        public $Revenue_ID;
        public $Person_ID;
        public $Family_ID;
        public $Revenue_Month_Year;
        public $Revenue_Value;
        public $Revenue_Current_Value;
        public $Revenue_Description;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        public function create() {
            $query = 'INSERT INTO '.$this->table.' 
                    VALUES
                    (
                        :revenue_id,
                        :person_id,
                        :family_id,
                        :revenue_month_year,
                        :revenue_value,
                        :revenue_current_value,
                        :revenue_description
                    )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':revenue_id', $this->Revenue_ID);
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':revenue_month_year', $this->Revenue_Month_Year);
            $stmt->bindParam(':revenue_value', $this->Revenue_Value);
            $stmt->bindParam(':revenue_current_value', $this->Revenue_Current_Value);
            $stmt->bindParam(':revenue_description', $this->Revenue_Description);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }

        public function read() {
            $query = 'SELECT 
                        Revenue_Value,
                        Revenue_Current_Value,
                        Revenue_Description 
                    FROM '.$this->table.' 
                    WHERE Family_ID = :family_id 
                    AND Revenue_Month_Year LIKE "%'.$this->Revenue_Month_Year.'"';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function readCurrentMonthTotalRevenue() {
            $query = 'SELECT 
                        SUM(Revenue_Value) AS Revenue_Value
                    FROM '.$this->table.' 
                    WHERE Family_ID = :family_id 
                    AND Revenue_Month_Year LIKE "%'.$this->Revenue_Month_Year.'"';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function updateRevenueCurrentValue() {
            $query = 'UPDATE '.$this->table.' 
                      SET Revenue_Current_Value = :current_value
                      WHERE Family_ID = :family_id
                      AND Revenue_Month_Year LIKE "%'.$this->Revenue_Month_Year.'"
                      AND Revenue_ID = :revenue_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':current_value', $this->Revenue_Current_Value);
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

        public function readRevenuesByPeriod() {
            $query = 'SELECT
                        Revenue_ID,
                        Revenue_Description,
                        Revenue_Current_Value
                     FROM '.$this->table.'
                     WHERE Family_ID = :family_id 
                     AND Revenue_Month_Year LIKE "%'.$this->Revenue_Month_Year.'"';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function getRevenueCurrentValue() {
            $query = 'SELECT 
                        Revenue_Current_Value
                      FROM '.$this->table.' 
                      WHERE Revenue_ID = :revenue_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':revenue_id', $this->Revenue_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function getRevenueDescription() {
            $query = 'SELECT 
                        Revenue_Description 
                      FROM 
                        '.$this->table.' 
                      WHERE 
                        Revenue_ID = :id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':id', $this->Revenue_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }

?>