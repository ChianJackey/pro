<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Request;
use app\models\User;

class CheckToken
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next, $run_type) {
        $hearer = $request->header();
        $get = $request->get();
        if ($run_type == 1) {
            $response = getRsp(509);
        } else {
            $response = redirect((string) url('/admin-login'));
        }
        if (isset($hearer['authorization']) || isset($get['token']) || isset($hearer['token'])) {
            if((isset($hearer['token']) && !empty($hearer['token'])) || (isset($hearer['authorization']) && !empty($hearer['authorization'])) || (isset($get['token']) && !empty($get['token']))) {
                if (isset($hearer['token'])) {
                    $token = $hearer['token'];
                } else if (isset($hearer['authorization'])) {
                    $token = $hearer['authorization'];
                } else {
                    $token = $get['token'];
                }
                $result = User::checkLogin($token, $run_type);
                if (!$result) {
                    return $response;
                }
                $request->user_info = $result;
            }
            return $next($request);
        }
        return $response;
    }
}
