<?php
class Database {
  private $host = 'localhost';
  private $dbName = 'projectSistemasUnah';
  private $username = 'root';
  private $password = 'User1234!';
  private $conn;

  public function connect() {
    $this->conn = null;
    try {
      $this->conn = new PDO(
        "mysql:host={$this->host};dbname={$this->dbName};charset=utf8",
        $this->username,
        $this->password
      );
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->conn->exec("SET time_zone = '-06:00'");
      date_default_timezone_set('America/Tegucigalpa');

    } catch (PDOException $e) {
      echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
      exit;
    }
    return $this->conn;
  }
  
}
