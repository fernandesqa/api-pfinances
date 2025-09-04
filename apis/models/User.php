<?php

    class User {
        private $conn;
        private $table = 'User';

        // PROPRIEDADES DA TABELA
        public $User_ID;
        public $Person_ID;
        public $Person_Name;
        public $Family_ID;
        public $Status_ID;
        public $Role_ID;
        public $User_Email;
        public $User_Password;
        public $User_First_Access;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // VERIFICA SE O E-MAIL INFORMADO JÁ EXISTE NA BASE
        public function readEmailAddress() {
            $query = 'SELECT 
                    User_Email
                FROM 
                    '.$this->table. '
                WHERE User_Email = :User_Email';
                

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //BIND 
            $stmt->bindParam(':User_Email', $this->User_Email);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;

        }

        // CADASTRA UM NOVO USUÁRIO
        public function create() {
            $query = 'INSERT INTO '.$this->table. '
                    (
                        Person_ID,
                        Family_ID,
                        Status_ID,
                        Role_ID,
                        User_Email,
                        User_Password,
                        User_First_Access
                    )
                    VALUES (
                        :person_id,
                        :family_id, 
                        :status_id,
                        :role_id,
                        :user_email,
                        :user_password,
                        :user_first_access 
                    )';

                //PREPARA A QUERY
                $stmt = $this->conn->prepare($query);

                //LIGA OS DADOS
                $stmt->bindParam(':person_id', $this->Person_ID);
                $stmt->bindParam(':family_id', $this->Family_ID);
                $stmt->bindParam(':status_id', $this->Status_ID);
                $stmt->bindParam(':role_id', $this->Role_ID);
                $stmt->bindParam(':user_email', $this->User_Email);
                $stmt->bindParam(':user_password', $this->User_Password);
                $stmt->bindParam(':user_first_access', $this->User_First_Access);
                
                //EXECUTA A QUERY
                if($stmt->execute()) {
                    return "success";
                }
                //EXIBE ERRO SE ALGO DER ERRADO
                printf("Error: %s.\n", $stmt->error);
                return "fail";
        }

        // CADASTRA UM NOVO USUÁRIO DEPENDENTE
        public function createDependent() {
            $query = 'INSERT INTO '.$this->table. '
                    (
                        Family_ID,
                        Status_ID,
                        Role_ID,
                        User_Email,
                        User_First_Access
                    )
                    VALUES (
                        :family_id, 
                        :status_id,
                        :role_id,
                        :user_email,
                        :user_first_access 
                    )';

                //PREPARA A QUERY
                $stmt = $this->conn->prepare($query);

                //LIGA OS DADOS
                $stmt->bindParam(':family_id', $this->Family_ID);
                $stmt->bindParam(':status_id', $this->Status_ID);
                $stmt->bindParam(':role_id', $this->Role_ID);
                $stmt->bindParam(':user_email', $this->User_Email);
                $stmt->bindParam(':user_first_access', $this->User_First_Access);
                
                //EXECUTA A QUERY
                if($stmt->execute()) {
                    return "success";
                }
                //EXIBE ERRO SE ALGO DER ERRADO
                printf("Error: %s.\n", $stmt->error);
                return "fail";
        }

        //SELECIONA TODOS OS DADOS DO USUÁRIO PELO E-MAIL INFORMADO PARA VALIDAR A AUTENTICAÇÃO
        public function getAuthData() {
            $query = 'SELECT 
                    User.User_ID, 
                    User.Family_ID, 
                    User.Status_ID,
                    Role_ID,
                    Person.Person_Name, 
                    User.User_Password,
                    User.User_First_Access
                FROM '.$this->table.' 
                INNER JOIN Person 
                ON User.Person_ID = Person.Person_ID 
                WHERE User.User_Email = ?';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //BIND EMAIL
            $stmt->bindParam(1, $this->User_Email);
 
            //EXECUTA A QUERY
            $stmt->execute();
 
            return $stmt;

        }

        public function saveDataFirstAccess() {
            $query = 'INSERT INTO '.$this->table.' 
                    (
                        Family_ID, 
                        Status_ID, 
                        Role_ID, 
                        User_Email, 
                        User_First_Access
                    ) 
                    VALUES (
                        :family_id, 
                        1, 
                        2, 
                        :user_email, 
                        1
                    )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':user_email', $this->User_Email);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }

        public function getUsers() {
            $query = 'SELECT 
                    Person.Person_Name, 
                    User.User_Email, 
                    User.Role_ID, 
                    User.User_First_Access 
                FROM '.$this->table.' AS User 
                LEFT JOIN Person AS Person ON User.Person_ID = Person.Person_ID 
                WHERE User.Family_ID = :family_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //BIND FAMILYID
            $stmt->bindParam(':family_id', $this->Family_ID);
 
            //EXECUTA A QUERY
            $stmt->execute();
 
            return $stmt;
        }

        public function saveDataDependentFirstAccess() {
            $query = 'UPDATE '.$this->table.'
                    SET Person_ID = :person_id,
                        User_Password = :user_password,
                        User_First_Access = 0
                    WHERE User_Email = :user_email';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':person_id', $this->Person_ID);
            $stmt->bindParam(':user_password', $this->User_Password);
            $stmt->bindParam(':user_email', $this->User_Email);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function checkFirstAccess() {
            $query = 'SELECT User_First_Access 
                    FROM '.$this->table.' 
                    WHERE User_Email = :user_email';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_email', $this->User_Email);  

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function getUserById() {
            $query = 'SELECT User_ID 
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function getFamilyId() {
            $query = 'SELECT Family_ID 
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function getPersonIdAndFamilyId() {
            $query = 'SELECT 
                        Person_ID,
                        Family_ID 
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }
?>