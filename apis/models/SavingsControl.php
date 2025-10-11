<?php

    class SavingsControl {

        private $conn;
        private $table = 'Savings_Control';

        public $Savings_Control_ID;
        public $Savings_Control_Description;
        public $Savings_Control_Value;
        public $Person_ID;
        public $Family_ID;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //CADASTRA UM NOVO INVESTIMENTO OU ECONOMIA
        public function createSavings() {
            $query = 'INSERT INTO '.$this->table.' 
                     VALUES (
                        :savings_control_id,
                        :description,
                        :value,
                        :person_id,
                        :family_id
                     )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':savings_control_id', $this->Savings_Control_ID);
            $stmt->bindParam(':description', $this->Savings_Control_Description);
            $stmt->bindParam(':value', $this->Savings_Control_Value);
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function getSavingsId() {
            $query = 'SELECT 
                        Savings_Control_ID 
                      FROM '.$this->table.' 
                      WHERE 
                        Savings_Control_Description = :description 
                      AND 
                        Family_ID = :family_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':description', $this->Savings_Control_Description);
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //RETORNA TODAS AS ECONOMIAS CADASTRADAS POR UMA FAMÍLIA
        public function getSavings() {
            $query = 'SELECT 
                        Savings_Control_ID,
                        Savings_Control_Description,
                        Savings_Control_Value
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

        //OBTÉM O VALOR ATUAL DE UM REGISTRO DE ECONOMIA
        public function getSavingsValue() {
            $query = 'SELECT 
                        Savings_Control_Value 
                      FROM 
                        '.$this->table.' 
                      WHERE 
                        Savings_Control_ID = :id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':id', $this->Savings_Control_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //ATUALIZA O VALOR DE UM REGISTRO DE ECONOMIA
        public function updateSavingsValue() {
            $query = 'UPDATE '.$this->table.' 
                      SET 
                        Savings_Control_Value = :savings_control_value 
                      WHERE
                        Savings_Control_ID = :id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':savings_control_value', $this->Savings_Control_Value);
            $stmt->bindParam(':id', $this->Savings_Control_ID);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function getSavingsDescription() {
            $query = 'SELECT 
                        Savings_Control_Description 
                      FROM 
                        '.$this->table.' 
                      WHERE 
                        Savings_Control_ID = :id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':id', $this->Savings_Control_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }

?>