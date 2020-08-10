<?php


namespace app\controllers;

use app\framework\classes\Controller;
use app\framework\classes\Route;
use app\models\Task;
use app\framework\classes\Session;

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
        $task = new Task([
            'status' => 0,
            'is_edited' => 0
        ]);
        $session = Session::getInstance();
        if ($task->load($_POST) && $task->save()) {
            $session->set('success', 'Task successfully created!');
            return $this->redirect(Route::getAppUrl());
        }
        $data = Task::getPaginatedResults(
            $task,
            $_GET['page'] ?? 1,
            $_GET['sort'] ?? null,
            $_GET['sortType'] ?? null
        );
        if ($session->get('success')) {
            $data['success'] = $session->get('success');
            $session->remove('success');
        }
        $data['task'] = $task;
        $data['errors'] = $task->getValidationErrors();
        $data['appUrl'] = Route::getAppUrl();
        $data['isAdmin'] = $session->get('isAdmin');
        return $this->render('index', $data);
    }
}