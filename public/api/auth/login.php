<?php
declare(strict_types=1);
header('Content-Type: application/json');
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../classes/controllers/authController.php';

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
  }

  $data = json_decode(file_get_contents('php://input'), true) ?? [];
  $controller = new AuthController();
  $result = $controller->login($data);

  // ⚡️ AÑADE ESTO:
  // Guarda los tokens en cookies HttpOnly para que el navegador las mande automáticamente.
  setcookie('accessToken', $result['accessToken'], [
    'expires'  => time() + 900,   // 15 minutos
    'path'     => '/',
    'secure'   => false,          // true solo si usas HTTPS
    'httponly' => true,           // no accesible por JS
    'samesite' => 'Lax'
  ]);

  setcookie('refreshToken', $result['refreshToken'], [
    'expires'  => time() + 60*60*24*30, // 30 días
    'path'     => '/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax'
  ]);

  echo json_encode([
    'ok'      => true,
    'message' => 'login_success',
    'userId'  => $result['sub'] ?? null,
    'roles'   => $result['roles'] ?? [],
  ]);
} catch (Throwable $e) {
  http_response_code(401);
  echo json_encode(['error' => $e->getMessage()]);
}
