<?php
include_once('token.php');
class Customers extends Token {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all customers
     * @return string
    */
    protected function getAllCustomers() : string {
        $sql = "SELECT id, name, email FROM customers ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result) : $this->notFound();
    }
    
    /**
     * Get customer by id
     * @param string $id
     * @return string
    */
    protected function getCustomer(?string $id = null) : string {
        $sql = "SELECT * FROM customers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return $this->queryFailed();
        }

        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $this->fetched($result, "get") : $this->notFound();
    }

    /**
     * Add customer
     * @param string $name
     * @param string $email
     * @return string
     */
    protected function addCustomerQuery(?string $name = null, ?string $email = null) : string {
        $sql = "INSERT INTO customers (id, name, email) VALUES (UUID(), ?, ?)";
        $stmt = $this->conn->prepare($sql);

        $this->checkFields($name, $email);

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }

        $stmt->bind_param('ss', $name, $email);
        return $stmt->execute() ? $this->success('customer') : $this->queryFailed();
    }

    /**
     * Edit customer
     * @param string $id
     * @param string $name
     * @param string $email
     * @return string
     */
    protected function editCustomerQuery(?string $id = null, ?string $name = null, ?string $email = null) : string {
        $this->checkFields($name, $email);

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }
        $sql = "UPDATE customers SET name = ?, email = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) { 
            return $this->queryFailed();
        }

        $stmt->bind_param('sss', $name, $email, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $this->edited() : $this->queryFailed();
    }

    /**
     * Delete customer
     * @param string $id
     * @return string
     */
    protected function deleteCustomerQuery(?string $id = null) : string {
        $sql = "DELETE FROM customers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return $this->queryFailed();
        }

        $stmt->bind_param('s', $id);
        return $stmt->execute() ? $this->success() : $this->notFound();
    }

    /**
     * Check fields
     * @param string $name
     * @param string $email
     * @return void
     */
    private function checkFields(?string $name = null, ?string $email = null) : void {
        if(empty($name) || is_null($name) || $name === "") {
            $this->errors['name'] = 'Name is required';
        } else if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $this->errors['name'] = 'Please enter a valid name';
        }

        if(empty($email) || is_null($email) || $email === "") {
            $this->errors['email'] = 'Email is required';
        } else if ($this->checkEmail($email) == false) {
            $this->errors['email'] = 'Please enter a valid email';
        }
    }

    /**
     * Check email
     * @param string $email
     * @return bool
    */
    private function checkEmail(string $email) : bool {
        $valid_names = [
            "gmail.com",
            "yahoo.com",
            "hotmail.com",
            "outlook.com"
        ];
        $emailName = explode("@", $email);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        if(!in_array(end($emailName), $valid_names)) return false;
        if(!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/", $email)) return false;
        return true;
    }
}

?>