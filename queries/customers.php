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
    protected function getAllCustomers(?string $page = null) : string {
        $page = intval($page);
        if(!empty($page)) {
            $offset = ($page - 1) * 5;
            $totalPages = ceil($this->countCustomers() / 5);
            $sql = "SELECT id, name, email FROM customers ORDER BY id DESC LIMIT 5 OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $offset); 
        } else {
            $sql = "SELECT id, name, email FROM customers ORDER BY id DESC";
            $stmt = $this->conn->prepare($sql);
        }
        
        if(!$stmt) return $this->queryFailed();

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        
        if(!empty($page) && $result->num_rows > 0) {
            return $this->fetched($result, null, $totalPages);
        } else if($result->num_rows > 0) {
            return $this->fetched($result);
        }

        return $this->notFound();
    }

    /**
     * Count customers
     * @return int
    */
    protected function countCustomers() : int {
        $sql = "SELECT COUNT(id) AS total FROM customers";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
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
        } else if ($this->checkNameExists($name) == true) {
            $this->errors['name'] = 'Name already exists';
        }

        if(empty($email) || is_null($email) || $email === "") {
            $this->errors['email'] = 'Email is required';
        } else if ($this->checkEmail($email) == false) {
            $this->errors['email'] = 'Please enter a valid email';
        } else if ($this->checkEmailExists($email) == true) {
            $this->errors['email'] = 'Email already exists';
        }
    }

    /**
     * Check Name exists
     * @param string $name
     * @return bool
     */
    private function checkNameExists(string $name) : bool {
        $sql = "SELECT name FROM customers WHERE name = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
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

    /**
     * Check email exists
     * @param string $email
     * @return bool
     */
    private function checkEmailExists(string $email) : bool {
        $sql = "SELECT email FROM customers WHERE email = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0 ? true : false;
    }
}

?>