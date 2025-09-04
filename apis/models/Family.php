<?php

    class Family {
        private $conn;
        private $table = 'Family';

        // PROPRIEDADES DA TABELA
        public $Family_ID;
        public $Family_Name;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // CONSULTA O ID DA FAMILIA
        public function readFamilyId() {
            $query = 'SELECT 
                    Family_ID
                FROM 
                    '.$this->table. '
                WHERE Family_Name = :family_name';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //BIND 
            $stmt->bindParam(':family_name', $this->Family_Name);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        // CONSULTA O NOME DA FAMILIA
        public function readFamilyName() {
            $query = 'SELECT 
                    Family_Name
                FROM 
                    '.$this->table. '
                WHERE Family_ID = :family_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //BIND 
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        // CADASTRA UMA NOVA FAMILIA
        public function create() {
            $query = 'INSERT INTO '.$this->table. '
                    (Family_Name) VALUES (:family_name)';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_name', $this->Family_Name);

            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }
    }
?>