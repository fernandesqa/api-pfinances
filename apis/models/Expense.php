<?php

    class Expense {
        private $conn;
        private $table = 'Expense';

        public $Expense_ID;
        public $Person_ID;
        public $Family_ID;
        public $Budget_ID;
        public $Expense_Category_ID;
        public $Expense_Installments_Expense;
        public $Expense_Date;
        public $Expense_Billing_Month_Year;
        public $Expense_Value;
        public $Expense_Description;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // REGISTRA UMA NOVA DESPESA NA BASE
        public function createExpense() {
            $query = 'INSERT INTO '.$this->table.' 
                      VALUES(
                                :expense_id,
                                :person_id,
                                :family_id,
                                :budget_id,
                                :expense_category_id,
                                :expense_installments_expense,
                                :expense_date,
                                :expense_billing_month_year,
                                :expense_value,
                                :expense_description
                            )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':expense_id', $this->Expense_ID);
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_ID);
            $stmt->bindParam(':expense_category_id', $this->Expense_Category_ID);
            $stmt->bindParam(':expense_installments_expense', $this->Expense_Installments_Expense);
            $stmt->bindParam(':expense_date', $this->Expense_Date);
            $stmt->bindParam(':expense_billing_month_year', $this->Expense_Billing_Month_Year);
            $stmt->bindParam(':expense_value', $this->Expense_Value);
            $stmt->bindParam(':expense_description', $this->Expense_Description);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function totalExpenses() {
            $query = 'SELECT 
                        COUNT(*) AS "Total"
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