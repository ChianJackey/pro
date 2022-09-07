<?php
namespace app\controller;

use app\BaseController;
use think\Request;
use think\facade\View;
use app\models\User;
use app\models\UserToken;
use think\facade\Config;
use app\models\MonitorLink;
use app\models\RedirectLink;
use app\models\RedorectRecord;

class Index extends BaseController
{
    public function login(Request $request){
        if($request->method() == 'GET'){
            return View::fetch('Index/login');
        }else{
            $checkField = ['account' => '""@length:2,10', 'pass' => '""@length:6,20'];
            if(!checkValue($response, $checkField)){
                return $response;
            }
            $result = User::login($checkField['account'], $checkField['pass']);
            return redirect((string) url('index', ['token' => $result]));
        }
    }

    public function index(Request $request){
        $token = $request->get('token', '');
        if($token === ''){
            return redirect((string) url('/login'));
        }
        $result = UserToken::checkToken($token);
        if(!$result){
            return redirect((string) url('/login'));
        }
        $power = Config::get('rule');
        return View::fetch('Index/index', ['token' => $token, 'power' => $power]);
    }

    public function monitorLink(Request $request){
        if($request->method() == 'GET'){
            $fieldNull = [
                'page'         => '0@min:1'
            ];
            $checkField = [];
            if (!checkValue($response, $checkField, false, $fieldNull)) {
                return $response;
            }
            $token = request()->param('token');
            return View::fetch('Index/monitor_link', ['token' => $token]);
        }else{
            $page = $request->post('page', 1);
            if(!is_numeric($page) || $page <= 0){
                $page = 1;
            }
            $result = MonitorLink::getMonitorLinkList($page);
            $count = MonitorLink::getMonitorLinkCount();
            return getRsp(0, $result, $count);
        }
    }

    public function monitorDetail(Request $request){
        if($request->method() == 'GET'){
            $checkField = [
                'id'         => '0@min:1'
            ];
            if(!checkValue($response, $checkField)){
                return $response;
            }
            $monitorInfo = MonitorLink::getMonitorLinkDetail($checkField['id']);
            $redirectList = RedirectLink::getRedirectLinkList($checkField['id']);
            return View::fetch('Index/monitor_detail', ['monitor_info' => $monitorInfo, 'redirect_list' => $redirectList]);
        }
    }

    public function deleMonitor(Request $request){
        if($request->method() == 'POST'){
            $checkField = [
                'id'         => '0@min:1'
            ];
            if(!checkValue($response, $checkField)){
                return $response;
            }
            MonitorLink::deleMonitorLink($checkField['id']);
        }
        return getRsp(200);
    }

    public function addRedirect(Request $request){
        if($request->method() == 'POST'){
            $checkField = [
                'monitor_id'           => '0@min:1',
                'redirect_link'     => '""@long:800',
                'num'               => '0@between:0,1000000',
                'rank'              => '0@between:0,1000000'
            ];
            if(!checkValue($response, $checkField)){
                return $response;
            }
            $checkField['create_time'] = time();
            $id = RedirectLink::addRedirectLink($checkField);
            return getRsp(200, ['id' => $id]);
        }
    }

    public function deleRedirect(Request $request){
        if($request->method() == 'POST'){
            $checkField = [
                'id'         => '0@min:1'
            ];
            if(!checkValue($response, $checkField)){
                return $response;
            }
            RedirectLink::deleRedirectLink($checkField['id']);
        }
        return getRsp(200);
    }

