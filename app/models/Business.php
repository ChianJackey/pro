<?php
namespace app\models;

use think\Model;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;

/**
 * @mixin \think\Model
 */
class Business extends Model
{
    //
    public static function getBusinessIdByName($name) {
        return Business::where([['name', 'like', "%$name%",], ['is_disable', '=', 0]])
            ->order('create_time', 'desc')
            ->column('id');
    }

    /**
     * 获取全部商务，下拉时使用
     * @param int is_disable -1不筛选该字段
     */
    public static function getBusinessAllOrName($name = '', $is_disable = -1) {
        if (empty($name)) {
            $where = $is_disable == -1 ? [] : [['is_disable', '=', $is_disable]];
            if (!Config::get('app.is_disable_redis')) {
                $result = Business::where($where)->column('name', 'id');
            } else {
                $result = Cache::store('redis')->hgetall('business');
                if (empty($result)) {
                    $result = Business::where($where)->column('name', 'id');
                    foreach ($result as $k => $v) {
                        Cache::store('redis')->hSet('business', $k, $v);
                    }
                }
            }
        } else {
            $where[] = ['name', 'like', "%$name%"];
            $result = Business::where($where)->column('name', 'id');
        }
        return $result;
    }

    /**
     * 商务详情
     */
    public static function getBusinessInfo($id, $field = '') {
        $result = Business::where('id', $id)->field($field)->find();
        if ($result == null) {
            return [];
        }
        return $result;
    }

    public static function getBusinessList($page) {
        return Db::name('business')
            ->limit(15)
            ->page((int)$page)
            ->select()
            ->toArray();
    }

    public static function getBusinessListCount() {
        return Db::name('business')->count();
    }

    public static function createOrUpdate($id, $data) {
        if (!empty($id) && (is_numeric($id) || $id > 0)) {
            Db::name('business')->where(['id' => $id])->update($data);
        } else {
            $data['create_time'] = time();
            Db::name('business')->insert($data);
        }
    }

    public static function del($id) {
        Db::name('business')->where('id',$id)->delete();
    }

}
