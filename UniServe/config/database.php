<?php
class Database {
    private $host     = "localhost";
    private $db_name  = "rendez_vous_db";
    private $username = "root";
    private $password = "";
    private $conn;

    // Méthode d'instance — utilisée par RendezvousController
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }
        return $this->conn;
    }

    // Méthode statique — utilisée par Model.php (UniServe)
    public static function connect(): PDO {
        $host     = "localhost";
        $db_name  = "rendez_vous_db";
        $username = "root";
        $password = "";

        $pdo = new PDO(
            "mysql:host=" . $host . ";dbname=" . $db_name,
            $username,
            $password
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE,      PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec("set names utf8");
        return $pdo;
    }
}
?>