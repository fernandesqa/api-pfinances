<?php

    class PendingIssuesHistory {
        private $conn;
        private $table = 'Pending_Issues_History';

        // PROPRIEDADES DA TABELA
        public $Pending_Issues_ID;
        public $User_ID;
        public $Family_ID;
        public $Pending_Issues_Description;
        public $Pending_Issues_Month_Year;
        public $Pending_Issues_Done;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        public function create() {
            $query = 'INSERT INTO '.$this->table.'
                    VALUES (
                        :pending_issue_id,
                        :user_id,
                        :family_id,
                        :description,
                        :month_year,
                        :done
                    )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':pending_issue_id', $this->Pending_Issues_ID);
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':description', $this->Pending_Issues_Description);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);
            $stmt->bindParam(':done', $this->Pending_Issues_Done);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }

        public function readUserTotalPendingIssues() {
            $query = 'SELECT 
                        Count(*) AS Pending_Issues
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

        public function readYears() {
            $query = 'SELECT 
                        DISTINCT(RIGHT(Pending_Issues_Month_Year, 4)) AS YEAR 
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

        public function readMonths() {
            $query = 'SELECT 
                        DISTINCT
                        IF(
                            LENGTH(Pending_Issues_Month_Year) = 5, 
                            SUBSTRING(Pending_Issues_Month_Year, 1, 1), 
                            SUBSTRING(Pending_Issues_Month_Year, 1, 2)
                        ) AS MONTH
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_Month_Year LIKE :year';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            $year = '%' . $this->Pending_Issues_Month_Year;

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':year', $year);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function read() {
            $query = 'SELECT 
                        Pending_Issues_ID,
                        Pending_Issues_Description,
                        Pending_Issues_Done
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_Month_Year = :month_year';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }

?>