<?php
namespace app\controller\admin;

use app\models\User;
use app\common\Excel;
use app\models\Department;
use app\models\Rule;
use think\facade\Config;
use think\Request;

class UserController
{
    public function userData(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('User/user_data', ['token' => $token]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $keywork = $request->param('keywork');
            $result = User::getUserData($page, $keywork);
            $count = User::getUserDataCount($keywork);
            $count = $count[0]['count'];
            $role_id = $request->user_info['role_id'];
            foreach ($result as &$val) {
                if ($role_id == 13) {
                    $val['examples_price'] = '*';
                    $val['discount'] = '*';
                } else {
                    if ($val['examples_price']==null) {
                        $val['examples_price'] = 0;
                    } else {
                        $val['examples_price'] = $val['examples_price'] / 100;
                    }
                }
                if ($role_id == 13) {
                    $val['discount'] = '*';
                } else {
                    if ($val['discount']==null) {
                        $val['discount'] = 0;
                    } else {
                        $val['discount'] = $val['discount'] / 100;
                    }
                }
                if ($role_id == 13) {
                    $val['deal_price'] = '*';
                } else {
                    if ($val['deal_price']==null) {
                        $val['deal_price'] = 0;
                    } else {
                        $val['deal_price'] = $val['deal_price'] / 100;
                    }
                }
                if ($role_id == 13) {
                    $val['chart_price'] = '*';
                } else {
                    if ($val['chart_price']==null) {
                        $val['chart_price'] = 0;
                    } else {
                        $val['chart_price'] = $val['chart_price'] / 100;
                    }
                }
                $val['create_time'] = date('Y-m-d', $val['create_time']);
            }
            return getRsp(0, $result, $count);
        }
    }

    public function userExport(Request $request) {
        $keywork = $request->param('keywork');
        $result = User::getUserData(-1, $keywork);
        foreach ($result as &$val) {
            if ($val['examples_price']==null) {
                $val['examples_price'] = 0;
            } else {
                $val['examples_price'] = $val['examples_price'] / 100;
            }
            if ($val['discount']==null) {
                $val['discount'] = 0;
            } else {
                $val['discount'] = $val['discount'] / 100;
            }
            if ($val['deal_price']==null) {
                $val['deal_price'] = 0;
            } else {
                $val['deal_price'] = $val['deal_price'] / 100;
            }
            if ($val['chart_price']==null) {
                $val['chart_price'] = 0;
            } else {
                $val['chart_price'] = $val['chart_price'] / 100;
            }
            $val['create_time'] = date('Y-m-d', $val['create_time']);
        }
        $header = ['id' => '员工ID', 'name' => '员工姓名', 'create_time' => '创建时间', 'count' => '订单数', 'examples_price' => '收益总金额', 'deal_price' => '成交总金额', 'discount' => '折扣力度总额', 'chart_price' => '代下单星图费总额'];
        $excel = new Excel('员工数据');
        $excel->writeExcel($result, $header);
    }

    public function userDetail(Request $request) {
        if ($request->method() == 'GET') {
            $id = $request->get('id','');
            $token = request()->param('token');
            $result = [];
            if ($id !='' && $id > 0) {
                $result = User::getUserInfo(['id' => $id]);
            }
            $department_list = Department::getDepartmentAllOrName();
            $rule_list = Rule::getRuleAll();
            $data = ['token' => $token, 'user_info' => $result, 'department_list' => $department_list, 'rule_list' => $rule_list];
            return view('User/user_detail', $data);
        } else {
            $check_field = [
                'names' => '""@length:2,24',
                'account'=> '""@length:2,24',
                'department_id' => '0@between:0,20',
                'role_id' => '0@between:0,20',
                'is_disable' => '0@between:0,1'
            ];
            $field_null = [ 'remarks' => '""@length:0,10000','phone'=> '""@length:2,12','email'=> '""@length:2,200','pass' => '""@length:2,16','rep_pass' => '""@length:2,16'];
            if (!checkValue($response, $check_field, false, $field_null)) {
                return $response;
            }
            if ($check_field['rep_pass'] !== $check_field['pass']) {
                return getRsp(511);
            }
            $check_field['name'] = $check_field['names'];
            unset($check_field['names']);
            $id = $request->param('id', 0);
            if ($id <= 0 || !is_numeric($id)) {
                if (!isset($check_field['rep_pass']) || empty($check_field['rep_pass']) || !isset($check_field['pass']) || empty($check_field['pass'])) {
                    return getRsp(500, '密码，重复密码不能为空');
                }
                if (User::chekcUser($check_field['account'])) {
                    return getRsp(512);
                }
                unset($check_field['rep_pass']);
                $check_field['create_time'] = time();
                $check_field['salt'] = createRandomString(12);
                $check_field['pass'] = md5($check_field['salt'] . $check_field['pass']);
                User::createUser($check_field);
                return getRsp(200);
            } else {
                if (!isset($check_field['rep_pass']) || empty($check_field['rep_pass']) || !isset($check_field['pass']) || empty($check_field['pass'])){
                    unset($check_field['pass']);
                } else {
                    $check_field['pass'] = md5($check_field['salt'] . $check_field['pass']);
                }
                unset($check_field['rep_pass']);
                unset($check_field['id']);
                User::updateUser($id, $check_field);
                return getRsp(200);
            }
        }
    }

    public function agreement(Request $request) {
        if ($request->method() == 'GET') {
            $config = Config::get('filesystem');
            $path = $config['disks']['public']['root'];
            $filename = $path . '/agreement.txt';
            $content = file_exists($filename) ? file_get_contents($filename) : '';
            $token = $request->param('token');
            return view('User/agreement', ['data' => $content, 'token' => $token]);
        } else {
            $data = $request->param('content','');
            $config = Config::get('filesystem');
            $path = $config['disks']['public']['root'];
            $filename = $path . '/agreement.txt';
            file_put_contents($filename, $data);
            return getRsp(200);
        }
    }

}