<?php
class SessionModel {
  private $conn;
  public function __construct($db) { $this->conn = $db; }

  public function createSession($idUser, $jwtId, $refreshTokenHash, $expiresAt) {
    $query = "
      INSERT INTO sessions (idUser, jwtId, refreshTokenHash, expiresAt)
      VALUES (:idUser, :jwtId, :refreshTokenHash, :expiresAt)
    ";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([
      ':idUser' => $idUser,
      ':jwtId' => $jwtId,
      ':refreshTokenHash' => $refreshTokenHash,
      ':expiresAt' => $expiresAt
    ]);
  }
}
