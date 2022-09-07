<?php
namespace app\models;

use think\Model;

/**
 * @mixin \think\Model
 */
class RedirectLink extends Model
{
    public static function getRedirectLinkList($monitorId){
        return RedirectLink::where(['monitor_id' => $monitorId, 'is_delete' => 0])
            ->field('id,redirect_link,num,rank')
            ->order('id desc')
            ->select()
            ->toArray();
    }

    public static function addRedirectLink($params){
        return RedirectLink::insertGetId($params);
    }

    public static function deleRedirectLink($id){
        return RedirectLink::where('id', $id)->update(['is_delete' => 1]);
    }

    public static function saveRedirectLink($data){
        foreach($data as $val){
            RedirectLink::where('id', $val['id'])
                ->update([
                    'redirect_link' => $val['redirect_link'],
                    'num' => $val['num'],
                    'rank' => $val['rank']
                ]);
        }
    }

    public static function getSelectLink(){
        return RedirectLink::where(['is_delete' => 0])
            ->field('redirect_link')
            ->order('id desc')
            ->group('redirect_link')
            ->select()
            ->toArray();
    }

    public static function getRedirectLink($monitorId){
        return RedirectLink::alias('a')
            ->leftjoin('redorect_record b','a.id = b.redirect_id and b.date = "'.date('Y-m-d',time()).'"')
            ->where(['a.monitor_id' => $monitorId, 'a.is_delete' => 0])
            ->field('a.id,a.redirect_link,a.num,a.rank,b.num as redirect_num')
            ->select()
            ->toArray();
    }

    public static function getLastRedirectInfo(){
        return RedirectLink::order('id desc')->where(['is_delete' => 0])->field('id,redirect_link')->find();
    }

}
