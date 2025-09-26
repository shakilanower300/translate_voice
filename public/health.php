<?php
// Simple health check that doesn't require Laravel bootstrap
http_response_code(200);
header('Content-Type: application/json');

echo json_encode([
    'status' => 'ok',
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
    'server' => 'PHP Built-in'
]);
?>