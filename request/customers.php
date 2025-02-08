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

    public function get(?string $id = null) {
        return $this->getCustomer($id);
    }

    public function addCustomer(?string $name = null, ?string $email = null) : string {
        return $this->addCustomerQuery($name, $email);
    }

    public function editCustomer(?string $id = null, ?string $name = null, ?string $email = null) {
        return $this->editCustomerQuery($id, $name, $email);
    }

    public function deleteCustomer(?string $id = null) : string {
        return $this->deleteCustomerQuery($id);
    }

    public function verifyToken(?string $token = null) : bool {
        return $this->checkToken($token);
    }

    public function unauthorizedData() : string {
        return $this->unauthorized();
    }
    
    public function badCustomerRequest() : string {
        return $this->badRequest();
    }

}

$customers = new CustomersRequest();


if($requestMethod == 'OPTIONS') {
    http_response_code(200);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization, X-Requested-With");
    exit();
}

if(!$customers->verifyToken($token)) {
    echo $customers->unauthorizedData();
    exit();
}

if($requestMethod == 'POST') {
    $id = $_GET['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;

    if($process && $process == 'get_all_customers') {
        echo $customers->getAll();
    }

    if($process && $process == 'add_customer') {
        echo $customers->addCustomer($name, $email);
    }

    if($process && $process == 'edit_customer') {
        echo $customers->editCustomer($id, $name, $email);   
    }

    if(!$process) {
        $customers->badCustomerRequest();
    }
}

if($requestMethod == 'GET') {
    $id = $_GET['id'] ?? null;

    if(isset($id)) {
        echo $customers->get($id);
    }

    if(!$id) {
        $customers->badCustomerRequest();
    }
}

if($requestMethod == 'DELETE') {
    $id = $_GET['id'] ?? null;
    echo $customers->deleteCustomer($id);
}

?>