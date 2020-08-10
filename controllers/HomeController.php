<?php


namespace app\controllers;

use app\framework\classes\Controller;
use app\framework\classes\Route;
use app\models\Task;
use JasonGrimes\Paginator;

class HomeController extends Controller
{
    
    /**
     * index action for the "/" route
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $task = new Task();
        if ($task->load($_POST) && $task->validate()) {
        
        }
        $data = Task::getPaginatedResults(
            $task,
            $_GET['page'] ?? 1,
            $_GET['sort'] ?? null,
            $_GET['sortType'] ?? null
        );
        return $this->render('index', $data);
    }
}