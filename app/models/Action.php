<?php
namespace app\models;

use think\Model;
use app\models\Rule;
use think\facade\Db;

/**
 * @mixin \think\Model
 */
class Action extends Model
{
    //
    public static function getParentAction($power, $id = 0) {
        if (is_array($power)) {
            $power = implode(',', $power);
        }
        $where = [];
        if ($power != '*') {
            $where[] = ['a1.id', 'in', $power];
        } else {
            if ($id == 13) {
                $where[] = ['a1.id', 'not in', [3, 6, 8, 9, 15, 17, 21, 27]];
                $where[] = ['a2.id', 'not in', [3, 6, 8, 9, 15, 17, 21, 27]];
            }
        }
        $result = self::getAction($where);
        
        if (empty($result)) {
            return [];
        }
        $list = [];
        foreach ($result as $val) {
            if (!isset($list[$val['id']])) {
                $list[$val['id']] = ['ppid' => $val['id'], 'name' => $val['pname']];
            }
            $list[$val['id']]['child'][] = ['name' => $val['name'], 'route' => $val['route'], 'is_menu' => $val['is_menu']];
        }
        return $list;
    }

    public static function getActionFromRole($role_id) {
        $rule_info =  Rule::getRuleAll($role_id);
        if (empty($rule_info)) {
            return [];
        }
        $power = $rule_info['power'];
        $where[] = ['a1.id', 'in', $power];
        if ($role_id == 13) {
            $where[] = [];
        }
        $action_info = self::getAction($where);
        return empty($action_info) ? [] : array_column($action_info, 'route');
    }

    public static function getAction($param) {
        return Db::name('action')
            ->alias('a1')
            ->where($param)
            ->field('a1.id,a1.name as pname,a1.pid as ppid, a2.name,a2.route,a2.pid,a2.is_menu')
            ->join('action a2', 'a1.id = a2.pid')
            ->select()
            ->toArray();
    }

}
