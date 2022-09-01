<?php
/* 验证相关文件 */
use think\facade\Request;
use \think\facade\Config;
define('PARAM_ERR_CODE', 505);
define('CHECK_RULE_ERR', 501);

/**
 * 验证请求参数类型
 * @param null $response 响应
 * @param array $data 验证的参数 [参数 => 允许通过的类型@验证的名称:规则]
 * @param bool $check_null 参数是否能为空
 * @param array $field_null 允许为空字段，为空时不验证
 * @param bool $is_tips true为明文提示
 * @param string $variable 允许参数跟该变量相同类型时通过
 * @param string|int $value 请求输入的变量
 * @example [password => '""@length:6,20']
 */
function checkValue(&$response, &$data, $check_null = false, $field_null = [], $is_tips = false) {
    $request_data = Request::param();
    $data = array_merge($data, $field_null);
    foreach ($data as $key => $val) {
        $tips = $key;
        if ($is_tips) {
            $error_data = Config::get('error_data');
            if (isset($error_data[$key])) {
                $tips = $error_data[$key];
            }
        }
        $value = isset($request_data[$key]) ? $request_data[$key] : '';
        $value_len = is_array($value)?count($value):strlen($value);
        if ($value_len == 0 && $check_null === false && !isset($field_null[$key])) {
            $response = getRsp(PARAM_ERR_CODE, $tips . ' 不能为空 !');
            return false;
        }
        $variable = substr($val, 0, stripos($val, '@'));
        $val = substr($val, stripos($val, '@') + 1);
        //该参数有值，或者不在非空数组里则验证,否则给默认值
        if (!isset($field_null[$key]) || $value_len > 0) {
            if (is_numeric($variable) && $variable !== '') {
                if ((strstr($variable, '.') && !strstr($value, '.')) || (!is_numeric($value) && strstr($variable, '.'))) {
                    $response = getRsp(PARAM_ERR_CODE, $tips . ' 不是浮点 !');
                    return false;
                } else if (!is_numeric($value)){
                    $response = getRsp(PARAM_ERR_CODE, $tips . ' 不是数字 !');
                    return false;
                }
            }
            $result = checkIllegal($val, $value, $key, $tips);
            if ($result === false) {
                $response = $val;
                return false;
            }
        } else {
            $value = is_numeric($variable) ? 0 : '';
        }
        $data[$key] = $value;
    }
    return true;
}

/**
 * 验证参数是否合法
 * @param string $rule_val 验证的名称与规则  length:5,10
 * @param string|array $check_value 验证的值
 * @param string $param_name 参数名称 包含time字段额外进行时间格式验证
 * @example length:2,10|max:10
 * @example max:最大值,min:最小值,length:长度区间,between:数值大小区间,long:字符串最大长度,eq:字符串长度,in:参数在中间
 */
function checkIllegal(&$rule_val, &$check_value, $param_name, $tips) {
    foreach (explode('|', $rule_val) as $rule_arr) {
        $rule_arr = explode(':', $rule_arr);
        if ($rule_arr[0] == 'length') {
            $value = explode(',', $rule_arr[1]);
            if (count($value) != 2) {
                $rule_val = getRsp(CHECK_RULE_ERR, 'length必须是逗号相隔的区间');
                return false;
            }
            if (strlen($check_value) < $value[0] || strlen($check_value) > $value[1]) {
                $rule_val = getRsp(PARAM_ERR_CODE, $tips . "长度必须在$value[0]和$value[1]之间");
                return false;
            }
        }
        if ($rule_arr[0] == 'max') {
            if ($check_value > $rule_arr[1]) {
                $rule_val = getRsp(PARAM_ERR_CODE, $tips . "最大不能超过$rule_arr[1]");
                return false;
            }
        }
        if ($rule_arr[0] == 'min') {
            if ($check_value < $rule_arr[1]) {
                $rule_val = getRsp(PARAM_ERR_CODE, $tips . "最小不能超过$rule_arr[1]");
                return false;
            }
        }
        if ($rule_arr[0] == 'eq') {
            if (strlen($check_value) != $rule_arr[1]) {
                $rule_val = getRsp(PARAM_ERR_CODE, $tips . "字符长度应该为$rule_arr[1]");
                return false;
            }
        }
        if ($rule_arr[0] == 'between') {
            $value = explode(',', $rule_arr[1]);
            if (count($value) != 2) {
                $rule_val = getRsp(CHECK_RULE_ERR, $tips . '必须是逗号相隔的区间');
                return false;
            }
            if ($check_value < $value[0] || $check_value > $value[1]) {
                $rule_val = getRsp(PARAM_ERR_CODE, $tips . "大小必须在$value[0]和$value[1]之间");
                return false;
            }
        }
        if (strstr($param_name, 'time')) {
            $result = strtotime($check_value);
            if ($result == false) {
                $rule_val = getRsp(PARAM_ERR_CODE, "$param_name 时间格式错误");
                return false;
            }
            $check_value = $result;
        }
    }
    return true;
}



