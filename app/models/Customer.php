<?php
namespace app\models;

use think\Model;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;

/**
 * @mixin \think\Model
 */
class Customer extends Model
{
    //
    public static function getCustomerIdByName($name) {
        return Customer::where([['name', 'like', "%$name%",], ['is_disable', '=', 0]])
            ->order('create_time', 'desc')
            ->column('id');
    }

    /**
     * 获取全部客户，下拉时使用
     * @param int is_disable -1不筛选该字段
     */
    public static function getCustomerAllOrName($name = '', $is_disable = -1) {
        if (empty($name)) {
            $where = $is_disable == -1 ? [] : [['is_disable', '=', $is_disable]];
            if (!Config::get('app.is_disable_redis')) {
                $result = Customer::where($where)->column('name', 'id');
            } else {
                $result = Cache::store('redis')->hgetall('customer');
                if (empty($result)) {
                    $result = Customer::where($where)->column('name', 'id');
                    foreach ($result as $k => $v) {
                        Cache::store('redis')->hSet('customer', $k, $v);
                    }
                }
            }
        } else {
            $where[] = ['name', 'like', "%$name%"];
            $result = Customer::where($where)->column('name', 'id');
        }
        return $result;
    }

    /**
     * 客户详情
     */
    public static function getCustomerInfo($id, $field = '') {
        $result = Customer::where('id', $id)->field($field)->find();
        if ($result == null) {
            return [];
        }
        return $result;
    }

    public static function getCustomerList($page) {
        return Db::name('customer')
            ->limit(15)
            ->page((int)$page)
            ->select()
            ->toArray();
    }

    public static function getCustomerListCount() {
        return Db::name('customer')->count();
    }

    public static function createOrUpdate($id, $data) {
        if (!empty($id) && (is_numeric($id) || $id > 0)) {
            Db::name('customer')->where(['id' => $id])->update($data);
        } else {
            $data['create_time'] = time();
            Db::name('customer')->insert($data);
        }
    }

    public static function del($id) {
        Db::name('customer')->where('id',$id)->delete();
    }

}
