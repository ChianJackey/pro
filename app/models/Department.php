<?php
namespace app\models;

use think\Model;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;

class Department extends Model
{
    public static function getDepartmentAllOrName($name = '') {
        if (empty($name)) {
            $result = Department::order('id', 'desc')->column('name', 'id');
        } else {
            $where[] = ['name', 'like', "%$name%"];
            $result = Department::where($where)->order('id', 'desc')->column('name', 'id');
        }
        return $result;
    }

    public static function getDepartmentList($page) {
        return Db::name('department')
            ->limit(15)
            ->page((int)$page)
            ->select()
            ->toArray();
    }

    public static function getDepartmentListCount() {
        return Db::name('department')->count();
    }

    public static function createOrUpdate($id, $name) {
        if (!empty($id) || is_numeric($id) || $id > 0) {
            Db::name('department')->where(['id' => $id])->update(['name' => $name]);
        } else {
            Db::name('department')->insert(['name' => $name, 'create_time' => time()]);
        }
    }

    public static function del($id) {
        Db::name('department')->where('id',$id)->delete();
    }
}