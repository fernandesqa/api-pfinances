<?php

    class ExpenseCategory {

        private $conn;
        private $table = 'Expense_Category';

        public $Expense_Category_ID;
        public $Expense_Category_Description;

        // CONSTRUTOR COM BANCO DE DADOS
        public function __construct($db) {
            $this->conn = $db;
        }

        // CONSULTA AS CATEGORIAS
        public function getCategories() {
            $query = 'SELECT
                        * 
                      FROM '.$this->table.'';
            
            //PREPARA A QUERY
            $stmt = $this->conn->prepare($query);

            //EXECUTA A QUERY
            $stmt->execute();

            return $stmt;
        }
    }

?>