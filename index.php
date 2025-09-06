<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST method is allowed"]);
    exit;
}

$file = "products.json";
if (!file_exists($file)) {
    file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
}
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}
$errors = [];
if (empty($data['name']) || !is_string($data['name']) || strlen($data['name']) > 255) {
    $errors[] = "Product name is required and must be a string (max 255 characters).";
}
if (isset($data['description']) && !is_string($data['description'])) {
    $errors[] = "Description must be a string.";
}
if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
    $errors[] = "Price is required and must be a positive number.";
}
if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0 || intval($data['quantity']) != $data['quantity']) {
    $errors[] = "Quantity is required and must be a non-negative integer.";
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(["status" => "error", "errors" => $errors]);
    exit;
}

if (file_exists($file)) {
    $products = json_decode(file_get_contents($file), true);
    if (!is_array($products)) {
        $products = [];
    }
} else {
    $products = [];
}

$newProduct = [
    "id" => count($products) + 1,
    "name" => $data['name'],
    "description" => $data['description'] ?? "",
    "price" => (float)$data['price'],
    "quantity" => (int)$data['quantity']
];

$products[] = $newProduct;
file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT));

http_response_code(201);
echo json_encode([
    "status" => "success",
    "message" => "Product created successfully",
    "product" => $newProduct
]);
