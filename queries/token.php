<?php
include_once('../api_status.php');

class Token extends API {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Generate token
     * @return string
    */
    protected function generateToken() : string {
        $token = bin2hex(random_bytes(16));
        return $token;
    }

    /**
     * Insert token
     * @param string $token
     * @param string $id
     * @return void
    */
    protected function insertToken(string $token, string $id) : void {
        $sql = "UPDATE users SET token = ? WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $token, $id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Check token
     * @param string $token
     * @return bool
    */
    public function checkToken(?string $token = null) : bool {
        $sql = "SELECT token FROM users WHERE token = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}

?>