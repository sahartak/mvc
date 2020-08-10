<?php


namespace app\framework\classes;
use app\framework\interfaces\{AppInterface, Configurable};

class App implements AppInterface
{
    const CONFIG_DATABASE = 'database';
    const CONFIG_ROUTES = 'routes';
    
    const CONFIG_KEYS = [
        self::CONFIG_DATABASE => Database::class,
        self::CONFIG_ROUTES => Route::class
    ];
    
    protected static string $basePath;
    
    /**
     * App constructor.
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->extractConfigs($configs);
    }
    
    /**
     * Returns base path where application is running
     * @return string
     */
    public static function getBasePath(): string
    {
        return static::$basePath;
    }
    
    /**
     * run application and returning response
     * @return void
     */
    public function run(): void
    {
        $route = new Route();
        $result = $route->run();
        if (is_null($result)) {
            header("HTTP/1.0 404 Not Found");
        }
        echo $result;
    }
    
    /**
     * Extracting application configs from given configs array and initializing classes
     * @param array $configs
     * @return bool
     */
    protected function extractConfigs(array $configs)
    {
        static::$basePath = $configs['basePath'];
        foreach (static::CONFIG_KEYS as $configKey => $class) {
            /* @var Configurable $class */
            $class::setConfigs($configs[$configKey] ?? []);
            if (!$class::validateConfigs()) {
                throw new \InvalidArgumentException('Invalid arguments for '.$configKey);
            }
        }
        return true;
    }
}