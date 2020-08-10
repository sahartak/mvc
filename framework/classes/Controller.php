<?php


namespace app\framework\classes;


abstract class Controller
{
    /**
     * render view by view name and variable params to be passed to view
     * @param string $view
     * @param array $params
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $view, array $params = []): string
    {
        $viewsPath = App::getBasePath().'/views';
        $loader = new \Twig\Loader\FilesystemLoader($viewsPath);
        $loader->addPath($viewsPath, 'views');
        $twig = new \Twig\Environment($loader);
        return $twig->render('@views/'.$view.'.html', $params);
    }
    
    /**
     * Redirect page to given url
     * @param string $url
     * @return bool
     */
    public function redirect(string $url)
    {
        header("Location: $url");
        return true;
    }
}