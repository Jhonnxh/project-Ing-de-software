<?php
class UserModel {
  private $db;

  public function __construct($db) {
    $this->db = $db;
  }

  public function findByEmail(string $email): ?array {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public function getUserRoles(int $userId): array {
    $stmt = $this->db->prepare("
      SELECT r.roleName 
      FROM user_roles ur
      JOIN roles r ON ur.idRole = r.idRole
      WHERE ur.idUser = ?
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_column($rows, 'roleName');
  }
}

