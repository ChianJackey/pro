<?php
namespace app\models;

use think\Model;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use app\common\DefineConst;
/**
 * @mixin \think\Model
 */
class Blogger extends Model
{
    //
    public static function getBloggerIdByName($name) {
        return Blogger::where([['name', 'like', "%$name%",], ['is_disable', '=', 0]])
            ->order('create_time', 'desc')
            ->column('id');
    }
    /**
     * 获取全部博主，下拉时使用
     * @param int is_disable -1不筛选该字段
     */
    public static function getBloggerAllOrName($name = '', $is_disable = -1) {
        if (empty($name)) {
            $where = $is_disable == -1 ? [] : [['is_disable', '=', $is_disable]];
            if (!Config::get('app.is_disable_redis')) {
                $result = Blogger::where($where)->order('id', 'desc')->column('name', 'id');
            } else {
                $result = Cache::store('redis')->hgetall('blogger');
                if (empty($result)) {
                    $result = Blogger::where($where)->order('id', 'desc')->column('name', 'id');
                    foreach ($result as $k => $v) {
                        Cache::store('redis')->hSet('blogger', $k, $v);
                    }
                }
            }
        } else {
            $where[] = ['name', 'like', "%$name%"];
            $result = Blogger::where($where)->order('id', 'desc')->column('name', 'id');
        }
        return $result;
    }

    /**
     * 博主详情
     */
    public static function getBloggerInfo($id, $field = '') {
        $result = Blogger::where('id', $id)->field($field)->find();
        if ($result == null) {
            return [];
        }
        return $result;
    }

    /**
     * 博主列表
     * @param int type 1:小程序调用(发布时间一天),2:后台调用
     * @tisp  type:1 inner join只查询有订单
     */
    public static function getBolggerList($param, $page, $field = '*', $order = 'total', $type = 1) {
        $obj = self::getBloggerObj($param, $type);
        if ($page != -1) {
            $obj = $obj->limit(15)
                ->page((int)$page);
        }
        $result = $obj->order($order,'desc')
            ->field($field)
            ->select()
            ->toArray();
        return $result;
    }

    public static function getBloggerCount($param, $type) {
        $obj = self::getBloggerObj($param, $type);
        return $obj->count();
    }

    public static function getBloggerObj($param, $type) {
        $where = [];
        //博主名字筛选
        if (isset($param['blogger_name']) && !empty($param['blogger_name'])) {
            $where[] = ['b.name', '=', $param['blogger_name']];
        }
        //平台筛选
        if (isset($param['platform_type']) && !empty($param['platform_type'])) {
            $where[] = ['o.platform_type', '=', $param['platform_type']];
        }
        if ($type == 1) {
            //时间筛选
            if (isset($param['date']) && !empty($param['date'])) {
                $start_time = date('Y-m-d', strtotime($param['date']));
            } else {
                $start_time = date('Y-m-d', time());
            }
            $end_time = $start_time . ' 23:59:59';
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $where[] = ['o.publish_time', '>=', $start_time];
            $where[] = ['o.publish_time', '<=', $end_time];
        }
        //状态筛选
        if (isset($param['is_disable']) && in_array($param['is_disable'], [0, 1])) {
            $where[] = ['b.is_disable', '=', $param['is_disable']];
        }
        //关键字
        $or = [];
        if (isset($param['keywork']) && !empty($param['keywork'])) {
            $or[] = ['b.id', '=', $param['keywork']];
            $or[] = ['b.name', 'like', '%' . $param['keywork'] . '%'];
            $or[] = ['b.nickname', 'like', '%' . $param['keywork'] . '%'];
        }
        $obj = Db::name('blogger')
            ->alias('b')
            ->leftJoin ('order o', 'b.id = o.blogger_id')
            ->where($where);
        if (isset($or) && !empty($or)) {
            $obj = $obj->where(function($query) use($or){
                $query->whereOr($or);
            });
        }
        $obj = $obj->group('b.id');
        return $obj;
    }

