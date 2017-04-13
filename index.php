<?php
$method = $_SERVER['REQUEST_METHOD'];
$uri = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['PHP_SELF'],'index.php'));
require_once 'blacklist.php';
require_once 'function.php';
if ($method == 'GET') {

    // 这里可以进行路由的分发
    if ($uri == '') {
        include 'index.html';
    } else {
        $target = gettarget($uri);
        if ($target) {
            redirect($target);
        } else {
            redirect('/');
        }
    }
} else if ($method == 'POST') {

    // 对于POST请求的处理模式，同样可以先进行路由的解析和分发
    if (!key_exists('target', $_POST)) {
        sendjson(array(
            'status' => 'error',
            'msg' => 'parameter error'
        ));
        status(401);
    }

    $target = $_POST['target'];

    if (banned($target)) {
        sendjson(array(
            'status' => 'error',
            'msg' => 'this link can not be shortened',
        ));
        status(400);
    }

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
