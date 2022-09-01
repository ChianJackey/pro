<?php
namespace app\controller\api;

use think\facade\Config;
use app\models\User;
use app\models\Order;
use app\models\Blogger;
use app\models\UserKpi;
use app\models\Rule;
use app\common\Excel;
use think\Request;
use app\common\DefineConst;

class UsersController
{
    /**
     * 用户协议
     */
    public function agreement() {
        $config = Config::get('filesystem');
        $path = $config['disks']['public']['root'];
        $filename = $path . '/agreement.txt';
        $content = file_exists($filename) ? file_get_contents($filename) : '';
        return getRsp(200, $content);
    }

    /**
     * 登录
     * @param string $account 登录账号
     * @param string $pass    密码
     */
    public function login() {
        $check_field = ['account' => '""@length:2,10', 'pass' => '""@length:6,20'];
        if (!checkValue($response, $check_field)) {
            return $response;
        }
        $result = User::login($check_field['account'], $check_field['pass']);
        if (is_numeric($result)) {
            return getRsp(500, $result);
        }
        return getRsp(200, ['token' => $result]);
    }

    /**
     * 今日看板-数据统计
     * @param int $type 统计类型 1收益 2成交金额
     * @param string $date y-m
     */
    public function profitStatistics(Request $request) {
        $field_null = ['type' => '0@between1,2', 'date' => '""@length:7,7'];
        $check_field = [];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $profit_statistics = Order::getProfitStatistics($check_field);//收益
        $day_profit = $profit_statistics['day_profit'];
        $month_profit = $profit_statistics['month_profit'];
        $Platfrom = DefineConst::getPlatfrom();
        $day_money = [];
        $day_order_num = [];
        $month_money = [];
        $month_order_num = [];
        foreach ($day_profit as $val) {
            if (in_array($val['platform_type'], array_keys($Platfrom))) {
                $day_money['field_' . $val['platform_type']] = $val['money'];
                $day_order_num['field_' . $val['platform_type']] = $val['order_num'];
            }
        }
        foreach ($month_profit as $val) {
            if (in_array($val['platform_type'], array_keys($Platfrom))) {
                $month_money['field_' . $val['platform_type']] = $val['money'];
                $month_order_num['field_' . $val['platform_type']] = $val['order_num'];
            }
        }
        return getRsp(200, ['day_money' => $day_money, 'day_order_num' => $day_order_num, 'month_money' => $month_money, 'month_order_num' => $month_order_num]);
    }

    /**
     * 今日看板-博主排名
     * @param int $type 统计类型 1收益 2成交金额
     * @param string $date y-m
     * @param int $page
     */
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
        $blogger_ranking = Blogger::getBloggerRanking($check_field, $page);
        return getRsp(200, $blogger_ranking);
    }

    /**
     * 我的订单
     */
    public function myOrder(Request $request) {
        $start_time = date('Y-m', time());
        $end_time = strtotime("$start_time +1 month -1 day");
        $start_time = strtotime($start_time);
        $user_order = Order::getUserOrder($request->user_info['id'], $start_time, $end_time);
        $user_kpi = UserKpi::getUserKpi($request->user_info['id'], $start_time, $end_time);
        $user_kpi = array_values($user_kpi);
        foreach ($user_order as $key => $val) {
            $user_order[$key]['kpi'] = $user_kpi[$key] ?? '';
        }
        return getRsp(200, $user_order);
    }

    /**
     * 金额排期
     */
    public function moneySchedule(Request $request) {
        $user_id = $request->user_info['id'];
        $result = Order::getMoneySchedule($user_id);
        return getRsp(200, $result);
    }

    /**
     * 导出金额排期表
     */
    public function exportSchedule(Request $request) {
        $user_id = $request->user_info['id'];
        $result = Order::getMoneySchedule($user_id);
        $field = [
            'publish_time',         //发布时间
            'blogger_id',           //博主ID
            'platform_type',        //平台
            'brand_name',           //品牌名称
            'deal_price',           //成交价
            'chart_price',          //下单星图费
            'discount',             //折扣力度
            'examples_price',       //刊例价
            'collection_account',   //收款账户
            'collection_time',      //收款时间
            'remarks',              //备注
            'docking_name'          //接单人员
        ];
        $excel = new Excel('金额排期表');
        $excel->moneyScheduleExcel($result, $field, 3);
    }

    /**
     * 员工管理
     */
    public function workerManage(Request $request) {
        $user_power = User::getUserPower();
        $rule_list = Rule::getRuleAll();
        $list = [];
        foreach ($user_power as $val) {
            if (!isset($list[$val['department_id']])) {
                $list[$val['department_id']] = ['department_name' => $val['department_name'], 'list' => []];
            }
            $list[$val['department_id']]['list'][] = ['user_name' => $val['user_name'], 'rule_name' => $rule_list[$val['role_id']]['name']];
        }
        return getRsp(200, $list);

    }

    public function platfrom() {
        return getRsp(200, DefineConst::getPlatfrom());
    }
}