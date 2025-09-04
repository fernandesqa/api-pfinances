<?php

    class FamilyInvites {
        private $conn;
        private $table = 'Family_Invite';

        //PROPRIEDADES DA TABELA 
        public $Family_ID;
        public $Family_Invite_Code;
        public $Family_Invite_Email;
        public $Family_Invite_Used;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // CADASTRA UM NOVO CONVITE NA BASE
        public function create() {
            $query = 'INSERT INTO '.$this->table.' 
                    VALUES (
                        :family_id, 
                        :family_invite_code, 
                        :family_invite_email, 
                        0
                    )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':family_invite_code', $this->Family_Invite_Code);
            $stmt->bindParam(':family_invite_email', $this->Family_Invite_Email);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }

        public function read() {
            $query = 'SELECT *
                    FROM '.$this->table.' 
                    WHERE Family_Invite_Email = :family_invite_email';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_invite_email', $this->Family_Invite_Email);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function readAll() {
            $query = 'SELECT 
                        Family_Invite_Code,
                        Family_Invite_Email,
                        Family_Invite_Used
                    FROM '.$this->table.'
                    WHERE Family_ID = :family_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_id', $this->Family_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function getInviteCode() {
            $query = 'SELECT 
                        Family_Invite_Code
                    FROM '.$this->table.'
                    WHERE Family_Invite_Code = :invite_code 
                    AND Family_Invite_Email = :email';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':invite_code', $this->Family_Invite_Code);
            $stmt->bindParam(':email', $this->Family_Invite_Email);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function update() {
            $query = 'UPDATE '.$this->table.' 
                SET Family_Invite_Used = 1 
                WHERE Family_Invite_Code = :invite_code 
                AND Family_Invite_Email = :email';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);
            
            //LIGA OS DADOS
            $stmt->bindParam(':invite_code', $this->Family_Invite_Code);
            $stmt->bindParam(':email', $this->Family_Invite_Email);

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