<?php
include_once('../headers.php');
include_once('../queries/orders.php');

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


if($requestMethod == 'OPTIONS') {
    http_response_code(200);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization, X-Requested-With");
    exit();
}   

class OrdersRequest extends Orders {

    public function __construct() {
        parent::__construct();
    }

    public function getAll() : string {
        return $this->getAllOrders();
    }

    public function badOrderRequest() : string {
        return $this->badRequest();
    }
}

$orders = new OrdersRequest();

if($requestMethod == 'POST') {
    
    if($process && $process == 'get_all_orders') {
        echo $orders->getAll();
    }

    if(!$process) {
        $orders->badOrderRequest();
    }
}

if($requestMethod == 'GET') {
    echo $orders->getAll();
}

?>