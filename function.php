<?php
require_once 'config.php';

// 获取PDO方法
function getdb() {
    $host = DB_HOST;
    $port = DB_PORT;
    $user = DB_USER;
    $pass = DB_PASS;
    $dbname = DB_NAME;

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};";
    return new PDO($dsn, $user, $pass);
}

// 重定向跳转
function redirect($url) {
    echo "<!DOCTYPE html>
        <html><head>
        <meta charset=\"utf-8\">
        </head><body>正在跳转至{$url}</br>该网站和本站半毛钱关系也没有,不管是钓鱼还是黄网都不关我事.

        <script>
        //setTimeout(this.location='{$url}',1000);
        this.location='{$url}';
        </script>
        </body></html>";
    // header("Location: ".$url, true, 302);
}

// 将数组转为json发送出去
function sendjson($data) {
    header('Content-type: application/json');
    echo json_encode($data);
}

// 设置http状态码
function status($code) {
    header(':', true, 200); // 因为jq无法处理非200情况所以都返回了200
    exit();
}

// 检查短网址是否存在 （弃用并与gettarget整合
function sexist($s) {
    $db = getdb();
    $st = $db->prepare('SELECT `target` FROM '.PREFIX.'urls WHERE `code` = ? or `s` = ?');
    $st->execute([$s, $s]);
    $re = $st->fetchAll();
    if (count($re) > 0) {
        return true;
    } else {
        return false;
    }
}

// 编码与id的转换方法
function btoa($number) {
}

// 获取原网址，如果短网址不存在返回false
function gettarget($s) {
    $db = getdb();
    $st = $db->prepare('SELECT `target` FROM '.PREFIX.'urls WHERE `s` = ?');
    $st->execute([$s]);
    $re = $st->fetchAll();
    if (count($re) > 0) {
        return $re[0]['target'];
    }

    $st = $db->prepare('SELECT `target` FROM '.PREFIX.'urls WHERE `id` = ?');
    $st->execute([base_convert($s, 36, 10)]);
    if (count($re) > 0) {
        return $re[0]['target'];
    }

    return false;
}

// 创建短网址
function create($target, $s = null) {
    if (!strpos($target, '://')) {
        $target = 'http://' . $target;
    }
    $db = getdb();
    if ($s != null) {
        // 如果给定了后缀则判断后缀是否存在
        $st = $db->prepare('SELECT `target` FROM '.PREFIX.'urls WHERE `s` = ?');
        $st->execute([$s]);
        if ($st) {
            $re = $st->fetchAll();
            if (count($re) > 0) {
                return ['success' => false, 'msg' => 'short url exist'];
            }
        }

        // 后缀不存在判断是否会与之前的默认后缀重合
        $st = $db->query('SELECT MAX(id) FROM '.PREFIX.'urls');
        $re = $st->fetchAll()[0];
        if ($re != null) {
            if (base_convert($s, 36, 10) < $re[0]) {
                return ['success' => false, 'msg' => 'wrong short url'];
            }
        }
    }

    if ($s == null) {
        // 如果没有设置后缀则自动生成
        $st = $db->query('SELECT MAX(id) FROM '.PREFIX.'urls');
        $re = $st->fetchAll()[0];
        if ($re != null) {
            $num = $re[0] + 1;
            while (sexist(base_convert($num, 10, 36))) {
                $num += 100;
            }
            $s = base_convert($num, 10, 36);
        } else {
            $s = 1;
        }
    }

    // 写入数据库
    $st = $db->prepare('INSERT INTO '.PREFIX.'urls (`target`, `s`) VALUES (?, ?)');
    $re = $st->execute([$target, $s]);
    if ($re) {
        return ['success' => true, 'msg' => 'success', 'target' => $target, 's' => $s];
    } else {
        return ['success' => false, 'msg' => 'unknown error'];
    }
}
