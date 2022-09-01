<?php
namespace app\models;

use think\Model;
use think\facade\Db;
use app\models\User;
use app\common\DefineConst;

/**
 * @mixin \think\Model
 */
class UserKpi extends Model
{
    /**
     * 获取用户KPI
     */
    public static function getUserKpi($user_id, $start_time, $end_time) {
        $result =  Db::name('user_kpi')
            ->where([['user_id', '=', $user_id], ['create_time', '>=', $start_time], ['create_time', '<=', $end_time]])
            ->find();
        if ($result == null) {
            return [];
        }
        return $result;
    }

    public static function getUserKpis($page) {
        $obj = self::getUserKpiObj();
        $result = $obj
            ->field('u.name, user_kpi.*')
            ->limit(15)
            ->page((int)$page)
            ->select()
            ->toArray();
        if (empty($result)) {
            return [];
        }//
        $platfrom = DefineConst::getPlatfrom();
        foreach ($result as &$val) {
            $kpi = [];
            $kpi_json = json_decode($val['kpi_json'], true);
            $num = 1;
            foreach ($platfrom as $k => $v) {
                $val['field_' . $num] = $v;
                if (isset($kpi_json[$k])) {
                    $val['kpi_' . $num] = $kpi_json[$k]['kpi'];
                } else {
                    $val['kpi_' . $num] = 0;
                }
                $num++;
            }

            if (isset($kpi_json[0])) {
                $val['kpi_0'] = $kpi_json[0]['kpi'];
            } else {
                $val['kpi_0'] = 0;
            }
            $val['examine_time'] = date('Y-m-d', $val['examine_time']);
        }
        return $result;
    }

    public static function getUserKpiCount() {
        $obj = self::getUserKpiObj();
        return $obj->count();
    }

    public static function getUserKpiObj() {
        return Db::name('user')
            ->alias('u')
            ->join('user_kpi', 'u.id = user_kpi.user_id')
            ->order('u.id', 'desc');
    }

    public static function setUserKpi($id, $data) {
        $result = UserKpi::find($id);
        if ($result == null) {
            Db::name('user_kpi')->insert($data);
        } else {
            Db::name('user_kpi')->where(['id' => $id])->update($data);
        }
    }

    public static function getUserKpiFromMonth() {
        $time = strtotime(date('Y-m', time()));
        $user_list = User::getUserAll();
        $kpi_list = Db::name('user_kpi')
            ->field('douyin, kuaishou, xiaohongshu, all, user_id')
            ->where(['examine_time' => $time])
            ->select()
            ->toArray();
        $kpi_list = array_column($kpi_list, null, 'user_id');
        foreach ($user_list as &$val) {
            if (!isset($kpi_list[$val['id']])) {
                $val['douyin'] = 0;
                $val['kuaishou'] = 0;
                $val['xiaohongshu'] = 0;
                $val['all'] = 0;
            } else {
                $val['douyin'] = $kpi_list[$val['id']]['douyin'];
                $val['kuaishou'] = $kpi_list[$val['id']]['kuaishou'];
                $val['xiaohongshu'] = $kpi_list[$val['id']]['xiaohongshu'];
                $val['all'] = $kpi_list[$val['id']]['all'];
            }
        }
        return $user_list;
    }
}
