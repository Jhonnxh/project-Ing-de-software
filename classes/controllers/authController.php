<?php
require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../services/authService.php';

class AuthController {
  private $authService;

  public function __construct() {
    $db = (new Database())->connect();
    $this->authService = new AuthService($db);
  }

  public function login(array $data): array {
    $email = trim($data['email'] ?? '');
    $password = (string)($data['password'] ?? '');
    if ($email === '' || $password === '') {
      throw new Exception('email_and_password_required');
    }
    return $this->authService->login($email, $password);
  }
}
