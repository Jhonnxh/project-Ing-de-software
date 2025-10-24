<?php
class RoleModel {
  private $conn;
  public function __construct($db) { $this->conn = $db; }

  public function getRolesByUserId($idUser) {
    $query = "
      SELECT r.roleName, ur.scopeJson
      FROM user_roles ur
      JOIN roles r ON ur.idRole = r.idRole
      WHERE ur.idUser = :idUser
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':idUser', $idUser);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
