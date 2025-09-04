<?php
    require __DIR__.'/vendor/autoload.php';

    // Load the .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    class Security {

        public function get_secret_key() {
            return $_ENV['SECRET_KEY'];
        }

        public function get_issuer_claim() {
            return $_ENV['ISSUER_CLAIM'];
        }

        public function get_audience_claim() {
            return $_ENV['AUDIENCE_CLAIM'];
        }
    }

?>