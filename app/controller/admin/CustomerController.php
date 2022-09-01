<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\models\Customer;

class CustomerController
{
    public function customerList(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Customer/customer_list', ['token' => $token]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $result = Customer::getCustomerList($page);
            $count = Customer::getCustomerListCount();
            return getRsp(0, $result, $count);
        }
    }

    public function customerDetail(Request $request) {
        if ($request->method() == 'POST') {
            $id = $request->param('id', 0);
            $name = $request->param('name', 0);
            $data['name'] = $name;
            Customer::createOrUpdate($id, $data);
            return getRsp(200);
        } else {
            $id = $request->param('id','');
            if (is_numeric($id) && $id > 0) {
                Customer::del($id);
            }
            return getRsp(200);
        }
    }
}
