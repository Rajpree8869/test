<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only PUT method is allowed"]);
    exit;
}

$file ="products.json";
if (!file_exists($file)) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Products file not found"]);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Product ID is required and must be numeric"]);
    exit;
}
$id = (int)$_GET['id'];

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}

$products = json_decode(file_get_contents($file), true);
if (!is_array($products)) {
    $products = [];
}

$found = false;
foreach ($products as &$product) {
    if ($product['id'] === $id) {
        if (isset($data['name'])) {
            if (!is_string($data['name']) || strlen($data['name']) > 255) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Invalid name"]);
                exit;
            }
            $product['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            if (!is_string($data['description'])) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Description must be a string"]);
                exit;
            }
            $product['description'] = $data['description'];
        }
        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] <= 0) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Price must be a positive number"]);
                exit;
            }
            $product['price'] = (float)$data['price'];
        }
        if (isset($data['quantity'])) {
            if (!is_numeric($data['quantity']) || $data['quantity'] < 0 || intval($data['quantity']) != $data['quantity']) {
                http_response_code(422);
                echo json_encode(["status" => "error", "message" => "Quantity must be a non-negative integer"]);
                exit;
            }
            $product['quantity'] = (int)$data['quantity'];
        }
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}

file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT));
http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Product updated successfully",
    "product" => $product
]);
