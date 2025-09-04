<?php

    class Invites {
        private $conn;
        private $table = 'System_Invite';

        //PROPRIEDADES DA TABELA 
        public $System_Invite_Code;
        public $System_Invite_Email;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        //CONSULTA PELO CONVITE E E-MAIL INFORMADOS
        public function readSystem() {
            $data = json_decode(file_get_contents("php://input"));
            $query = 'SELECT 
                    System_Invite_Code,
                    System_Invite_Email
                FROM 
                    '.$this->table. '
                WHERE System_Invite_Code = :System_Invite_Code 
                AND System_Invite_Email = :System_Invite_Email';
                

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //BIND 
            $stmt->bindParam(':System_Invite_Code', $data->inviteCode);
            $stmt->bindParam(':System_Invite_Email', $data->emailAddress);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //ATUALIZA O STATUS PARA INFORMAR QUE O CONVITE FOI UTILIZADO
        public function updateSystem() {
            $data = json_decode(file_get_contents("php://input"));
            $query = 'UPDATE '.$this->table. '
                    SET
                        System_Invite_Used = 1
                    WHERE
                        System_Invite_Code = :System_Invite_Code
                    AND System_Invite_Email = :System_Invite_Email';

                //PREPARA A QUERY
                $stmt = $this->conn->prepare($query);

                //LIGA OS DADOS
                $stmt->bindParam(':System_Invite_Code', $data->inviteCode);
                $stmt->bindParam(':System_Invite_Email', $data->emailAddress);
                
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