<?php
namespace app\models;

use think\Model;
use think\facade\Db;

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

}
