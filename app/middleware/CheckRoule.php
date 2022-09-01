<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\View;

class CheckRoule
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next) {
        View::assign('roule', $request->user_info['role_id']);
        return $next($request);
    }
}
