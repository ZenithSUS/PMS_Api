<?php
include_once('token.php');
class Products extends Token {

    public function __construct() {
        parent::__construct();
    }

    /** 
     * Get all products
     * @return string
    */
    protected function getAllProducts() : string {
        $sql = "SELECT id, name, price, quantity FROM products ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result) : $this->notFound();
    }

    /** 
     * Get product by id
     * @param string $id
     * @return string
    */
    protected function getProduct(?string $id = null) : string {
        $sql = "SELECT id, name, price, quantity FROM products WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->bind_param('i', $id)) {
            return $this->queryFailed();
        }

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result, "get") : $this->notFound();
    }

    /** 
     * Add product
     * @param string $name
     * @param string $price
     * @param string $quantity
     * @return string
    */
    protected function addProductQuery(?string $name = null, ?string $quantity = null, ?string $price = null) : string {
        $this->checkFields($name, $price, $quantity);

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }
        $sql = "INSERT INTO products (id, name, price, quantity) VALUES (UUID(), ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->bind_param('sii', $name, $price, $quantity)) {
            return $this->queryFailed();
        }

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        return $this->success('product');
    }

    /**
     * Edit Product
     * @param string $id
     * @param string $name
     * @param string $price
     * @param string $quantity
     * @return string
     */
    protected function editProductQuery(?string $id = null, ?string $name = null, ?string $quantity = null, ?string $price = null) : string {
        $this->checkFields($name, $price, $quantity);

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }

        $sql = "UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        $quantity = intval($quantity);
        if(!$stmt->bind_param('ssis', $name, $price, $quantity, $id)) {
            return $this->queryFailed();
        }

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        return $this->success('product');
    }

    /**
     * Delete Product
     * @param string $id
     * @return string
    */
    protected function deleteProductQuery(?string $id = null) : string {
        $sql = "DELETE FROM products WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return $this->queryFailed();
        }

        $stmt->bind_param('s', $id);
        return $stmt->execute() ? $this->success('product') : $this->notFound();
    }

    /**
     * Check fields
     * @param string $name
     * @param string $price
     * @param string $quantity
     * @return void
     */
    private function checkFields(?string $name = null, ?string $price = null, ?string $quantity = null) : void {
        if(empty($name) || is_null($name) || $name === "") {
            $this->errors['name'] = 'Name is required';
        }

        if(empty($price) || is_null($price) || $price === "") {
            $this->errors['price'] = 'Price is required';
        }

        if(empty($quantity) || is_null($quantity) || $quantity === "") {
            $this->errors['quantity'] = 'Quantity is required';
        }
    }
}
?>