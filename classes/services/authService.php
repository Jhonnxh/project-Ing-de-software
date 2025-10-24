<?php
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/jwtService.php';

class AuthService {
  private $db;
  private $jwt;

  public function __construct($db) {
    $this->db = $db;
    $this->jwt = new JwtService();
  }

  public function login(string $email, string $password): array {
    $userModel = new UserModel($this->db);
    $user = $userModel->findByEmail($email);

    if (!$user || !password_verify($password, $user['passwordHash'])) {
      throw new Exception('invalid_credentials');
    }

    $roles = $userModel->getUserRoles($user['idUser']);
    $tokens = $this->jwt->generateTokens($user, $roles);

    // Store refresh token hash
    $stmt = $this->db->prepare("
      INSERT INTO sessions (idUser, jwtId, refreshTokenHash, expiresAt)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
      $user['idUser'],
      $tokens['jwtId'],
      $tokens['refreshHash'],
      $tokens['refreshExpire'],
    ]);

    return $tokens;
  }
}
