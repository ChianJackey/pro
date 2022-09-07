<?php
namespace app\models;

use think\Model;

/**
 * @mixin \think\Model
 */
class MonitorLink extends Model
{
    public static function getMonitorLinkList($page){
        return MonitorLink::where(['is_delete' => 0])
            ->field('id,monitor_link,name,remark,create_time')
            ->order('id desc')
            ->limit(10)
            ->page($page)
            ->select()
            ->toArray();
    }

    public static function getMonitorLinkCount(){
        return MonitorLink::where(['is_delete' => 0])->count();
    }

    public static function deleMonitorLink($id){
        return MonitorLink::where('id', $id)->update(['is_delete' => 1]);
    }

    public static function getMonitorLinkDetail($id){
        return MonitorLink::where(['id' => $id])
            ->field('id,name,remark,monitor_link')
            ->find()
            ->toArray();
    }

    public static function saveMonitorLink($id, $params){
        MonitorLink::where('id', $id)->update($params);
    }

    public static function addMonitorLink($params){
        return MonitorLink::insertGetId($params);
    }

    public static function getMonitorLinkId($flag){
        $id = MonitorLink::where(['flag' => $flag, 'is_delete' => 0])->value('id');
        if(!$id){
            return MonitorLink::order('id desc')->where(['is_delete' => 0])->value('id');
        }
        return $id;
    }

}
