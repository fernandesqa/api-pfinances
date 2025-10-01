<?php

    class Budget {

        private $conn;
        private $table = 'Budget';

        public $Budget_Control_ID;
        public $Revenue_ID;
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
                        :revenue_id,
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
            $stmt->bindParam(':revenue_id', $this->Revenue_ID);
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
    }

?>