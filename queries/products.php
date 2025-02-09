<?php
include_once('token.php');
class Products extends Token {

    public function __construct() {
        parent::__construct();
    }

    protected function getAllProducts() : string {
        $sql = "SELECT id, name, price, quantity FROM products ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result) : $this->notFound();
    }
}
?>