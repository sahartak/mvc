<?php


namespace app\controllers;

use app\framework\classes\Controller;
use app\framework\classes\Session;
use app\models\Task;
use app\framework\classes\Route;

class AdminController extends Controller
{
    
    const ADMIN_LOGIN = 'admin';
    const ADMIN_PASSWORD = '123';
    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function login()
    {
        $data = ['errors' => []];
        $session = Session::getInstance();
        if ($session->get('isAdmin')) {
            return $this->redirect(Route::getAppUrl());
        }
        
        if ($_POST) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if (!$username) {
                $data['errors']['username'] = 'username is required';
            }
            if (!$password) {
                $data['errors']['password'] = 'password is required';
            }
            if (!$data['errors']) {
                if ($username == static::ADMIN_LOGIN && $password == self::ADMIN_PASSWORD) {
                    $session->set('isAdmin', true);
                    return $this->redirect(Route::getAppUrl());
                } else {
                    $data['errors']['wrongCredentials'] = 'wrong credentials';
                }
            }
        }
        
        return $this->render('login', $data);
    }
    
    
    public function edit()
    {
        $this->checkAdmin();
        $id = intval($_GET['id'] ?? null);
        if (!$id) {
            return null;
        }
        $task = Task::findById($id);
        if (!$task) {
            return null;
        }
        $session = Session::getInstance();
        $attributes = array_merge($task->safeAttributes(), ['status']);
        $oldText = $task->text;
        if ($task->load($_POST, $attributes)) {
            if ($task->text !== $oldText) {
                $task->is_edited = 1;
            }
            if ($task->save()) {
                $session->set('success', 'Task successfully saved!');
                return $this->redirect(Route::getAppUrl());
            }
        }
        $errors = $task->getValidationErrors();
        return $this->render('edit', compact('task', 'errors'));
    }
    /**
     * logout user
     */
    public function logout()
    {
        $this->checkAdmin();
        $session = Session::getInstance();
        $session->remove('isAdmin');
        return $this->redirect(Route::getAppUrl());
    }
    
    /**
     * check if admin logged in
     */
    protected function checkAdmin()
    {
        $session = Session::getInstance();
        if (!$session->get('isAdmin')) {
            $this->redirect(Route::getAppUrl().'/login');
        }
    }
    
}