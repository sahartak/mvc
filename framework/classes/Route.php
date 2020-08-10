<?php


namespace app\framework\classes;


use app\framework\interfaces\Configurable;

class Route implements Configurable
{
    
    const CONFIG_APP_URL = 'appUrl';
    
    protected static string $appUrl;
    protected static array $routes = [];
    
    protected string $routeName;
    protected $controller;
    protected string $action;
    
    /**
     * @param array $configs
     * @return bool
     */
    public static function setConfigs(array $configs): bool
    {
        static::$routes = $configs;
        return true;
    }
    
    /**
     * @return string|null
     */
    protected function retrieveAction(): ?string
    {
        $routeName = $this->parseRouteName();
        $rule = static::$routes['rules'][$routeName] ?? null;
        if (!$rule) {
            return null;
        }
        
        $routeParts = explode('@', $rule);
        $controllerName = ucfirst($routeParts[0]).'Controller';
        $controller = $this->initController($controllerName);
        if (!$controller) {
            return null;
        }
        
        $action = $routeParts[1] ?? 'index';
        if (!method_exists($controller, $action)) {
            return null;
        }
        
        $this->action = $action;
        return $controller->$action();
    }
    
    
    /**
     * @param string $name
     * @return Controller|null
     */
    protected function initController(string $name): ?Controller
    {
        try {
            $controller = '\app\controllers\\'.$name;
            $this->controller = new $controller();
            return $this->controller;
        } catch (\Exception $exception) {
            return null;
        }
    }
    
    /**
     * @return string
     */
    public function run(): ?string
    {
        $this->parseRouteName();
        return $this->retrieveAction();
    }
    
    
    /**
     * @return string
     */
    protected function parseRouteName(): string
    {
        $apUrl = static::getAppUrl();
        $subFolderPaths = explode('/', $apUrl);
        $uri = $_SERVER['REQUEST_URI'];
        if (count($subFolderPaths) > 1) {
            $uri = str_replace(array_slice($subFolderPaths, 1), '', $uri);
        }
        $this->routeName = str_replace([$apUrl, '?'.$_SERVER['QUERY_STRING'], '//'], '', $uri);
        if (!$this->routeName) {
            $this->routeName = '/';
        }
        if (strpos($this->routeName, '/') !== 0) {
            $this->routeName = '/'.$this->routeName;
        }
        return $this->routeName;
    }
    
    /**
     * @inheritDoc
     */
    public static function validateConfigs(): bool
    {
        if (empty(static::$routes[static::CONFIG_APP_URL])) {
            throw new \InvalidArgumentException('Missing '.static::CONFIG_APP_URL);
        }
        if (empty(static::$routes['rules'])) {
            throw new \InvalidArgumentException('Missing route rules');
        }
        
        return true;
    }
    
    /**
     * Returns application base url
     * @return string
     */
    public static function getAppUrl(): string
    {
        return static::$routes[static::CONFIG_APP_URL];
    }
}