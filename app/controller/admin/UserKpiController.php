<?php
namespace app\controller\admin;

use think\Request;
use app\models\UserKpi;
use app\models\User;
use think\facade\Config;
use app\common\DefineConst;

class UserKpiController
{
    public function userKpi(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            $user_list = User::getUserAll();
            return view('UserKpi/kpi_list', ['user_list' => $user_list, 'token' => $token, 'platfrom' => DefineConst::getPlatfrom()]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $result = UserKpi::getUserKpis($page);
            $count = UserKpi::getUserKpiCount();
            return getRsp(0, $result, $count);
        }
    }

    public function setKpi(Request $request) {
        $id = $request->param('id', 0);
        $kpi_json = $request->param('kpi_json', '');
        $user_name = $request->param('user_name', '');
        $user_id = $request->param('user_id', '');
        $data = ['kpi_json' => $kpi_json, 'user_name' => $user_name, 'user_id' => $user_id];
        if ($id > 0) {
            UserKpi::setUserKpi($id, $data);
        } else {
            $data['create_time'] = time();
            $data['examine_time'] = strtotime(date('Y-m', time()));
            UserKpi::setUserKpi($id, $data);
        }
        return getRsp(200);
    }

    public function modUserKpi(Request $request) {
        return '';
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('UserKpi/user_kpi', ['token' => $token]);
        } else {
            $result = UserKpi::getUserKpiFromMonth();
            $count = count($result);
            return getRsp(0, $result, $count);
        }
    }
}
