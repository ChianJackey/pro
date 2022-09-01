<?php
namespace app\models;

use think\Model;
use app\models\Business;
use app\models\Customer;
use app\models\Blogger;
use app\common\DefineConst;
use think\facade\Db;

/**
 * @mixin \think\Model
 */
class Order extends Model
{
    /**
     * 新增或编辑
     * 有ID编辑，无ID新增
     */
    public static function createOrUpdate($data) {
        //$data['order_price'] = $data['order_price'] * 100;
        $data['chart_price'] = $data['chart_price'] * 100;
        $data['examples_price'] = $data['examples_price'] * 100;
        $res = $data['examples_price'] * $data['discount'] / 100;
        $data['deal_price'] = $data['examples_price'] - $data['chart_price'] - $res;
        $data['examples_price'] - $data['chart_price'];
        if (isset($data['id']) && !empty($data['id']) && $data['id'] > 0) {
            $result = Order::find($data['id']);
            if ($result === null) {
                return false;
            }
            try {
                unset($data['order_id']);
                unset($data['user_id']);
                unset($data['create_time']);
                Order::update($data);
                return true;
            } catch (\Throwable $th) {
                return $th->getMessage();
            }
        } else {
            $result = Order::create($data);
            return is_int($result->id) || is_numeric($result->id) ? true : false;
        }
    }

    /**
     * @param string $keywork 博主 商务 产品 品牌 客户名称
     * @param string $date 日期 为空当天日期
     * @param int $platform_type 平台
     * @param int $blogger_id 博主
     * @param int $type type=1:时间区间一个月，type=2:时间区间一天
     * @param array $order [order_field, order_rule]
     * @param string $group group_field
     * @param array $join [table_name, ext]
     */
    public static function getOrderList($param, $field = '*', $page, $order = [], $group = '', $join = []) {
        if (empty($order)) {
            $order = ['o.publish_time', 'asc'];
        }
        $obj = self::getOrderObj($param, $group, $join);
        if ($page != -1) {
            $obj = $obj->limit(15)
                ->page($page);
        }
        $result = $obj->order($order[0], $order[1])
            ->field($field)
            ->select()
            ->toArray();
        return $result;
    }

    public static function getOrderCount($param) {
        $obj = self::getOrderObj($param);
        return $obj->count();
    }

    public static function getPrice($param) {
        $obj = self::getOrderObj($param);
        $result = $obj->field('examples_price, discount, chart_price, order_price')->select()->toArray();
        if (empty($result)) {
            return ['order_price' => 0, 'examples_price' => 0, 'cooperation_price' => 0];
        }
        $order_price = 0;
        $examples_price = 0;
        $cooperation_price = 0;
        foreach ($result as $val) {
            $order_price += $val['order_price'];//订单金额
            $examples_price += $val['examples_price'];//刊例价
            $money = $val['examples_price'];
            $return_money = $money * ($val['discount'] / 100);//返点金额
            $cooperation_price += $money - $val['chart_price'] - $return_money;//合作金额
        }
        $order_price = $order_price / 100;
        $examples_price = $examples_price / 100;
        $cooperation_price = $cooperation_price / 100;
        return ['order_price' => $order_price, 'examples_price' => $examples_price, 'cooperation_price' => $cooperation_price];
    }

