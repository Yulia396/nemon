<?php

/*$host = "sql112.infinityfree.com";
$user = "if0_38428078";  
$password = "PMtsH8MpGgeAE";
$dbname = "if0_38428078_facturanemon";
*/

$host = "localhost";
$user = "root";
$password = "";
$dbname = "facturanemon";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Mostrar error en formato JSON
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit;
}
?>