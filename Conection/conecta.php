<?php

$host = 'gondola.proxy.rlwy.net';
$port = '47233';
$dbname = 'railway';
$user = 'postgres';
$password = 'rAZTgbILJdwksCIOgrZovewsqnufWbVT';

try {
    // Crear conexión PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    
    // Configurar PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "Conexión exitosa a PostgreSQL Railway";
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
