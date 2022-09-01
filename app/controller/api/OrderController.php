<?php
namespace app\controller\api;

use think\Request;
use app\models\Order;
use app\models\Business;
use app\models\Blogger;
use app\common\DefineConst;

class OrderController
{
    /**
     * 订单列表
     */
    public function orderList(Request $request) {
        $platform_type = min(array_keys(DefineConst::getPlatfrom())) . ',' . max(array_keys(DefineConst::getPlatfrom()));
        $field_null = [
            'keywork' => '""@length:1,10',                      //关键字
            'date' => '""@length:10,10',                          //时间y-m
            'start_date' => '""@eq:10',
            'end_date' => '""@eq:10',
            'platform_type' => '0@between:' . $platform_type,   //平台
            'blogger_id' => '0@min:1'                           //博主排期进入的订单列表，增加blogger_id条件
        ];
        $check_field = [];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $page = $request->get('page', 1);
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $field = 'o.id, o.order_state, o.platform_type, o.product_name, o.brand_name, o.business_id, o.remarks, o.publish_time, o.blogger_id';
        $page = -1;
        $result = Order::getOrderList($check_field, $field, $page);
        $count = Order::getOrderCount($check_field);
        $price = Order::getPrice($check_field);
        $business_info = Business::getBusinessAllOrName();
        $blogger_info = Blogger::getBloggerAllOrName();
        $list = [];
        foreach ($result as $val) {
            $val['business_name'] = isset($business_info[$val['business_id']]) ? $business_info[$val['business_id']] : '';
            $val['platform_type'] = isset(DefineConst::getPlatfrom()[$val['platform_type']]) ? DefineConst::getPlatfrom()[$val['platform_type']] : '';
            $val['blogger_name'] = isset($blogger_info[$val['blogger_id']]) ? $blogger_info[$val['blogger_id']] : '';
            unset($val['business_name']);
            $list[date('m-d', $val['publish_time'])][] = $val;

        }
        $data = [
            'order_price' => '',
            'examples_price' => $price['examples_price'],
            'cooperation_price' => $price['cooperation_price'],
            'count' => $count, 'list' => $list
        ];
        return getRsp(200, $data);
    }

    /**
     * 创建订单
     */
    public function createOrder(Request $request) {
        //从常量里取选项的区间值
        $platform_type = min(array_keys(DefineConst::getPlatfrom())) . ',' . max(array_keys(DefineConst::getPlatfrom()));
        $check_field = [
            'platform_type'         => '0@between:' . $platform_type,           //平台类型
            'publish_time'          => '""@eq:10',                              //发布时间
            'collection_time'       => '""@eq:10',                              //发布时间
            'brand_name'            => '""@long:24',                            //品牌名称
            'product_name'          => '""@long:24',                            //产品名称
            'order_state'           => '""@long:12',                            //订单状态
            'cooperation_mode'      => '""@long:24',                            //合作性质
            'collection_state'      => '""@long:12',                            //收款状态
            'examples_price'        => '0@between:1,100000000',                 //刊例价
            'discount'              => '0@between:1,100',                       //折扣力度
            'chart_price'           => '0@between:1,100000000',                 //代下星图费
            'collection_account'    => '""@long:24',                            //收款账户
        ];
        $field_null = ['docking_phone' => '""@long:20', 'docking_name' => '""@long:24', 'docking_job' => '""@long:12', 'remarks' => '""@long:1000', 'id' => '0@min:1', 'customer_id' => '0@min:1', 'business_id'=> '0@min:1','blogger_id' => '0@min:1','order_price' => '0@between:1,100000000'];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $string = createRandomString();
        $check_field['order_id'] = date('Ymd') . $string;
        $check_field['user_id'] = $request->user_info['id'];
        if ($check_field['publish_time'] > $check_field['collection_time']) {
            //return getRsp(505, '发布时间不能小于收款时间');
        }
        unset($check_field['blogger']);
        unset($check_field['business']);
        unset($check_field['customer']);
        unset($check_field['blogger_name']);
        unset($check_field['business_name']);
        unset($check_field['customer_name']);
        $result = Order::createOrUpdate($check_field);
        if ($result === true) {
            return getRsp(200);
        }
        return getRsp(500, $result);
    }

    /**
     * 订单详情
     */
    public function orderInfo() {
        /* 博主名称 */
        /* 对接商务 */
        /* 客户名称 */
        $check_field = ['id' => '0@min:1'];
        if (!checkValue($response, $check_field, false)) {
            return $response;
        }
        $list = Order::getOrderInfo($check_field['id']);
        return getRsp(200, $list);
    }
}



