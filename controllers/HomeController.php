<?php


namespace app\controllers;

use app\framework\classes\Controller;
use app\framework\classes\Route;
use app\models\Task;

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
        $task->status = 0;
        if ($task->load($_POST) && $task->save()) {
            return $this->redirect(Route::getAppUrl());
        }
        $data = Task::getPaginatedResults(
            $task,
            $_GET['page'] ?? 1,
            $_GET['sort'] ?? null,
            $_GET['sortType'] ?? null
        );
        $data['task'] = $task;
        $data['errors'] = $task->getValidationErrors();
        return $this->render('index', $data);
    }
}