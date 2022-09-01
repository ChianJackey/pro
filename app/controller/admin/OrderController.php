<?php
namespace app\controller\admin;

use think\Request;
use app\models\Order;
use app\models\Blogger;
use app\models\Customer;
use app\models\Business;
use app\common\DefineConst;
use app\common\Excel;

class OrderController
{
    /**
     * 订单列表
     */
    public function orderList(Request $request) {
        if ($request->method() == 'GET') {
            $token = request()->param('token');
            return view('Order/order_list', ['token' => $token, 'role_id' => $request->user_info['role_id']]);
        } else {
            $field_null = [
                'order_id'     => '""@length:1,30',                         //订单ID
                'product_name' => '""@length:1,7',                          //产品名称
                'order_state'  => '""@length:1,30',                         //订单状态
                'blogger_name' => '""@length:1,30',                         //博主
                'customer_name'=> '""@length:1,30',
                'start_date'   => '""@eq:10',
                'end_date'     => '""@eq:10',
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

            if (in_array($request->user_info['role_id'], [10, 11, 12])) {
                $check_field['user_id'] = $request->user_info['id'];
            }
            $field = 'o.id,o.order_id,o.customer_id,o.blogger_id,o.platform_type,o.product_name,o.order_state,o.deal_price,o.publish_time,o.examples_price,o.chart_price,o.discount,o.business_id,o.customer_id';
            $result = Order::getOrderList($check_field, $field, $page, ['o.publish_time', 'desc']);
            $blogger_info = Blogger::getBloggerAllOrName();
            $customer_info = Customer::getCustomerAllOrName();
            $business_info = Business::getBusinessAllOrName();
            foreach ($result as &$val) {
                if ($request->user_info['role_id'] == 13) {
                    $val['examples_price'] = '*';
                    $val['chart_price'] = '*';
                    $val['deal_price'] = '*';
                }
                $val['blogger_name'] = $blogger_info[$val['blogger_id']] ?? '';
                $val['customer_name'] = $customer_info[$val['customer_id']] ?? '';
                $val['business_name'] = $business_info[$val['business_id']] ?? '';
                $val['platform_type'] = DefineConst::getPlatfrom()[$val['platform_type']] ?? '';
                if ($val['deal_price'] != '*') {
                    $val['deal_price'] = $val['deal_price'] / 100;
                }
                $val['publish_time'] = date('Y-m-d', $val['publish_time']);
            }
            $count = Order::getOrderCount($check_field);
            return getRsp(0, $result, $count);
        }
    }

    /**
     * @tips method get 展示 有ID展示订单详情
     * @tips method post 提交
     */
    public function orderDetail(Request $request) {
        if ($request->method() == 'GET') {
            $id = $request->get('id','');
            $token = request()->param('token');
            $result = [];
            if ($id !='' && $id > 0) {
                $result = Order::getOrderInfo($id);
            } else {
                //新增界面则生成订单ID
                $string = createRandomString();
                $result['order_id'] = date('Ymd') . $string;
            }
            $business_list = Business::getBusinessAllOrName('',0);
            $blogger_list = Blogger::getBloggerAllOrName('', 0);
            $customer_list = Customer::getCustomerAllOrName('', 0);
            $data = [
                'token' => $token,
                'platform_type' => DefineConst::getPlatfrom(),
                'business_list' => $business_list,
                'blogger_list' => $blogger_list,
                'customer_list' => $customer_list,
                'order_info' => $result
            ];
            return view('Order/order_detail', $data);
        } else {
            $platform_type = min(array_keys(DefineConst::getPlatfrom())) . ',' . max(array_keys(DefineConst::getPlatfrom()));
            $check_field = [
                'blogger_id'            => '0@min:1',                               //博主ID
                'platform_type'         => '0@between:' . $platform_type,           //平台类型
                'publish_time'          => '""@eq:10',                              //发布时间
                'brand_name'            => '""@long:24',                            //品牌名称
                'product_name'          => '""@long:24',                            //产品名称
                'order_state'           => '""@long:12',                            //订单状态
                'business_id'           => '0@min:1',                               //对接商务ID
                'cooperation_mode'      => '""@long:24',                            //合作性质
                'customer_id'           => '0@min:1',                               //客户ID
                'collection_time'       => '""@eq:10',                              //收款时间
                'collection_state'      => '""@long:12',                            //收款状态
                'examples_price'        => '0@between:1,100000000',                 //刊例价
                //'discount'              => '0@between:1,100000000',                 //折扣力度
                //'chart_price'           => '0@between:1,100000000',                 //代下星图费
                'collection_account'    => '""@long:24',                            //收款账户
                'deal_price'            => '0@between:1,100000000',                 //合作金额
            ];
            $field_null = ['discount' => '0@between:0,10', 'remarks' => '""@long:1000', 'id' => '0@min:1', 'order_id' => '""long:24', 'docking_phone' => '""@long:20', 'docking_name' => '""@long:24', 'docking_job'  => '""@long:12', 'order_price'=> '0@between:0,100000000', 'chart_price' => '0@between:0,100000000','chart_price' => '0@between:0,100000000'];
            if (!checkValue($response, $check_field, false, $field_null, true)) {
                return $response;
            }
            if ($check_field['publish_time'] > $check_field['collection_time']) {
                //return getRsp(505, '发布时间不能小于收款时间');
            }

            $check_field['user_id'] = $request->user_info['id'];
            $result = Order::createOrUpdate($check_field);
            if ($result === true) {
                return getRsp(200);
            }
            return getRsp(500, $result);
        }
    }

    //['订单ID','客户姓名','博主姓名','发布平台','产品名称','订单状态','成交价']
    public function orderExport(Request $request) {
        $field_null = [
            'order_id'     => '""@length:1,30',                         //订单ID
            'product_name' => '""@length:1,7',                          //产品名称
            'order_state'  => '""@length:1,30',                         //订单状态
            'blogger_name' => '""@length:1,30',                         //博主
            'customer_name'=> '""@length:1,30',
        ];
        $check_field = [];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $field = 'o.id,o.order_id,o.customer_id,o.blogger_id,o.platform_type,o.product_name,o.order_state,o.deal_price,o.publish_time';
        $result = Order::getOrderList($check_field, $field, -1, ['o.publish_time', 'desc']);
        $blogger_info = Blogger::getBloggerAllOrName();
        $customer_info = Customer::getCustomerAllOrName();
        foreach ($result as &$val) {
            $val['blogger_name'] = $blogger_info[$val['blogger_id']] ?? '';
            $val['customer_name'] = $customer_info[$val['customer_id']] ?? '';
            $val['platform_type'] = DefineConst::getPlatfrom()[$val['platform_type']] ?? '';
            $val['deal_price'] = $val['deal_price'] / 100;
            $val['publish_time'] = date('Y-m-d', $val['publish_time']);
        }
        $header = ['id' => '订单ID', 'customer_name' => '客户姓名', 'blogger_name' => '博主姓名', 'platform_type' => '发布平台', 'product_name' => '产品名称', 'order_state' => '订单状态', 'deal_price' => '成交价', 'publish_time' => '发布时间'];
        $excel = new Excel('金额排期表');
        $excel->writeExcel($result, $header);
    }

    public function orderDel(Request $request) {
        $id = $request->param('id','');
        if (is_numeric($id) && $id > 0) {
            Order::del($id);
        }
        return getRsp(200);
    }
}



