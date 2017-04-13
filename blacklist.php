<?php
function banned($url) {
    $blacklist = array(
      'dwz.cz',
      'url.cn',
      'goo.gl',
      'bit.ly',
      'adf.ly',
      't.co',
      't.cn',
      $_SERVER['HTTP_HOST'],
    );
    foreach ($blacklist as $key => $value) {
        if (stristr($url, $value)) {
            return true;
        }
    }
    return false;
}
