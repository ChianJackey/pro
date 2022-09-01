<?php
namespace app\controller\admin;

use think\Request;
use app\models\Rule;
use think\facade\Config;
use app\models\Action;

class PowerController
{
    public function rulePower(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Rule/rule_power', ['token' => $token]);
        } else {
            $is_disable = $request->param('is_disable', -1);
            $keywork = $request->param('keywork', '');
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $result = Rule::getRulePower($page, $is_disable, $keywork);
            $count = Rule::getRulePowerCount($is_disable, $keywork);
            return getRsp(0, $result, $count);
        }
    }

    public function ruleList(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Rule/rule_list', ['token' => $token]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $result = Rule::getRuleList($page);
            $count = Rule::getRuleListCount();
            return getRsp(0, $result, $count);
        }
    }

    public function ruleDetail(Request $request) {
        if ($request->method() == 'GET') {
            $id = $request->get('id','');
            $token = request()->param('token');
            $result = [];
            $rule_name = '';
            $result = Action::getParentAction('*');
            if ($id !='' && $id > 0) {
                $rule = Rule::getRuleAll($id);
                if (!empty($rule)) {
                    $power = explode(',', $rule['power']);
                    $rule_name = $rule['name'];
                }
            }
            $list = [];
            foreach ($result as $v) {
                $children = [];
                foreach ($v['child'] as $val) {
                    $children[] = ['title' => $val['name'],'disabled' => true];
                }
                if (isset($power) && in_array($v['ppid'], $power)) {
                    $checked = true;
                } else {
                    $checked = false;
                }
                $list[] = ['title' => $v['name'], 'id' => $v['ppid'], 'checked' => $checked, 'spread' => true, 'children' => array_values($children)];
            }
            return view('Rule/rule_detail', ['token' => $token, 'list' => $list, 'rule_name' => $rule_name, 'id' => $id]);
        } else {
            $rule_id = $request->param('rule_id', '');
            $id = $request->param('id', 0);
            $name = $request->param('name', 0);
            if ($rule_id == '') {
                return getRsp(200);
            }
            $rule_id = rtrim($rule_id, ',');
            $data['power'] = $rule_id;
            $data['id'] = $id;
            $data['name'] = $name;
            Rule::createOrCreate($data);
            return getRsp(200);
        }
    }
}
