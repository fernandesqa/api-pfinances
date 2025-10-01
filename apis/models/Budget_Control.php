<?php

    class BudgetControl {

        private $conn;
        private $table = 'Budget_Control';
        
        public $Budget_Control_ID;
        public $Person_ID;
        public $Family_ID;
        public $Budget_Control_Description;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //CADASTRA UM NOVO ORÇAMENTO
        public function createBudget() {
            $query = 'INSERT INTO '.$this->table.'
                    (
                     Person_ID,
                     Family_ID,
                     Budget_Control_Description
                    )
                     VALUES (
                     :person_id,
                     :family_id.
                     :description
                     )';
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':description', $this->Budget_Control_Description);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        //OBTÉM O ID DO ORÇAMENTO INFORMADO
        public function getBudgetId() {
            $query = 'SELECT 
                        Budget_Control_ID 
                      FROM '.$this->table.' 
                      WHERE
                        Family_ID = :family_id
                      AND
                        Budget_Control_Description = :description';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':description', $this->Budget_Control_Description);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //CONSULTA OS ORÇAMENTOS CADASTRADOS POR UMA FAMÍLIA
        public function getBudgets() {
            $query = 'SELECT 
                        Budget_Control_Description 
                      FROM 
                        '.$this->table.' 
                      WHERE 
                        Family_ID = :family_id';
            
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