    public static function getOrderObj($param, $group = '', $join = []) {
        $where = [];
        //平台筛选
        if (isset($param['platform_type']) && !empty($param['platform_type']) && $param['platform_type'] > 0) {
            $where[] = ['o.platform_type', '=', $param['platform_type']];
        }
        //时间筛选
        if (isset($param['start_date']) && !empty($param['start_date'])) {
            $where[] = ['o.publish_time', '>=', strtotime($param['start_date'])];
        }
        if (isset($param['end_date']) && !empty($param['end_date'])) {
            $where[] = ['o.publish_time', '<=', strtotime($param['end_date']) + 86399];
        }
        //博主排期进入的订单列表或后台博主条件筛选，增加blogger_id条件
        if (isset($param['blogger_id']) && !empty($param['blogger_id']) && $param['blogger_id'] > 0) {
            $where[] = ['o.blogger_id', '=', $param['blogger_id']];
        }
        //博主姓名
        if (isset($param['blogger_name']) && !empty($param['blogger_name'])) {
            $blogger_id = Blogger::getBloggerIdByName($param['blogger_name']);
            $where[] = ['o.blogger_id', 'in', implode(',', $blogger_id)];
        }
        //客户姓名
        if (isset($param['customer_name']) && !empty($param['customer_name'])) {
            $customer_id = Customer::getCustomerIdByName($param['customer_name']);
            $where[] = ['o.customer_id', 'in', implode(',', $customer_id)];
        }
        //订单ID
        if (isset($param['order_id']) && !empty($param['order_id'])) {
            $where[] = ['o.order_id', '=', $param['order_id']];
        }
        //产品名称
        if (isset($param['product_name']) && !empty($param['product_name'])) {
            $where[] = ['o.product_name', '=', $param['product_name']];
        }
        //订单状态
        if (isset($param['order_state']) && !empty($param['order_state'])) {
            $where[] = ['o.order_state', '=', $param['order_state']];
        }
        //查询自己的
        if (isset($param['user_id']) && !empty($param['user_id'])) {
            $where[] = ['o.user_id', '=', $param['user_id']];
        }
        //博主 商务 产品 品牌 客户名称查询
        $or = [];
        if (isset($param['keywork']) && !empty($param['keywork'])) {
            $param['keywork'] = trim($param['keywork'], ' ');
            $customer_id = Customer::getCustomerIdByName($param['keywork']);
            $business_id = Business::getBusinessIdByName($param['keywork']);
            $blogger_id = Blogger::getBloggerIdByName($param['keywork']);
            !empty($business_id) && $or[] = ['o.business_id' ,'in', implode(',', $business_id)];
            !empty($customer_id) && $or[] = ['o.customer_id' ,'in', implode(',', $customer_id)];
            !empty($blogger_id) && $or[] = ['o.blogger_id' ,'in', implode(',', $blogger_id)];
            $or[] = ['o.brand_name' ,'=', $param['keywork']];
            $or[] = ['o.product_name' ,'=', $param['keywork']];
        }
        $obj = Db::name('order')
            ->alias('o')
            ->where($where);
        if (isset($or) && !empty($or)) {
            $obj = $obj->where(function($query) use($or){
                $query->whereOr($or);
            });
        }
        if (!empty($join)) {
            $obj = $obj->join($join[0], $join[1]);
        }
        if (!empty($group)) {
            $obj = $obj->group($group);
        }
        return $obj;
    }

    public static function getOrderInfo($id) {
        $result = Order::where(['id' => $id])->find()->toArray();
        if ($result == null) {
            return [];
        }
        //博主名字
        $blogger_info = Blogger::getBloggerInfo($result['blogger_id'], 'id, name, is_disable');
        if (empty($blogger_info)) {
            $blogger_name = '';
        } else {
            $blogger_name = $blogger_info['name'];
        }
        //商务名字
        $business_info = Business::getBusinessInfo($result['business_id'], 'id, name, is_disable');
        if (empty($business_info)) {
            $business_name = '';
        } else {
            $business_name = $business_info['name'];
        }
        //客户名字
        $customer_info = Customer::getCustomerInfo($result['customer_id'], 'id, name, is_disable');
        if (empty($customer_info)) {
            $customer_name = '';
        } else {
            $customer_name = $customer_info['name'];
        }
        $result['blogger'] = $blogger_name;                                                 //博主
        $result['business'] = $business_name;                                               //商务
        $result['customer'] = $customer_name;                                               //客户
        $result['platform_type'] = DefineConst::getPlatfrom()[$result['platform_type']];    //平台
        $result['publish_time'] = date('Y-m-d', $result['publish_time']);                   //发布时间
        $result['collection_time'] = date('Y-m-d', $result['collection_time']);             //收款时间
        $result['examples_price'] = $result['examples_price'] / 100;                        //刊例价
        $result['discount'] = $result['discount'] / 100;                                    //折扣
        $result['deal_price'] = $result['deal_price'] / 100;                                //成交价
        $result['chart_price'] = $result['chart_price'] / 100;                              //代下星图费
        $result['order_price'] = $result['order_price'] / 100;                            //返点金额
        return $result;
    }

