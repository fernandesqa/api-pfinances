<?php

    class Budget {

        private $conn;
        private $table = 'Budget';

        public $Budget_Control_ID;
        public $Budget_Origin_ID;
        public $Person_ID;
        public $Family_ID;
        public $Budget_Month_Year;
        public $Budget_Value;
        public $Budget_Current_Value;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //CADASTRA OS VALORES DO ORÇAMENTO NO MÊS E ANO DESEJADO
        public function setBudgetValue() {
            $query = 'INSERT INTO '.$this->table.' 
                      VALUES (
                        :budget_control_id,
                        :origin_id,
                        :person_id,
                        :family_id,
                        :month_year,
                        :value,
                        :current_value
                      )';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':budget_control_id', $this->Budget_Control_ID);
            $stmt->bindParam(':origin_id', $this->Budget_Origin_ID);
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':month_year', $this->Budget_Month_Year);
            $stmt->bindParam(':value', $this->Budget_Value);
            $stmt->bindParam(':current_value', $this->Budget_Current_Value);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function getBudgetCurrentValue() {
            $query = 'SELECT 
                        Budget_Current_Value 
                      FROM 
                        '.$this->table.' 
                      WHERE 
                        Budget_Control_ID = :budget_id 
                      AND 
                        Family_ID = :family_id 
                      AND
                        Budget_Month_Year LIKE "%'.$this->Budget_Month_Year.'"';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_Control_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function updateBudgetCurrentValue() {
            $query = 'UPDATE '.$this->table.'
                      SET 
                        Budget_Current_Value = :current_value 
                      WHERE 
                        Budget_Control_ID = :budget_id
                      AND
                        Family_ID = :family_id
                      AND 
                        Budget_Month_Year = :month_year';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':current_value', $this->Budget_Current_Value);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_Control_ID);
            $stmt->bindParam(':month_year', $this->Budget_Month_Year);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

      public function getBudgetsByPeriods() {
        $query = 'SELECT 
                    c.Budget_Control_Description AS "Description",
                    b.Budget_Month_Year AS "Month_Year",
                    b.Budget_Value AS "Value",
                    b.Budget_Current_Value AS "Current_Value"
                  FROM 
                    '.$this->table.' AS b 
                  INNER JOIN Budget_Control AS c 
                  ON 
                    b.Budget_Control_ID = c.Budget_Control_ID 
                  WHERE b.Family_ID = :family_id 
                  AND b.Budget_Month_Year LIKE "%'.$this->Budget_Month_Year.'"';
        //PREPARA A QUERY
        $stmt = $this->conn->prepare($query);

        //LIGA OS DADOS
        $stmt->bindParam(':family_id', $this->Family_ID);

        //EXECUTA A QUERY
        $stmt->execute();

        return $stmt;
      }

      public function getBudgetsAlreadySetOnPeriod() {
        $query = 'SELECT 
                    DISTINCT(bc.Budget_Control_Description) AS "Description"
                  FROM 
                    '.$this->table.' AS b 
                  INNER JOIN 
                    Budget_Control AS bc
                  ON 
                    bc.Budget_Control_ID = b.Budget_Control_ID 
                  WHERE 
                    b.Family_ID = :family_id
                  AND 
                    b.Budget_Month_Year LIKE "%'.$this->Budget_Month_Year.'"';

        //PREPARA A QUERY
        $stmt = $this->conn->prepare($query);

        //LIGA OS DADOS
        $stmt->bindParam(':family_id', $this->Family_ID);

        //EXECUTA A QUERY
        $stmt->execute();

        return $stmt;
      }

      public function getPreviousBudgets() {
        $query = 'SELECT 
                    bc.Budget_Control_Description AS "Description",
                    SUM(b.Budget_Value) AS "Budget_Value"
                  FROM 
                    '.$this->table.' AS b 
                  INNER JOIN 
                    Budget_Control AS bc
                  ON 
                    bc.Budget_Control_ID = b.Budget_Control_ID 
                  WHERE 
                    b.Family_ID = :family_id
                  AND 
                    b.Budget_Month_Year LIKE "%'.$this->Budget_Month_Year.'"
                  GROUP BY 
                    bc.Budget_Control_Description';

        //PREPARA A QUERY
        $stmt = $this->conn->prepare($query);

        //LIGA OS DADOS
        $stmt->bindParam(':family_id', $this->Family_ID);

        //EXECUTA A QUERY
        $stmt->execute();

        return $stmt;
      }

      public function getBudgets() {
        $query = 'SELECT 
                    b.Budget_Control_ID AS "Budget_Control_ID",
                    bc.Budget_Control_Description AS "Budget_Control_Description",
                    SUM(b.Budget_Current_value) AS "Budget_Current_Value"
                  FROM 
                    '.$this->table.' AS b
                  INNER JOIN 
                    Budget_Control AS bc 
                  ON 
                    b.Budget_Control_ID = bc.Budget_Control_ID
                  WHERE 
                    b.Family_ID = :family_id 
                  AND
                    b.Budget_Month_Year LIKE "%'.$this->Budget_Month_Year.'"
                  GROUP BY 
                    b.Budget_Control_ID';

        //PREPARA A QUERY
        $stmt = $this->conn->prepare($query);

        //LIGA OS DADOS
        $stmt->bindParam(':family_id', $this->Family_ID);

        //EXECUTA A QUERY
        $stmt->execute();

        return $stmt;
      }

      public function getBudgetUsageData() {
        $query = 'SELECT 
                    bc.Budget_Control_Description AS "Budget_Description",
                    bc.Budget_Control_Icon_Name AS "Icon",
                    b.Budget_Value AS "Total_Set",
                      b.Budget_Current_Value AS "Total_Available",
                      b.Budget_Value - b.Budget_Current_Value AS "Total_Used"
                  FROM 
                    '.$this->table.' AS b
                  INNER JOIN
                    `Budget_Control` AS bc
                  ON 
                    b.Budget_Control_ID = bc.Budget_Control_ID
                  WHERE 
                    b.Family_ID = :family_id
                  AND
                    b.Budget_Month_Year LIKE "%'.$this->Budget_Month_Year.'"';

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