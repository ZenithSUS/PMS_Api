<?php
include_once('token.php');
class Customers extends Token {

    public function __construct() {
        parent::__construct();
    }

    protected function getAllCustomers() : string {
        $sql = "SELECT * FROM customers ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result) : $this->notFound();
    } 

}

?>