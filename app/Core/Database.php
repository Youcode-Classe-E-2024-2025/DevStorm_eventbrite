<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null; 
    private ?PDO $db = null; 

    private static string $host = 'localhost';
    private static string $dbname = 'eventbrite';
    private static string $user = 'postgres';
<<<<<<< HEAD
    private static string $pass = '1234';
=======
    private static string $pass = 'root';
>>>>>>> dfb0c8942ccc4055d0d0827fbc787eb69a1859c5

    private function __construct() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        if ($this->db === null) {
            try {
       
                $dsn = "pgsql:host=" . self::$host . ";dbname=" . self::$dbname . ";port=5432";
                $this->db = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                
                $this->initializeDatabase();
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->db;
    }

   
    private function initializeDatabase()
    {
        try {
            
            $tempDb = new PDO("pgsql:host=" . self::$host . ";dbname=postgres;port=5432", self::$user, self::$pass);
            $tempDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            
            $dbExists = $tempDb->query("SELECT 1 FROM pg_database WHERE datname = '" . self::$dbname . "'")->fetch();

            if (!$dbExists) {
                $tempDb->exec("CREATE DATABASE " . self::$dbname);

                $sql = file_get_contents('database/database.sql');
                $tempDb->exec($sql);
            }

            $tempDb = null;
        } catch (PDOException $e) {
            error_log("Database Initialization Error: " . $e->getMessage());
            die("Database initialization failed: " . $e->getMessage());
        }
    }
}