    /**
     * 今日看板-收益统计
     * @param int $type 统计类型 1收益 2成交金额
     * @param string $date y-m
     */
    public static function getProfitStatistics($param) {
        if (isset($param['type']) && !empty($param['type']) && $param['type'] == 2) {
            $field = 'deal_price as money, platform_type, discount, chart_price';
        } else {
            $field = 'examples_price as money, platform_type, discount, chart_price';
        }
        //时间筛选
        if (isset($param['date']) && !empty($param['date'])) {
            $month_start_time = date('Y-m', strtotime($param['date']));
            $day_start_time = date('Y-m-d', strtotime($param['date']));
        } else {
            $month_start_time = date('Y-m', time());
            $day_start_time = date('Y-m-d', time());
        }
        $month_end_time = date('Y-m-d', strtotime("$month_start_time +1 month -1 day"));
        $day_end_time = $day_start_time . ' 23:59:59';
        $month_start_time = strtotime($month_start_time);
        $month_end_time = strtotime($month_end_time);
        $day_start_time = strtotime($day_start_time);
        $day_end_time = strtotime($day_end_time);
        $month_where[] = ['publish_time', '>=', $month_start_time];
        $month_where[] = ['publish_time', '<=', $month_end_time + 86399];
        $day_where[] = ['publish_time', '>=', $day_start_time];
        $day_where[] = ['publish_time', '<=', $day_end_time];
        $list = [];
        $platfrom = DefineConst::getPlatfrom();

        foreach ($platfrom as $k => $v) {
            $list[$k] = ['money' => 0, 'order_num' =>0, 'cooperation_money' => 0, 'platform_type' => $k];
        }

        $list1 = $list2 = $list;
        $month_profit = Order::where($month_where)->field($field)->select()->toArray();

        $order_nums = 0;
        $moneys = 0;
        $cooperation_moneys = 0;

        foreach ($month_profit as $val) {
            if (isset($list1[$val['platform_type']])) {
                $money = $val['money'] / 100;//刊例价
                $return_money = $money * ($val['discount'] / 100); //返点金额
                $chart_price = $val['chart_price'] / 100;//代下单金额
                $cooperation_money = $money - $return_money - $chart_price;//合作金额
                $list1[$val['platform_type']]['money'] += $money;
                $list1[$val['platform_type']]['order_num'] += 1;
                $list1[$val['platform_type']]['cooperation_money'] += $cooperation_money;
            }
            $order_nums += 1;
            $moneys = $moneys + $money;
            $cooperation_moneys = $cooperation_moneys + $cooperation_money;
        }
        $list1[] = ['money' => $moneys, 'order_num' => $order_nums, 'cooperation_money' => $cooperation_moneys, 'platform_type' => '总计'];

        $order_nums = 0;
        $moneys = 0;
        $cooperation_moneys = 0;
        $day_profit = Order::where($day_where)->field($field)->select()->toArray();
        foreach ($day_profit as $val) {
            if (isset($list2[$val['platform_type']])) {
                $money = $list2[$val['platform_type']]['money'] / 100;//刊例价
                $return_money = $money * ($val['discount'] / 100); //返点金额
                $chart_price = $val['chart_price'] / 100;//代下单金额
                $cooperation_money = $money - $return_money - $chart_price;//合作金额
                $list2[$val['platform_type']]['money'] += $money;
                $list2[$val['platform_type']]['order_num'] += 1;
                $list2[$val['platform_type']]['cooperation_money'] += $cooperation_money;
            }
            $order_nums += 1;
            $moneys = $moneys + $money;
            $cooperation_moneys = $cooperation_moneys + $cooperation_money;
        }
        $list2[] = ['money' => $moneys, 'order_num' => $order_nums, 'cooperation_money' => $cooperation_moneys, 'platform_type' => '总计'];
        return ['day_profit' => array_values($list2), 'month_profit' => array_values($list1)];
    }

    /**
     * 用户订单
     */
    public static function getUserOrder($user_id, $start_time, $end_time) {
        $user_order = Order::field('sum(examples_price) as money, count(*) as order_num, platform_type')
            ->group('platform_type')
            ->where([['user_id', '=', $user_id], ['publish_time', '>=', $start_time], ['publish_time', '<=', $end_time]])
            ->select()
            ->toArray();
        $user_order = array_column($user_order, null, 'platform_type');
        $order_num = 0;
        $money = 0;
        foreach (DefineConst::getPlatfrom() as $platform_type => $name) {
            if (isset($user_order[$platform_type]) && !empty($user_order[$platform_type])) {
                $order_num += $user_order[$platform_type]['order_num'];
                $user_order[$platform_type]['money'] = $user_order[$platform_type]['money'] / 100;
                $money += $user_order[$platform_type]['money'];
                $user_order[$platform_type]['platform_type'] = $name;
            } else {
                $user_order[$platform_type] = ['money' => 0, 'order_num' => 0, 'platform_type' => $name];
            }
        }
        $user_order[] = ['money' => $money, 'order_num' => $order_num, 'platform_type' => '总计'];
        return array_values($user_order);
    }

    /**
     * 金额排期
     */
    public static function getMoneySchedule($user_id) {
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
        $result = Order::where(['user_id' => 1])
            ->field($field)
            ->order('deal_price', 'desc')
            ->select()
            ->toArray();
        if (empty($result)) {
            return [];
        }
        $blogger_list = Blogger::getBloggerAllOrName();
        foreach ($result as &$val) {
            $val['publish_time'] = date('m月d日', $val['publish_time']);
            $val['blogger_name'] = $blogger_list[$val['blogger_id']];
            $val['platform_type'] = DefineConst::getPlatfrom()[$val['platform_type']];
            $val['deal_price'] = $val['deal_price'] / 100;
            $val['chart_price'] = $val['chart_price'] / 100;
            $val['examples_price'] = $val['examples_price'] / 100;
            $val['collection_time'] = date('m月d日', $val['collection_time']);
        }
        return $result;
    }

    public static function del($id) {
        Db::name('order')->where('id',$id)->delete();
    }

}
