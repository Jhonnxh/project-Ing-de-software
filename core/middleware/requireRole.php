<?php
function requireRole(array $allowedRoles) {
  $token = $_COOKIE['accessToken'] ?? null;
  if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
  }

  [$header, $payload, $signature] = explode('.', $token);
  $data = json_decode(base64_decode($payload), true);

  $userRoles = array_column($data['roles'], 'roleName');
  foreach ($allowedRoles as $role) {
    if (in_array($role, $userRoles, true)) return;
  }

  http_response_code(403);
  echo json_encode(['error' => 'Forbidden']);
  exit;
}
