<?php

    class PendingIssues {
        private $conn;
        private $table = 'Pending_Issues';

        // PROPRIEDADES DA TABELA
        public $Pending_Issues_ID;
        public $User_ID;
        public $Family_ID;
        public $Person_Name;
        public $User_Email;
        public $Pending_Issues_Description;
        public $Pending_Issues_Month_Year;
        public $Pending_Issues_Done;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        public function create() {
            $query = 'INSERT INTO '.$this->table.' 
                    (
                        User_ID, 
                        Family_ID,
                        Pending_Issues_Description,
                        Pending_Issues_Month_Year,
                        Pending_Issues_Done
                    )
                    VALUES (
                        :user_id,
                        :family_id,
                        :description,
                        :month_year,
                        :done
                    )';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':family_id', $this->Family_ID);
            $stmt->bindParam(':description', $this->Pending_Issues_Description);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);
            $stmt->bindParam(':done', $this->Pending_Issues_Done);
            
            //EXECUTA A QUERY
            if($stmt->execute()) {
                return "success";
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return "fail";
        }

        public function read() {
            $query = 'SELECT 
                        Pending_Issues_ID,
                        Pending_Issues_Description,
                        Pending_Issues_Done
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_Month_Year = :month_year';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function readTotalPendingIssues() {
            $query = 'SELECT 
                        Count(*) AS Pending_Issues
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_Month_Year = :month_year';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function readTotalPendingIssuesNotDone() {
            $query = 'SELECT 
                        Count(*) AS Pending_Issues
                    FROM '.$this->table.' 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_Month_Year = :month_year 
                    AND Pending_Issues_Done = 0';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        public function readUserTotalPendingIssues() {
            $query = 'SELECT 
                        Count(*) AS Pending_Issues
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

        public function updateSatus() {
            $query = 'UPDATE '.$this->table.' 
                    SET Pending_Issues_Done = :done 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_ID = :pending_issue_id
                    AND Pending_Issues_Month_Year = :month_year';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':pending_issue_id', $this->Pending_Issues_ID);
            $stmt->bindParam(':done', $this->Pending_Issues_Done);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function update() {
            $query = 'UPDATE '.$this->table.' 
                    SET Pending_Issues_Description = :description,
                    Pending_Issues_Done = :done 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_ID = :pending_issue_id
                    AND Pending_Issues_Month_Year = :month_year';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':pending_issue_id', $this->Pending_Issues_ID);
            $stmt->bindParam(':description', $this->Pending_Issues_Description);
            $stmt->bindParam(':done', $this->Pending_Issues_Done);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function delete() {
            $query = 'DELETE FROM '.$this->table.' 
                    WHERE User_ID = :user_id 
                    AND Pending_Issues_ID = :pending_issue_id
                    AND Pending_Issues_Month_Year = :month_year';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);
    
            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':pending_issue_id', $this->Pending_Issues_ID);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);
    
             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
            
        }

        public function readAll() {
            $query = 'SELECT 
                        *
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

        public function reset() {
            $query = 'UPDATE '.$this->table.' 
                    SET Pending_Issues_Month_Year = :month_year,
                    Pending_Issues_Done = 0 
                    WHERE User_ID = :user_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS
            $stmt->bindParam(':user_id', $this->User_ID);
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

             //EXECUTA A QUERY
             if($stmt->execute()) {
                return true;
            }
            //EXIBE ERRO SE ALGO DER ERRADO
            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        //Consulta as pendências do usuário
        public function readUserPendingIssues() {
            $query = 'SELECT 
                        Pending_Issues_ID, 
                        Pending_Issues_Description 
                      FROM 
                        '.$this->table.' 
                      WHERE user_id = :user_id';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //Consulta o id de usuários com pendências não concluídas
        public function readUsersWithPendingIssuesNotDone() {
            $query = 'SELECT 
                        DISTINCT(pi.User_ID) as User_ID
                    FROM 
                        '.$this->table.' as pi
                    WHERE pi.Pending_Issues_Month_Year = :month_year 
                    AND pi.Pending_Issues_Done = 0';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //Consulta o nome e e-mail do usuário a partir do id
        public function readNameAndEmail() {
            $query = 'SELECT 
                        usr.USER_ID as "User_ID",
                        p.Person_Name as "Person_Name",
                        usr.User_Email as "User_Email"
                    FROM 
                        User as usr
                    INNER JOIN 
                        Person as p
                    ON p.Person_ID = usr.Person_ID
                    WHERE usr.User_ID = :user_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }

        //Consulta a lista de pendências não concluída do usuário
        public function readPendingIssuesList() {
            $query = 'SELECT 
                        pi.User_ID as "User_ID",
                        pi.Pending_Issues_Description as "Pending_Issues_Description"
                    FROM 
                        Pending_Issues as pi
                    WHERE pi.Pending_Issues_Month_Year = :month_year 
                    AND pi.Pending_Issues_Done = 0
                    AND pi.User_ID = :user_id';

            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //LIGA OS DADOS 
            $stmt->bindParam(':month_year', $this->Pending_Issues_Month_Year);
            $stmt->bindParam(':user_id', $this->User_ID);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }
?>