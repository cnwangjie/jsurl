<?php
$method = $_SERVER['REQUEST_METHOD'];
$uri = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['PHP_SELF'],'index.php'));
require_once 'function.php';
if ($method == 'GET') {
    $target = gettarget($uri);
    if ($target) {
        redirect($target);
    } else {
        // redirect('/');
        include 'index.html';
    }
} else if ($method == 'POST') {
    if (!key_exists('target', $_POST)) {
        sendjson(array(
            'status' => 'error',
            'msg' => 'parameter error'
        ));
        status(401);
    }

    $target = $_POST['target'];

    if (key_exists('s', $_POST)) {
        $s = $_POST['s'];

        if (preg_match('/^[A-Za-z0-9]+$/', $s) == 0) {
            sendjson(array(
                'status' => 'error',
                'msg' => 'short link format error',
            ));
            status(400);
        }

        if (gettarget($s)) {
            sendjson(array(
                'status' => 'error',
                'msg' => 'short link is exist',
            ));
            status(403);
        }

        $result = create($target, $s);

        if ($result['success']) {
            sendjson(array(
                'status' => 'success',
                'msg' => 'create success',
                'target' => $target,
                's' => $s
            ));
            status(200);
        } else {
            sendjson(array(
                'status' => 'error',
                'msg' => 'server error',
            ));
            status(500);
        }
    }

    $result = create($target, null);

    if ($result['success']) {
        sendjson(array(
            'status' => 'success',
            'msg' => 'create success',
            'target' => $result['target'],
            's' => $result['s'],
        ));
        status(200);
    } else {
        sendjson(array(
            'status' => 'error',
            'msg' => 'server error',
        ));
        status(500);
    }
} else {
    include 'index.html';
}
