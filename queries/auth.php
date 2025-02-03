<?php
include_once('token.php');
class Auth extends Token {
    public function __construct() {
        parent::__construct();
    }

    protected function loginUser(string $account, string $password) : string { 
        $sql = "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $account, $account);

        if(!$stmt->execute()) {
            return $this->queryFailed();
        }

        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if(!password_verify($password, $row['password'])) {
                $this->errors['auth_error'] = 'User or password is incorrect';
                return $this->fieldError($this->errors);
            }

            $token = $this->generateToken();
            $this->insertToken($token, $row['id']);
            return $this->success('login', $token);
        }

        
        if(!$this->checkLoginAuth($account, $password)){
            $this->errors['auth_error'] = 'User or password is incorrect';
            return $this->fieldError($this->errors);
        }

        return $this->queryFailed();
    }

    private function checkLoginAuth(string $account) : bool {
        $sql = "SELECT username, email FROM users WHERE email = ? OR username = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $account, $account);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }


    protected function registerUser(string $email, string $username, string $password, string $confirmpassword) : string {
        $this->checkFields($email, $username, $password, $confirmpassword);

        if(!empty($this->errors)) {
            return $this->fieldError($this->errors);
        }

        return $this->registerUserQuery($email, $username, $password);
    }

    private function checkFields(string $email, string $username, string $password, string $confirmpassword) : void {
        if(empty($email) || is_null($email)) {
            $this->errors['email'] = "Please fill the email";
        } else if ($this->checkEmail($email) == false) {
            $this->errors['email'] = "Please enter a valid email";
        } else if($this->emailExists($email)) {
            $this->errors['email'] = "Email already exists";
        }

        if(empty($username) || is_null($username)) {
            $this->errors['username'] = "Please fill the username";
        } else if($this->validateUsername($this->checkUsername($username))) {
            foreach($this->checkUsername($username) as $type => $hasType){
                if(!$hasType) $this->errors['usernameValid'][$type] = $type . " is required";
            }
        } 
        
        if($this->usernameExists($username)) {
            $this->errors['username'] = "Username already exists";
        }

        if(empty($password) || is_null($password)) {
            $this->errors['password'] = "Please fill the password";
        } else if(strlen($password) < 8) {
            $this->errors['password'] = "Password must be at least 8 characters";
        } else if($this->validatePassword($this->checkPassword($password))) {
            foreach($this->checkPassword($password) as $type => $hasType){
                if(!$hasType) $this->errors['passwordValid'][$type] = $type . " is required";
            }
        }

        if(empty($confirmpassword) || is_null($confirmpassword)) {
            $this->errors['confirmpassword'] = "Please fill the confirm password";
        } else if($confirmpassword != $password) {
            $this->errors['confirmpassword'] = "Passwords do not match";
        }

    }

    private function registerUserQuery(string $email, string $username, string $password) : string {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (id, email, username, password) VALUES (UUID(), ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $email, $username, $password);
        return $stmt->execute() ? $this->success('register') : $this->queryFailed();
    }
    
    private function checkUsername(string $username) : array {
        $uppercase = preg_match('/[A-Z]/', $username);
        $specialchars = preg_match('/[^A-Za-z0-9]/', $username);
        $usernameLength = strlen($username) > 5;
        
        return [
            "Uppercase" => $uppercase,
            "Special characters" => $specialchars,
            "5 characters" => $usernameLength
        ];
    }

    private function usernameExists(string $username) : bool {
        $sql = "SELECT username FROM users WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function validateUsername(array $username) : bool {
        foreach(array_keys($username) as $hasType) {
            $status = $hasType ? true : false;
        }
        return $status;
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

    private function emailExists(string $email) : bool {
        $sql = "SELECT email FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function checkPassword(string $password) : array {
        $uppercase = preg_match('/[A-Z]/', $password);
        $lowercase = preg_match('/[a-z]/', $password);
        $specialchars = preg_match('/[^A-Za-z0-9]/', $password);
        $numericVal = preg_match('/[0-9]/', $password);

       return [
            "Uppercase" => $uppercase,
            "Lowercase" => $lowercase,
            "Special characters" => $specialchars,
            "Numeric value" => $numericVal
       ];
    }


    private function validatePassword(array $password) : bool {
  
        foreach (array_keys($password) as $hasType){
            $status = $hasType ? true : false;
        }
        return $status;
    }
}
?>