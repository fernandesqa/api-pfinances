<?php
    require __DIR__.'/vendor/autoload.php';

    // Load the .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    class Database {
        // PARÂMETROS DA CONEXÃO
        private $conn;

        private function get_driver() {
            return $_ENV['DB_DRIVER'];
        }

        private function get_host() {
            return $_ENV['HOST'];
        }

        private function get_db_name() {
            return $_ENV['DB_NAME'];
        }

        private function get_username() {
            return $_ENV['USERNAME'];
        }

        private function get_password() {
            return $_ENV['PASSWORD'];
        }

        //CONNEXÃO COM O BANCO
        public function connection() {
            $this->conn = null;

            try {
                $this->conn = new PDO($this->get_driver().':host='.$this->get_host().';dbname='.$this->get_db_name().';charset=utf8mb4',
                $this->get_username(), $this->get_password());
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $e) {
                http_response_code(500);
                echo 'Connection Error: '.$e->getMessage();
            }

            return $this->conn;
        }

    }