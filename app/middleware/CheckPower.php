<?php
declare (strict_types = 1);

namespace app\middleware;

use app\models\Action;

class CheckPower
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next) {
        $role_id = $request->user_info['role_id'];
        if ($role_id == 1 || $role_id == 13) {
            return $next($request);
        }
        $action_info = Action::getActionFromRole($role_id);
        $action_info[] = 'admin-getstatistics';
        $action_info[] = 'admin-today-statistics';
        $action_info[] = 'admin-blogger-ranking';
        $route = $request->rule()->getRule();
        if (!empty($action_info) && !in_array($route, $action_info)) {
            return response('没有权限');
        }
        return $next($request);
    }
}
