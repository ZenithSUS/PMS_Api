<?php
require_once('token.php');
class Orders extends Token {
    public function __construct() {
        parent::__construct();
    }

    protected function getAllOrders() : string {
        $sql = "SELECT orders.id, customers.name AS customerName, products.name AS productName, orders.quantity
        FROM orders 
        INNER JOIN customers ON orders.customer_id = customers.id
        INNER JOIN products ON orders.product_id = products.id
        ORDER BY customers.name DESC";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result) : $this->notFound();
    }    
}

?>