
<?php
namespace app\controller\admin;

use app\models\User;
use app\models\UserToken;
use app\models\Rule;
use app\models\Action;
use app\models\Order;
use app\models\Blogger;
use think\Request;
use think\facade\View;
use think\facade\Db;
use app\common\DefineConst;

class IndexController
{
    /**
     * @param string account 登录账号
     * @param string pass 密码
     */
    public function login(Request $request) {
        if ($request->method() == 'GET') {
            return View::fetch('Index/login');
        } else {
            $check_field = ['account' => '""@length:2,10', 'pass' => '""@length:6,20'];
            if (!checkValue($response, $check_field)) {
                return $response;
            }
            $result = User::login($check_field['account'], $check_field['pass'], 2);
            if (is_numeric($result)) {
                return getRsp(500, $result);
            }
            return redirect((string) url('/admin-index', ['token' => $result]));
        }
    }

    /**
     * 外部首页
     */
    public function index(Request $request) {
        $token = $request->get('token', '');
        if ($token === '') {
            return redirect((string) url('/admin-login'));
        }
        $result = UserToken::checkToken($token);
        if (!$result) {
            return redirect((string) url('/admin-login'));
        }
        $role_id = $result['role_id'];
        $role_info = Rule::getRuleAll($role_id);
        $power = $role_info['power'];
        $power_info = Action::getParentAction($power, $role_id);
        return View::fetch('Index/index', ['token' => $token, 'power_info' => $power_info, 'name' => $result['name']]);
    }

    public function getStatistics(Request $request) {
        $token = $request->get('token', '');
        return View::fetch('Index/statistics',['token' => $token, 'platform' => DefineConst::getPlatfrom()]);
    }

    //今日看板-数据统计
    public function todayStatistics(Request $request) {
        $check_field = [];
        $field_null = ['type' => '0@between1,2', 'date' => '""@length:7,7'];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $profit_statistics = Order::getProfitStatistics($check_field, 2);//收益
        $list = [];

        $list[1] = array_column($profit_statistics['month_profit'], 'money');
        array_unshift($list[1], '本月收益');

        $list[2] = array_column($profit_statistics['month_profit'], 'order_num');
        array_unshift($list[2], '本月订单');

        $list[3] = array_column($profit_statistics['day_profit'], 'money');
        array_unshift($list[3], '今日收益');

        $list[4] = array_column($profit_statistics['day_profit'], 'order_num');

        array_unshift($list[4], '今日订单');
        $data = [];
        foreach ($list as $key => $v) {
            foreach ($v as $k => $val) {
                if ($request->user_info['role_id'] == 13) {
                    $data[$key]['v'.$k] = '*';
                } else {
                    $data[$key]['v'.$k] = $val;
                }
            }
        }
        return getRsp(0, $data, 5);
    }

    public function bloggerRanking(Request $request) {
        $field_null = ['type' => '0@between1,2', 'date' => '""@length:7,7', 'page' => '0@min:1'];
        $check_field = [];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $page = $check_field['page'];
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $check_field['role_id'] = $request->user_info['role_id'];
        $blogger_ranking = Blogger::getBloggerRanking($check_field, $page);
        $count = Blogger::getBloggerRankingCount($check_field);
        return getRsp(0, $blogger_ranking, $count);
    }

    public function platfrom(Request $request) {
        $method = $request->method();
        if ($method == 'GET') {
            $token = request()->param('token');
            $type = $request->param('type', '');
            if ($type == '') {
                return view('Index/platfrom', ['token' => $token]);
            } else {
                $data =  Db::name('platfrom_type')->order('sort asc')->select()->toArray();
                return getRsp(0, $data, count($data));
            }
        } else if ($method == 'POST') {
            $name = request()->param('name');
            $sort = request()->param('sort');
            $id = request()->param('id', 0);
            if ($id == 0) {
                Db::name('platfrom_type
                ')->insert(['name' => $name, 'sort' => $sort]);
            } else {
                Db::name('platfrom_type
                ')->where(['id' => $id])->update(['name' => $name, 'sort' => $sort]);
            }

        } else if ($method == 'DELETE') {
            $id = request()->param('id');
            Db::name('platfrom_type')->where(['id' => $id])->delete();
        }
    }
}