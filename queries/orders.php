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
    
    protected function addOrderQuery(?string $customerId = null, ?string $productId = null, ?int $quantity = 0) : string {
        $this->checkFields($customerId, $productId, $quantity);

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }

        $sql = "INSERT INTO orders (id, customer_id, product_id, quantity) VALUES (UUID(), ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return $this->queryFailed();
        }

        $stmt->bind_param('ssi', $customerId, $productId, $quantity);
        return $stmt->execute() ? $this->success('order') : $this->queryFailed();
    }

    private function checkFields(?string $customerId = null, ?string $productId = null, ?int $quantity = 0) : void {
        if(empty($customerId) || is_null($customerId) || $customerId === "") {
            $this->errors['customer'] = 'Please select a customer';
        }

        if(empty($productId) || is_null($productId) || $productId === "") {
            $this->errors['product'] = 'Please select a product';
        }

        if(empty($quantity) || $quantity === 0) {
            $this->errors['quantity'] = 'Please add a value';
        } else if ($quantity < 0) {
            $this->errors['quantity'] = 'Please enter a valid value';
        } else if ($productId !== "" && !$this->checkAvailableProduct($quantity, $productId)) {
            $this->errors['quantity'] = 'Please enter an available quantity';
        }
    }

    private function checkAvailableProduct(?int $quantity = 0, ?string $productId = null) : bool {
        $sql = "SELECT quantity FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        $availableQuantity = $result->fetch_assoc()['quantity'] ?? 0; 
        $getTotalPurchases = $this->getTotalPurchases($productId);
        
        if($getTotalPurchases === false) {
            return false;
        }

        return $availableQuantity >= ($quantity + $getTotalPurchases);
    }

    private function getTotalPurchases(?string $productId = null) : int | bool {
        $sql = "SELECT SUM(quantity) AS total_purchased FROM orders WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 0) {
            return 0;
        }

        return intval($result->fetch_assoc()['total_purchased']);       
    }
}

?>