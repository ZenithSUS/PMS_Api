<?php
include_once('token.php');

class Users extends Token {
    public function __construct() {
        parent::__construct();
    }

    protected function getUser(string $token) : string {
        $sql = "SELECT username FROM users WHERE token = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['username'];
    }
}
?>