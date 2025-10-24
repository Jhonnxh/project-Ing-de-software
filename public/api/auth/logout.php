<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/middleware/requireAuth.php'; // 👈 solo accede al middleware

try {
  // Validar token y obtener claims del usuario
  $claims = requireAuth();
  $jwtId  = $claims['jti'] ?? null;

  // Usar la clase Database desde dentro del middleware (que sí tiene acceso a /config)
  $db = (new Database())->connect();

  // Marcar la sesión como revocada
  if ($jwtId) {
    $stmt = $db->prepare("UPDATE sessions SET isRevoked = TRUE WHERE jwtId = ?");
    $stmt->execute([$jwtId]);
  }

  // Eliminar cookies en el navegador
  setcookie('accessToken', '', time() - 3600, '/', '', false, true);
  setcookie('refreshToken', '', time() - 3600, '/', '', false, true);

  echo json_encode(['ok' => true, 'message' => 'logout_success']);
} catch (Throwable $e) {
  http_response_code(401);
  echo json_encode(['error' => $e->getMessage()]);
}
