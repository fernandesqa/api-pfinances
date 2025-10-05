<?php

    class Savings {
        private $conn;
        private $table = 'Savings';

        public $Savings_Control_ID;
        public $Person_ID;
        public $Family_ID;
        public $Budget_ID;
        public $Savings_Month_Year;
        public $Savings_Value;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // CADASTRA OS DADOS NA TABELA
        public function createSavings() {
            $query = 'INSERT INTO '.$this->table.' 
                      VALUES(
                        :savings_control_id,
                        :person_id,
                        :family_id,
                        :budget_id,
                        :savings_month_year,
                        :savings_value
                      )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':savings_control_id', $this->Savings_Control_ID);
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':budget_id', $this->Budget_ID);
            $stmt->bindParam(':savings_month_year', $this->Savings_Month_Year);
            $stmt->bindParam(':savings_value', $this->Savings_Value);

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