<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "product_management_system";

    public function __construct() {
        $this->connect();
    }

    protected function connect() {
        $conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        return $this->checkConnection($conn);
    }

    protected function checkConnection($conn) {
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    protected function disconnect($conn) {
        $conn->close();
    }
}
?>