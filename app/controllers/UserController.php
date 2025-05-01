<?php

namespace app\controllers;

use Flight;

class UserController {

    public function __construct() {
    }

    public function showLogin() {
        Flight::render('login');
    }

    public function authenticate() {
        $generaliserModel = Flight::generaliserModel();
        $result = $generaliserModel->checkLogin('user', ['user_id','department_id'], 'POST', ['user_id', 'name', 'department_id']);
        if ($result['success']) {
            $_SESSION['department_id'] = $result['data']['department_id'];
            if ($result['data']['department_id'] == 1) {
                Flight:: redirect('admin');
            } else if($result['data']['department_id'] == 5){
                Flight:: redirect('office');
            } else {
                Flight:: redirect('home');
            }
        } else {
            Flight::render('login', ['error' => $result['message']]);
        }
    }

    public function logout() {
        session_destroy();
        Flight::redirect('.');
    }
}