    /**
     * 博主排行
     * @param int $type 统计类型 1收益 2成交金额
     * @param string $date y-m
     */
    public static function getBloggerRanking($param, $page) {
        if (isset($param['type']) && !empty($param['type']) && $param['type'] == 2) {
            $type = 'deal_price';
        } else {
            $type = 'examples_price';
        }
        $field = "sum($type) as money, count(o.id) as order_num, b.name,";
        $platfrom = DefineConst::getPlatfrom();
        foreach ($platfrom as $key => $val) {
            $field .= "(SELECT sum($type) FROM adverts_order WHERE blogger_id = o.blogger_id AND platform_type = $key GROUP BY platform_type) AS title_$key,";
        }
        $field = substr($field,0,strlen($field)-1);
        //时间筛选
        if (isset($param['date']) && !empty($param['date'])) {
            $start_time = date('Y-m', strtotime($param['date']));
        } else {
            $start_time = date('Y-m', time());
        }
        $end_time = date('Y-m-d', strtotime("$start_time +1 month -1 day"));
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time) + 86399;
        $limit = ($page - 1) * 15;
        $where = "(o.publish_time >= $start_time AND o.publish_time <= $end_time) OR o.publish_time IS NULL";
        $sql = "SELECT $field FROM adverts_blogger AS B LEFT JOIN adverts_order AS O ON B.id = O.blogger_id WHERE $where GROUP BY b.id ORDER BY money DESC LIMIT $limit,15";
        $result = Db::query($sql);
        //return $result;
        foreach ($result as $k => $v) {
            foreach ($platfrom as $key => $val) {
                if (isset($v['title_' . $key]) || $v['title_' . $key] == null) {
                    if (isset($param['role_id']) && $param['role_id'] == 13) {
                        $result[$k]['title_' . $key] = '*';
                    } else {
                        if ($v['title_' . $key] == null) {
                            $result[$k]['title_' . $key] = 0;
                        } else {
                            $result[$k]['title_' . $key] = $v['title_' . $key] / 100;
                        }
                    }
                }
            }
            if (isset($param['role_id']) && $param['role_id'] == 13) {
                $result[$k]['money'] = '*';
            } else {
                if ($v['money'] == null) {
                    $result[$k]['money'] = 0;
                } else {
                    $result[$k]['money'] = $v['money'] / 100;
                }
            }
        }
        return $result;
    }

    public static function getBloggerRankingCount($param) {
        if (isset($param['date']) && !empty($param['date'])) {
            $start_time = date('Y-m', strtotime($param['date']));
        } else {
            $start_time = date('Y-m', time());
        }
        $end_time = date('Y-m-d', strtotime("$start_time +1 month -1 day"));
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time) + 86399;
        $where = "(o.publish_time >= $start_time AND o.publish_time <= $end_time) OR o.publish_time IS NULL";
        $sql = "SELECT count(*) AS count FROM (SELECT count(*) FROM adverts_blogger AS B LEFT JOIN adverts_order AS O ON B.id = O.blogger_id WHERE $where
            GROUP BY b.id) AS a";
        $result = Db::query($sql);
        return $result[0]['count'];
    }

    /**
     * 创建或编辑
     * @param string user_id 创建者ID
     * @param string name 博主姓名
     * @param string nickname 博主昵称
     * @param string department 部门
     * @param string director 编导
     * @param string picture 头像
     * @param int create_time 创建时间
     */
    public static function createOrUpdate($data) {
        if (isset($data['id']) && !empty($data['id'])) {
            $result = Blogger::find($data['id']);
            if ($result === null) {
                return false;
            }
            try {
                unset($data['user_id']);
                unset($data['create_time']);
                Blogger::update($data);
                return true;
            } catch (\Throwable $th) {
                return $th->getMessage();
            }
        } else {
            $result = Blogger::create($data);
            return is_int($result->id) || is_numeric($result->id) ? true : false;
        }
    }

    public static function getBloggerData($page, $keywork = '') {
        if ($page != -1) {
            $page = ((int)$page-1) * 10;
            $limit = "LIMIT $page, 15";
        } else {
            $limit = '';
        }
        $where = '';
        if ($keywork != '') {
            $where =  "WHERE b.name LIKE '%$keywork%' OR b.nickname LIKE '%$keywork%' OR b.id LIKE '%$keywork%'";
        }
        $result = Db::query("SELECT b.id,b.name,b.create_time,count(*) AS count,sum(o .examples_price) AS examples_price,sum(o .discount) AS discount,sum(o.deal_price) AS deal_price,sum(o .chart_price) AS chart_price FROM adverts_blogger AS b LEFT JOIN adverts_order AS o ON b.id = o.blogger_id $where GROUP BY b.id ORDER BY b.create_time $limit");
        return $result;
    }
    public static function getBloggerDataCount($keywork = '') {
        $where = '';
        if ($keywork != '') {
            $where =  "WHERE b.name LIKE '%$keywork%' OR b.nickname LIKE '%$keywork%' OR b.id LIKE '%$keywork%'";
        }
        return DB::query("SELECT count(*) as count FROM(SELECT b.id,b.name,b.create_time,count(*) AS count,sum(o .examples_price) AS examples_price,sum(o .discount) AS discount,sum(o.deal_price) AS deal_price,sum(o .chart_price) AS chart_price FROM adverts_blogger AS b LEFT JOIN adverts_order AS o ON b.user_id = o.user_id $where GROUP BY b.id) AS a");

    }

    public static function del($id) {
        Db::name('blogger')->where('id',$id)->delete();
    }

}
