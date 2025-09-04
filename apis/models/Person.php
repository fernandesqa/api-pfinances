<?php

    class Person {
        private $conn;
        private $table = 'Person';

        // PROPRIEDADES DA TABELA
        public $Person_ID;
        public $Person_Name;
        public $Person_Creation_Date_Time;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // CONSULTA O ID DA PESSOA
        public function readPersonId() {
            $query = 'SELECT 
                    Person_ID
                FROM 
                    '.$this->table. '
                WHERE Person_Name = :person_name 
                AND Person_Creation_Date_Time = :date_time';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':person_name', $this->Person_Name);
            $stmt->bindParam(':date_time', $this->Person_Creation_Date_Time);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        // CADASTRA UMA NOVA PESSOA
        public function create() {
            $query = 'INSERT INTO '.$this->table. '
                    (Person_Name, Person_Creation_Date_Time) VALUES (:person_name, :date_time)';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_name', $this->Person_Name);
            $stmt->bindParam(':date_time', $this->Person_Creation_Date_Time);

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