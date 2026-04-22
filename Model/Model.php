<?php

declare(strict_types=1);

class Model
{
    protected $db;

    public function __construct()
    {
        // On récupère l'instance de connexion via votre classe Database
        try {
            $this->db = Database::connect();
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
}