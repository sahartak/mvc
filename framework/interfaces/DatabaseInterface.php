<?php
namespace app\framework\interfaces;

interface DatabaseInterface
{
    /**
     * Returns class object
     * @return static
     */
    public static function getInstance();
    
    /**
     * Method
     * @param string $host
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function connect(string $host, string $dbname, string $username, string $password);
    
    /**
     * Returns Database connection object
     * @return mixed
     */
    public function getDb();
}