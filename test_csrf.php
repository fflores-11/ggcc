<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>Prueba CSRF</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Token en sesión: " . ($_SESSION['csrf_token'] ?? 'NO EXISTE') . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Datos POST recibidos:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    $token = $_POST['csrf_token'] ?? '';
    echo "<p>Token recibido: " . htmlspecialchars($token) . "</p>";
    echo "<p>Token en sesión: " . ($_SESSION['csrf_token'] ?? 'NO EXISTE') . "</p>";
    echo "<p>Verificación: " . (verifyCSRFToken($token) ? 'OK ✓' : 'FALLÓ ✗') . "</p>";
}

$token = generateCSRFToken();
?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $token ?>">
    <button type="submit">Probar Token</button>
</form>
