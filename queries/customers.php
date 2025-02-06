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

    protected function addCustomerQuery(?string $name = null, ?string $email = null) : string {
        $sql = "INSERT INTO customers (id, name, email) VALUES (UUID(), ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if(empty($name) || is_null($name)) {
            $this->errors['name'] = 'Name is required';
        } else if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $this->errors['name'] = 'Please enter a valid name';
        }

        if(empty($email) || is_null($email)) {
            $this->errors['email'] = 'Email is required';
        } else if ($this->checkEmail($email) == false) {
            $this->errors['email'] = 'Please enter a valid email';
        }

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }

        $stmt->bind_param('ss', $name, $email);
        return $stmt->execute() ? $this->success('customer') : $this->queryFailed();
    }

    protected function deleteCustomerQuery(?string $id = null) : string {
        $sql = "DELETE FROM customers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if(!$stmt) {
            return $this->queryFailed();
        }

        $stmt->bind_param('s', $id);
        return $stmt->execute() ? $this->success() : $this->notFound();
    }

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