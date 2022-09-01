<?php

use \think\facade\Config;

/**
 * 生成响应数据
 * @param int $code 状态码
 * @param array|string $data 错误信息或输出的数据
 */
function getRsp(int $code = 200, $data = '', $count = 0) {
    $error_code = Config::get('error_code');
    $message = isset($error_code[$code]) ? $error_code[$code] : '';
    $data = ['code' => $code, 'message' => $message, 'data' => $data];
    if ($count > 0) {
        $data['count'] = $count;
    }
    return json($data);
}

/**
 * 生成随机字符串
 * @param int $length 字符串长度
 */
function createRandomString($length = 24) {
    $string = '';
    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $char_len = strlen($chars);
    for($i=0; $i < $length; $i++) {
        $loop = mt_rand(0, ($char_len-1));
        $string .= $chars[$loop];
    }
    return $string;
}

/**
 * 调试返回
 */
function R($param) {
    if (is_array($param)) {
        return json($param);
    }
    return json(['data' => $param]);
}

function checkDir($dir) {
    if ($handle = @opendir($dir)) {
    while (($file = readdir($handle)) !== false) {
        if (($file == ".") || ($file == "..")) {
           continue;
        }
        if (is_dir($dir . '/' . $file)) {
           // 递归
           checkDir($dir . '/' . $file);
        } else {
           unlink($dir . '/' . $file);
        }
     }
    @closedir($handle);
    rmdir ($dir);
   }
}
