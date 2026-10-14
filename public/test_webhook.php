<?php
/**
 * Test simple directo para verificar si el servidor Apache / PHP recibe peticiones
 * en la ruta del webhook sin depender del router de Laravel
 */
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$headers = getallheaders();
$rawBody = file_get_contents('php://input');

$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ip_cliente' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'method' => $method,
    'query' => $_GET,
    'body_length' => strlen($rawBody),
    'body_preview' => substr($rawBody, 0, 500),
    'headers' => $headers,
];

// Escribir a un archivo de log local en public/ para inspección inmediata
file_put_contents(__DIR__ . '/webhook_last_hit.json', json_encode($logData, JSON_PRETTY_PRINT));

echo json_encode([
    'status' => 'ONLINE',
    'message' => 'El servidor web está escuchando correctamente.',
    'hit' => $logData
], JSON_PRETTY_PRINT);
