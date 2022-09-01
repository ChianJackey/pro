<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\models\Business;

class BusinessController
{
    public function businessList(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Business/business_list', ['token' => $token]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $result = Business::getBusinessList($page);
            $count = Business::getBusinessListCount();
            return getRsp(0, $result, $count);
        }
    }

    public function businessDetail(Request $request) {
        if ($request->method() == 'POST') {
            $id = $request->param('id', 0);
            $name = $request->param('name', 0);
            $is_disable = $request->param('is_disable', '');
            $data['name'] = $name;
            if ($is_disable != '') {
                $data['is_disable'] = $is_disable;
            }
            Business::createOrUpdate($id, $data);
            return getRsp(200);
        } else {
            $id = $request->param('id','');
            if (is_numeric($id) && $id > 0) {
                Business::del($id);
            }
            return getRsp(200);
        }
    }
}
