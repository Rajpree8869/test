<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only GET method is allowed"]);
    exit;
}
$file = "products.json";
if (!file_exists($file)) {
    echo json_encode(["status" => "success", "products" => []]);
    exit;
}

$products = json_decode(file_get_contents($file), true);

if (!is_array($products)) {
    $products = [];
}

http_response_code(200);
echo json_encode([
    "status" => "success",
    "products" => $products
]);
