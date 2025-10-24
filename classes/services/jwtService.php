<?php
class JwtService {
  private $config;

  public function __construct() {
    $this->config = include __DIR__ . '/../../config/jwtConfig.php';
  }

  private function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  public function generateTokens(array $user, array $roles): array {
    $jwtId = bin2hex(random_bytes(16));
    $issuedAt = time();
    $expire = $issuedAt + (int)$this->config['accessExpire'];

    $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payloadArr = [
      'sub'   => $user['idUser'],
      'iat'   => $issuedAt,
      'exp'   => $expire,
      'jti'   => $jwtId,
      'roles' => $roles,
      'iss'   => $this->config['issuer']   ?? 'unah-system',
      'aud'   => $this->config['audience'] ?? 'unah-system-users',
    ];
    $payload = $this->base64UrlEncode(json_encode($payloadArr));
    $signature = hash_hmac('sha256', "$header.$payload", $this->config['secretKey']);
    $accessToken = "$header.$payload.$signature";

    $refreshToken = bin2hex(random_bytes(32));
    $refreshHash  = hash('sha256', $refreshToken);
    $refreshExpireAt = time() + (int)$this->config['refreshExpire'];

    return [
      'accessToken'   => $accessToken,
      'refreshToken'  => $refreshToken,
      'refreshHash'   => $refreshHash,
      'refreshExpire' => date('Y-m-d H:i:s', $refreshExpireAt),
      'jwtId'         => $jwtId,
      'sub'           => $user['idUser'],
      'roles'         => $roles
    ];
  }

  public function verifyAccessToken(string $token): array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) throw new Exception('invalid_token_structure');

    [$headerB64, $payloadB64, $signature] = $parts;
    $expected = hash_hmac('sha256', "$headerB64.$payloadB64", $this->config['secretKey']);
    if (!hash_equals($expected, $signature)) throw new Exception('invalid_signature');

    $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
    if (!$payload) throw new Exception('invalid_payload');
    if (isset($payload['exp']) && $payload['exp'] < time()) throw new Exception('token_expired');

    return $payload;
  }
}

