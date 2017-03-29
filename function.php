<?php
define('PREFIX', 'jsurl_');
function getdb() {
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $dbname = 'test';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};";
    return new PDO($dsn, $user, $pass);
}

function redirect($url) {
    echo "<!DOCTYPE html>
        <html><head></head><body>正在跳转至{$url},该网站和本站半毛钱关系也没有,不管是钓鱼还是黄网都不关我事.

        <script>
        //setTimeout(this.location='{$url}',1000);
        this.location='{$url}';
        </script>
        </body></html>";
    // header("Location: ".$url, true, 302);
}

function sendjson($data) {
    header('Content-type: application/json');
    echo json_encode($data);
}

function status($code) {
    header(':', true, $code);
    exit();
}

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

function btoa($number) {
}

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

function create($target, $s = null) {
    $db = getdb();
    if ($s != null) {
        $st = $db->prepare('SELECT `target` FROM '.PREFIX.'urls WHERE `s` = ?');
        $st->execute([$s]);
        if ($st) {
            $re = $st->fetchAll();
            if (count($re) > 0) {
                return ['success' => false, 'msg' => 'short url exist'];
            }
        }

        $st = $db->query('SELECT MAX(id) FROM '.PREFIX.'urls');
        $re = $st->fetchAll()[0];
        if ($re != null) {
            if (base_convert($s, 36, 10) < $re[0]) {
                return ['success' => false, 'msg' => 'wrong short url'];
            }
        }
    }

    if ($s == null) {
        $st = $db->query('SELECT MAX(id) FROM '.PREFIX.'urls');
        $re = $st->fetchAll()[0];
        if ($re != null) {
            $s = $re[0] + 1;
        } else {
            $s = 1;
        }
    }
    
    $st = $db->prepare('INSERT INTO '.PREFIX.'urls (`target`, `s`) VALUES (?, ?)');
    $re = $st->execute([$target, $s]);
    if ($re) {
        return ['success' => true, 'msg' => 'success', 'target' => $target, 's' => $s];
    } else {
        return ['success' => false, 'msg' => 'unknown error'];
    }
}
