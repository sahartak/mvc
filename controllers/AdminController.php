<?php


namespace app\controllers;

use app\framework\classes\Controller;
use app\framework\classes\Session;
use app\models\Task;
use app\framework\classes\Route;

class AdminController extends Controller
{

    protected function checkAdmin()
    {
        $session = Session::getInstance();
        if (!$session->get('isAdmin')) {
            $this->redirect('/login');
        }
    }

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
                if ($username == 'admin' && $password == '123') {
                    $session->set('isAdmin', true);
                    return $this->redirect(Route::getAppUrl());
                } else {
                    $data['errors']['wrongCredentials'] = 'wrong credentials';
                }
            }
        }

        return $this->render('login', $data);

     }

    /**
     * logout user
     */
    public function logout()
    {
        $session = Session::getInstance();
        $session->remove('isAdmin');
        return $this->redirect(Route::getAppUrl());
    }

}