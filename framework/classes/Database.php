<?php


namespace app\framework\classes;


use app\framework\interfaces\Configurable;
use app\framework\interfaces\DatabaseInterface;
use InvalidArgumentException;
use Opis\Database\Connection;

class Database implements DatabaseInterface, Configurable
{
    
    protected static $instance;
    protected ?\Opis\Database\Database $db;
    
    protected static string $host;
    protected static string $username;
    protected static string $password;
    protected static string $dbname;
    
    /**
     * Database constructor.
     */
    protected function __construct()
    {
        if (!static::validateConfigs()) {
            throw new InvalidArgumentException('missing db configs');
        }
        $this->db = $this->connect(static::$host, static::$dbname, static::$username, static::$password);
    }
    
    /**
     * @return Database
     */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Returns connection object
     * @return \Opis\Database\Database|null
     */
    public function getDb(): ?\Opis\Database\Database
    {
        return $this->db;
    }
    
    /**
     * Returns last inserted id from database
     * @return int
     */
    public function getLastInsertId(): int
    {
        return $this->getDb()->getConnection()->getPDO()->lastInsertId();
    }
    
    
    /**
     * @param string $host
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @return \Opis\Database\Database
     */
    public function connect(string $host, string $dbname, string $username, string $password): \Opis\Database\Database
    {
        $connection = new Connection(
            "mysql:host={$host};dbname={$dbname}",
            $username,
            $password
        );
        $connection->initCommand('SET NAMES UTF8');
        return new \Opis\Database\Database($connection);
    }
    
    public static function setConfigs(array $configs): bool
    {
        static::$host = $configs['host'] ?? null;
        static::$username = $configs['username'] ?? null;
        static::$password = $configs['password'] ?? null;
        static::$dbname = $configs['dbname'] ?? null;
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public static function validateConfigs(): bool
    {
        return !is_null(static::$host) && !is_null(static::$dbname) && !is_null(static::$username) && !is_null(static::$password);
    }
}