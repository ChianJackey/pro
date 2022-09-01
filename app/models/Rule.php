<?php
namespace app\models;

use think\Model;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use app\models\Action;

/**
 * @mixin \think\Model
 */
class Rule extends Model
{
    //
    public static function getRuleAll($id = 0) {
        if (!Config::get('app.is_disable_redis')) {
            $result = Rule::select()->toArray();
        } else {
            $result = Cache::store('redis')->hgetall('rule');
            if (empty($result)) {
                $result = Rule::select()->toArray();
            }
        }
        if (empty($result)) {
            return [];
        }
        $rule_list = array_column($result, null, 'id');
        if ($id > 0) {
            $rule_list = $rule_list[$id];
        }
        return $rule_list;
    }

    public static function getRulePower($page, $is_disable, $keywork) {
        $obj = self::getRulePowerObj($is_disable, $keywork);
        if ($page != -1) {
            $obj = $obj->limit(15)
                ->page((int)$page);
        }
        return $obj->order('u.create_time', 'desc')
            ->select()
            ->toArray();
    }

    public static function getRulePowerCount($is_disable, $keywork) {
        $obj = self::getRulePowerObj($is_disable, $keywork);
        return $obj->count();
    }

    public static function getRulePowerObj($is_disable, $keywork = '') {
        $where = [];
        if ($is_disable != -1) {
            $where['is_disable'] = $is_disable;
        }
        $or = '';
        if (!empty($keywork)){
            $or = "u.name LIKE '%$keywork%' OR u.account LIKE '%$keywork%' OR u.id LIKE '%$keywork%'";
        }
        $obj = Db::name('user')
            ->alias('u')
            ->field('u.*, r.name as rule_name, d.name as department_name')
            ->join('rule r', 'u.role_id = r.id')
            ->Leftjoin('department d', 'u.department_id = d.id')
            ->where($where)
            ->where($or);
        return $obj;
    }

    public static function getRuleList($page) {
        return Db::name('rule')
            ->limit(15)
            ->page((int)$page)
            ->select()
            ->toArray();
    }

    public static function getRuleListCount() {
        return Db::name('rule')->count();
    }

    public static function getRuleName($id) {
        $result = Rule::find($id);
        if ($result === null) {
            return '';
        } else {
            return $result['name'];
        }
    }

    public static function createOrCreate($data) {
        if (isset($data['id']) && !empty($data['id']) && is_numeric($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            Db::name('rule')->where(['id' => $id])->update($data);
        } else {
            $data['create_time'] = time();
            Db::name('rule')->insert($data);
        }
    }

}
