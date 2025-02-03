<?php
include('../headers.php');
include_once('../queries/customers.php');

$requestMethod = $_SERVER["REQUEST_METHOD"] ?? null;
$process = isset($_POST['process']) ? $_POST['process'] : null;

if(function_exists('getallheaders')) {
    $headers = getallheaders();
    if(isset($headers['Authorization'])) {
        $headers['Authorization'] = $headers['Authorization'];
    } elseif(isset($headers['X-Authorization'])) {
        $headers['Authorization'] = $headers['X-Authorization'];
    }
} else if(function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
} else {
    $headers = array();
    if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    } else if(isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_X_AUTHORIZATION'];
    } else {
        $headers['Authorization'] = null;
    }
}  

$token = $headers['Authorization'] ?? null;
if(isset($token) && strpos($token, 'Bearer ') !== false) {
    $token = explode(' ', $token)[1];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

class CustomersRequest extends Customers {

    public function __construct() {
        parent::__construct();
    }

    public function getAll() {
        return $this->getAllCustomers();
    }

    public function verifyToken(?string $token = null) : bool {
        return $this->checkToken($token);
    }

    public function unauthorizedData() : string {
        return $this->unauthorized();
    }

}

$customers = new CustomersRequest();


if($requestMethod == 'OPTIONS') {
    http_response_code(200);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization, X-Requested-With");
    exit();
}

if(!$customers->verifyToken($token)) {
    echo $customers->unauthorizedData();
    exit();
}

if($requestMethod == 'POST') {
    echo $customers->getAll();
}

if($requestMethod == 'GET') {
    echo $customers->getAll();
}

?>