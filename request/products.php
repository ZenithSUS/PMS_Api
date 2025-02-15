<?php
include_once('../headers.php');
include_once('../queries/products.php');

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
if(isset($token) && strpos($token, 'Bearer ' !== false)) {
    $token = explode(' ', $token)[1];
}

class ProductsRequest extends Products {
    public function __construct() {
        parent::__construct();
    }

    public function getAll() : string {
        return $this->getAllProducts();
    }

    public function get(?string $id = null) : string {
        return $this->getProduct($id);
    }

    public function addProduct(?string $productName = null, ?string $quantity = null, ?string $price = null) : ?string {
        return $this->addProductQuery($productName, $quantity, $price);
    }

    public function editProduct(?string $id = null, ?string $productName = null, ?string $quantity = null, ?string $price = null) : ?string {
        return $this->editProductQuery($id, $productName, $quantity, $price);
    }

    public function deleteProduct(?string $id = null) : string {
        return $this->deleteProductQuery($id);
    }
    
    public function badProductRequest() : string {
        return $this->badRequest();
    }

    public function verifyToken(?string $token = null) : bool {
        return $this->checkToken($token);
    }

    public function unauthorizedData() : string {
        return $this->unauthorized();
    }
}

$productsRequest = new ProductsRequest();

if($requestMethod == 'OPTIONS') {
    http_response_code(200);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization, X-Requested-With");
    exit();
}

if(!$productsRequest->verifyToken($token)) {
    echo $productsRequest->unauthorizedData();
    exit();
}

if($requestMethod == 'POST') {
    
    if($process && $process == 'get_all_products') {
        echo $productsRequest->getAll();
    }

    if($process && $process == 'add_product') {
        $productName = $_POST['productName'] ?? null;
        $quantity = $_POST['quantity'] ?? null;
        $price = $_POST['price'] ?? null;
        echo $productsRequest->addProduct($productName, $quantity, $price);
    }

    if($process && $process == 'edit_product') {
        $id = $_GET['id'] ?? null;
        $productName = $_POST['productName'] ?? null;
        $quantity = $_POST['quantity'] ?? null;
        $price = $_POST['price'] ?? null;
        echo $productsRequest->editProduct($id, $productName, $quantity, $price);
    }

    if(!$process) {
        $productsRequest->badProductRequest();
    }
}

if($requestMethod == 'GET') {
    $id = $_GET['id'] ?? null;
    if(isset($id)) {
        echo $productsRequest->get($id);
    } else {
        echo $productsRequest->getAll();
    }
}

if($requestMethod == 'DELETE') {
    $id = $_GET['id'] ?? null;

    if(!isset($id)) {
        echo $productsRequest->badProductRequest();
        exit();
    }

    echo $productsRequest->deleteProduct($id);
}
?>