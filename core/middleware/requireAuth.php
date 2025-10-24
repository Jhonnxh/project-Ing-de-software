<?php
require_once __DIR__ . '/../../classes/services/jwtService.php';
require_once __DIR__ . '/../../config/connection.php';

/**
 * Obtiene el token desde el header Authorization o desde la cookie HttpOnly.
 */
function getBearerOrCookieToken(): ?string {
  $headers = function_exists('getallheaders') ? getallheaders() : [];
  $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
  if (stripos($auth, 'Bearer ') === 0) {
    return trim(substr($auth, 7));
  }
  // ✅ Leer directamente desde la cookie que guarda login.php
  return $_COOKIE['accessToken'] ?? null;
}

/**
 * Valida un JWT, comprueba la sesión en base de datos, y actualiza actividad.
 */
function requireAuth(): array {
  header('Content-Type: application/json');

  $token = getBearerOrCookieToken();
  error_log("ACCESS TOKEN: " . substr($token ?? 'NULL', 0, 40));

  if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
  }

  try {
    $jwt = new JwtService();
    $claims = $jwt->verifyAccessToken($token);
    $jwtId  = $claims['jti'] ?? null;
    if (!$jwtId) throw new Exception('invalid_jti');

    $db = (new Database())->connect();
    $stmt = $db->prepare("
      SELECT isRevoked, expiresAt, lastActivity
      FROM sessions
      WHERE jwtId = ?
      ORDER BY idSession DESC
      LIMIT 1
    ");
    $stmt->execute([$jwtId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) throw new Exception('session_not_found');
    if (!empty($session['isRevoked'])) throw new Exception('session_revoked');
    if (strtotime($session['expiresAt']) < time()) throw new Exception('session_expired');

    // 🧠 Control de inactividad (más tolerante)
    $inactivitySeconds = 60 * 60; // 1 hora
    $last = strtotime($session['lastActivity'] ?? '1970-01-01 00:00:00');
    $diff = time() - $last;
    error_log("DEBUG lastActivity: {$session['lastActivity']} | diff: {$diff}s");

    // Control de inactividad (solo si lastActivity no está vacío)
      if (!empty($session['lastActivity'])) {
        $last = strtotime($session['lastActivity']);
        $diff = abs(time() - $last);
        // si la diferencia es menor a 5 horas, mantenemos la sesión activa
        if ($diff > 60 * 60 * 5) { // 5 horas de inactividad real
          $upd = $db->prepare("UPDATE sessions SET isRevoked = TRUE WHERE jwtId = ?");
          $upd->execute([$jwtId]);
          throw new Exception('session_inactive');
        }
      }

    // ✅ Refrescar actividad
    $upd = $db->prepare("UPDATE sessions SET lastActivity = NOW() WHERE jwtId = ?");
    $upd->execute([$jwtId]);

    $GLOBALS['authClaims'] = $claims;
    return $claims;

  } catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
  }
}

/**
 * Verifica si el usuario tiene alguno de los roles permitidos.
 */
function requireRole(array $allowedRoles): array {
  $claims = $GLOBALS['authClaims'] ?? requireAuth();
  $roles = array_map(
    fn($r) => is_array($r) ? ($r['roleName'] ?? $r['role'] ?? '') : $r,
    $claims['roles'] ?? []
  );
  foreach ($allowedRoles as $role) {
    if (in_array($role, $roles, true)) return $claims;
  }
  http_response_code(403);
  echo json_encode(['error' => 'forbidden']);
  exit;
}
