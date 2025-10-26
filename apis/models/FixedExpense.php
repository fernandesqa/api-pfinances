<?php

    class FixedExpense {
        private $conn;
        private $table = 'Fixed_Expense';

        public $Fixed_Expense_ID;
        public $Person_ID;
        public $Family_ID;
        public $Budget_ID;
        public $Expense_Category_ID;
        public $Fixed_Expense_Month_Year;
        public $Fixed_Expense_End_Month_Year;
        public $Fixed_Expense_Value;
        public $Fixed_Expense_Description;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // REGISTRA UMA NOVA DESPESA FIXA NA BASE
        public function createFixedExpense() {
            $query = 'INSERT INTO '.$this->table.' 
                      (
                        Person_ID,
                        Family_ID,
                        Budget_ID,
                        Expense_Category_ID,
                        Fixed_Expense_Month_Year,
                        Fixed_Expense_End_Month_Year,
                        Fixed_Expense_Value,
                        Fixed_Expense_Description
                      ) 
                      VALUES (
                                :person_id,
                                :family_id,
                                :budget_id,
                                :expense_category_id,
                                :fixed_expense_month_year,
                                :fixed_expense_end_month_year,
                                :fixed_expense_value,
                                :fixed_expense_description
                             )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_ID);
            $stmt->bindParam(':expense_category_id', $this->Expense_Category_ID);
            $stmt->bindParam(':fixed_expense_month_year', $this->Fixed_Expense_Month_Year);
            $stmt->bindParam(':fixed_expense_end_month_year', $this->Fixed_Expense_End_Month_Year);
            $stmt->bindParam(':fixed_expense_value', $this->Fixed_Expense_Value);
            $stmt->bindParam(':fixed_expense_description', $this->Fixed_Expense_Description);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        // CONSULTA O ID DA DESPESA FIXA
        public function getFixedExpenseId() {
            $query = 'SELECT 
                        Fixed_Expense_ID 
                      FROM '.$this->table.' 
                      WHERE 
                        Family_ID = :family_id 
                      AND
                        Fixed_Expense_Description = :fixed_expense_description 
                      AND
                        Fixed_Expense_Month_Year = :fixed_expense_month_year
                      AND 
                        Person_ID = :person_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':fixed_expense_month_year', $this->Fixed_Expense_Month_Year);
            $stmt->bindParam(':fixed_expense_description', $this->Fixed_Expense_Description);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }
?>