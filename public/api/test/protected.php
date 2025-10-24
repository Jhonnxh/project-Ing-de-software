<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/middleware/requireAuth.php';
require_once __DIR__ . '/../../../config/connection.php';

try {
  $claims = requireAuth();

  $db = (new Database())->connect();
  $stmt = $db->prepare("SELECT fullName FROM users WHERE idUser = ?");
  $stmt->execute([$claims['sub']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    'ok' => true,
    'fullName' => $user['fullName'] ?? 'Usuario',
    'roles' => $claims['roles'] ?? []
  ]);
} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(['error' => $e->getMessage()]);
}
