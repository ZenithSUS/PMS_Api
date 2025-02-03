<?php
include_once('../headers.php');
include_once('../queries/auth.php');

$requestMethod = $_SERVER["REQUEST_METHOD"] ?? null;
$process = isset($_POST['process']) ? $_POST['process'] : null;

class AuthRequest extends Auth {

    public function __construct(){
        parent::__construct();
    }
   
    public function login(string $account, string $password) : string {
        return $this->loginUser($account, $password);
    }

    public function register(string $email, string $username, string $password, string $confirmpassword) : string {
        return $this->registerUser($email, $username, $password, $confirmpassword);
    }
}

$auth = new AuthRequest();

if($requestMethod == "POST") {

    if($process && $process == "login") {
        $account = $_POST['account'] ?? null;
        $password = $_POST['password'] ?? null;
        echo $auth->login($account, $password);
    }

    if($process && $process == "register") {
        $email = $_POST['email'] ?? null;
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $confirmpassword = $_POST['confirmpassword'] ?? null;
        echo $auth->register($email, $username, $password, $confirmpassword);
    }
}


?>