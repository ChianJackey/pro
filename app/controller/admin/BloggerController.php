<?php
namespace app\controller\admin;

use think\Request;
use app\models\Blogger;
use app\models\Order;
use think\facade\Config;
use app\common\DefineConst;

class BloggerController
{
    /**
     * 博主列表
     */
    public function bloggerList(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Blogger/blogger_list', ['token' => $token]);
        } else {
            $platform_type = min(array_keys(DefineConst::getPlatfrom())) . ',' . max(array_keys(DefineConst::getPlatfrom()));
            $field_null = [
                'keywork'      => '""@length:1,24',                  //关键字
                'is_disable'   => '0@in:0,1,-1',                       //状态
                'page'         => '0@min:1'
            ];
            $check_field = [];
            if (!checkValue($response, $check_field, false, $field_null)) {
                return $response;
            }
            $page = $request->post('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $user_id = $request->user_info['id'];
            $check_field['user_id'] = $user_id;
            $field = 'b.nickname, b.name, b.picture, b.id, b.department, b.director, b.is_disable, b.create_time';
            $check_field['is_disable'] = $request->param('is_disable', '-1') ?? $check_field['is_disable'];
            $result = Blogger::getBolggerList($check_field, $page, $field, 'b.create_time', 2);
            $count = Blogger::getBloggerCount($check_field, 2);
            return getRsp(0, $result, $count);
        }
    }

    /**
     * 设置状态
     * @param  blogger_id,is_disable
     * @return response
     */
    public function disable(Request $request) {
        $disable = $request->param('is_disable', 0);
        $id = $request->param('id', 0);
        if (!in_array($disable, [0, 1]) || $id <= 0 || !is_numeric($id)) {
            return getRsp(200);
        }
        $disable = $disable == 1 ? 0 : 1;
        $result = Blogger::createOrUpdate(['id' => $id, 'is_disable' => $disable]);
        return getRsp($result);
    }

    /**
     * 博主详情，新增编辑博主
     * @return [type] [description]
     */
    public function bloggerDetail(Request $request) {
        if ($request->method() == 'GET') {
            $id = $request->get('id','');
            $token = request()->param('token');
            $result = [];
            if ($id !='' && $id > 0) {
                $result = Blogger::getBloggerInfo($id);
            }
            $data = ['token' => $token, 'blogger_info' => $result];
            return view('Blogger/blogger_detail', $data);
        } else {
            $user_id = $request->user_info['id'];
            $check_field = [
                'name' => '""@length:2,24',
                'nickname'=> '""@length:2,24',
                'department' => '""@length:2,12',
                'director'=> '""@length:2,12',
                'picture'=> '""@length:2,200',
                'is_disable' => '0@between:0,1'
            ];
            $field_null = ['id' => '0@min:1'];
            if (!checkValue($response, $check_field, false, $field_null)) {
                return $response;
            }
            $check_field['user_id'] = $user_id;
            $check_field['create_time'] = time();
            $result = Blogger::createOrUpdate($check_field);
            if ($result) {
                return getRsp(200);
            }
            return getRsp(500);
        }
    }


    /**
     * 上传头像
     */
    public function uploadPicture(Request $request) {
        $file = $request->file('file');
        if (!$file) {
            return getRsp(510);
        }
        $config = Config::get('filesystem');
        $date = date('Y-m-d');
        $path = $config['disks']['public']['root'];
        $path .= "/images/picture/$date";
        $result = \think\facade\Filesystem::disk('public')->putFile('/images/picture', $file);
        $result = str_replace('\\', '/', $result);
        if ($result) {
            return getRsp(200, $result);
        }
        return json(['code'=>500]);
    }

    /**
     * 添加博主
     */
    public function createBlogger(Request $request) {
        $user_id = $request->user_info['id'];
        $check_field = [
            'name' => '""@length:2,24',
            'nickname'=> '""@length:2,24',
            'department' => '""@length:2,12',
            'director'=> '""@length:2,12',
            'picture'=> '""@length:2,200',
        ];
        $field_null = ['id' => '0@min:1'];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $check_field['user_id'] = $user_id;
        $check_field['create_time'] = time();
        $result = Blogger::createOrCreate($check_field);
        if ($result) {
            return getRsp(200);
        }
        return getRsp(500);
    }

    //博主数据
    public function bloggerData(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Blogger/blogger_data', ['token' => $token]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $keywork = $request->param('keywork');
            $result = Blogger::getBloggerData($page, $keywork);
            $count = Blogger::getBloggerDataCount($keywork);
            $count = $count[0]['count'];
            $role_id = $request->user_info['role_id'];
            foreach ($result as &$val) {
                if ($role_id == 13) {
                    $val['examples_price'] = '*';
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

    public function bloggerExport(Request $request) {
        $keywork = $request->param('keywork');
        $result = Blogger::getBloggerData(-1, $keywork);
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
        $excel = new Excel('博主数据');
        $excel->writeExcel($result, $header);
    }

    public function del(Request $request) {
        $id = $request->param('id','');
        if (is_numeric($id) && $id > 0) {
            Blogger::del($id);
        }
        return getRsp(200);
    }

}