    public function saveRedirect(Request $request){
        if($request->method() == 'POST'){
            $redirectLink = $request->post('redirect_link', []);
            $id = $request->post('monitor_id', []);
            $rank = $request->post('rank', []);
            $num = $request->post('num', []);
            $id = $request->post('id');
            $name = $request->post('name');
            $remark = $request->post('remark');
            if(!empty($num)){
                $data = [];
                foreach($num as $key => $val){
                    $temp = [
                        'id' => isset($id[$key]) ? $id[$key] : 0,
                        'redirect_link' => isset($redirectLink[$key]) ? $redirectLink[$key] : 0,
                        'num' => isset($num[$key]) ? $num[$key] : 0,
                        'rank' => isset($rank[$key]) ? $rank[$key] : 0,
                    ];
                    $data[] = $temp;
                }
                RedirectLink::saveRedirectLink($data);
            }
            MonitorLink::saveMonitorLink($id, ['name' => $name, 'remark' => $remark]);
        }
        return getRsp(200);
    }

    public function addMonitor(Request $request){
        if($request->method() == 'POST'){
            $checkField = [
                'redirect_link'     => '""@long:800',
                'num'               => '0@between:0,1000000',
                'rank'              => '0@between:0,1000000',
                'name'              => '""@long:48',
                'remark'            => '""@long:240',
            ];
            if(!checkValue($response, $checkField)){
                return $response;
            }
            $flag = createMonitorFlag();
            $monitorLink = [
                'flag' => $flag,
                'monitor_link' => createUrl($flag),
                'name' => $checkField['name'],
                'remark' => $checkField['remark'],
                'create_time' => time()
            ];
            $id = MonitorLink::addMonitorLink($monitorLink);
            $redirectLink = [
                'monitor_id' => $id,
                'redirect_link' => $checkField['redirect_link'],
                'num' => $checkField['num'],
                'rank' => $checkField['rank'],
                'create_time' => time()
            ];
            RedirectLink::addRedirectLink($redirectLink);
        }
        return getRsp(200);
    }

    public function redorectRecord(Request $request){
        if($request->method() == 'GET'){
            $startDate = date("Y-m-d", strtotime("-7 day"));
            $endDate = date('Y-m-d', time());
            return View::fetch('Index/redorect_record', ['start' => $startDate, 'end' => $endDate]);
        }else{
            $fieldNull = [
                'end_date'      => '""@eq:10',
                'start_date'    => '""@eq:10',
                'redirect_link' => '""@length:1,300',
                'page'          => '1@min:1'
            ];
            $checkField = [];
            if (!checkValue($response, $checkField, false, $fieldNull)) {
                return $response;
            }
            $where = [];
            if(!empty($checkField['start_date']) && !empty($checkField['end_date'])){
                $where[] = ['date', 'between', [$checkField['start_date'], $checkField['end_date']]];
            }
            if(!empty($checkField['redirect_link'])){
                $where[] = ['redirect_link', 'like', [$checkField['redirect_link'] . '%']];
            }
            $result = RedorectRecord::getRedorectRecord($where,$checkField['page']);
            $count = RedorectRecord::getRedorectRecordCount($where);
            return getRsp(0, $result, $count);;
        }
    }

    public function skip(Request $request){
        $flag = $request->route('flag','');
        $monitorId = MonitorLink::getMonitorLinkId($flag);
        $linkList = RedirectLink::getRedirectLink($monitorId);
        $linkInfo = [];
        //没有配置跳转链接取最后一条的链接
        if(!$linkList){
            $linkInfo = RedirectLink::getLastRedirectInfo();
        }else{
            $temp = 0;
            foreach($linkList as $key => &$val){
                if($val['redirect_num'] >= $val['num'] && 0 != $val['num']){
                    unset($linkList[$key]);
                    continue;
                }
                $temp += $val['rank'];
                $val['rank'] = $temp;
            }
            $rank = mt_rand(1,max(array_column($linkList,'rank')));
            foreach($linkList as $v){
                if($v['rank'] >= $rank){
                    $linkInfo = $v;
                    break;
                }
            }
        }
        RedorectRecord::increasingNum($linkInfo['id']);
        $url = $linkInfo['redirect_link'];
        if(false === strpos($url, 'http://') && false === strpos($url, 'https://')){
            $url = 'http://' . $url;
        }
        redirect($url)->send();
    }
}
