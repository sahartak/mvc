<?php
return [
    'appUrl' => 'http://localhost/mvc',
    'rules' => [
        '/' => 'Home',
        '/login' => 'Admin@login',
        '/logout' => 'Admin@logout',
        '/edit' => 'Admin@edit'
    ]
];