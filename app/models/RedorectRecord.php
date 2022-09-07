<?php
namespace app\models;

use think\Model;

/**
 * @mixin \think\Model
 */
class RedorectRecord extends Model
{
    public static function getRedorectRecord($params,$page){
        return RedorectRecord::alias('a')
            ->where($params)
            ->field('a.num,a.date,b.redirect_link')
            ->join('redirect_link b','a.redirect_id=b.id')
            ->limit(10)
            ->page($page)
            ->order('a.id desc')
            ->select()
            ->toArray();
    }

    public static function getRedorectRecordCount($params){
        return RedorectRecord::alias('a')
            ->where($params)
            ->field('a.num,a.date,b.redirect_link')
            ->join('redirect_link b','a.redirect_id=b.id')
            ->count();
    }

    public static function increasingNum($redirectId){
        $record = RedorectRecord::where(['redirect_id' => $redirectId])->find();
        if($record == null){
            RedorectRecord::create(['redirect_id' => $redirectId, 'num' => 1, 'date' => date('Y-m-d', time())]);
        }else{
            $record->num = $record->num + 1;
            $record->save();
        }
    } 
}
