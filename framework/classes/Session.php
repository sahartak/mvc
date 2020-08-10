<?php


namespace app\framework\classes;


class Session
{
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Session constructor.
     */
    protected function __construct()
    {
        session_start();
    }

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public function get(string $key, $defaultValue = null)
    {
        return $_SESSION[$key] ?? $defaultValue;
    }

    /**
     * @param string $key
     */
    public function remove(string $key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}