<?php
namespace app\controller\api;

use think\Request;
use app\models\Blogger;
use app\models\Order;
use think\facade\Config;
use app\common\DefineConst;

class BloggerController
{
    public function BloggerAll(Request $request) {
        $name = $request->param('name', '');
        return getRsp(200, Blogger::getBloggerAllOrName($name, -1));
    }

    /**
     * 博主订单数据(博主排期)
     */
    public function bloggerList(Request $request) {
        $platform_type = min(array_keys(DefineConst::getPlatfrom())) . ',' . max(array_keys(DefineConst::getPlatfrom()));
        $field_null = [
            'blogger_name' => '""@length:1,24',                  //关键字
            'date' => '""@length:10,10',                         //时间y-m-d
            'platform_type' => '0@between:' . $platform_type     //平台
        ];
        $check_field = [];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $page = $request->get('page', 1);
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        $user_id = $request->user_info['id'];
        $check_field['user_id'] = $user_id;
        $field = 'b.nickname, b.picture, count(o.id) total, b.id';
        $page = -1;
        $result = Blogger::getBolggerList($check_field, $page, $field, 1);
        return getRsp(200, $result);
    }

    /**
     * 上传头像
     */
    public function uploadPicture(Request $request) {
        $file = $request->file('file');
        if (!$file) {
            return getRsp(510);
        }
        $config = Config::get('filesystem');
        $date = date('Y-m-d');
        $path = $config['disks']['public']['root'];
        $path .= "/images/picture/$date";
        $result = \think\facade\Filesystem::disk('public')->putFile('/images/picture', $file);
        $result = str_replace('\\', '/', $result);
        if ($result) {
            return getRsp(200, $result);
        }
        return getRsp(500);
    }

    /**
     * 添加博主
     */
    public function createBlogger(Request $request) {
        $user_id = $request->user_info['id'];
        $check_field = [
            'name' => '""@length:2,24',
            'nickname'=> '""@length:2,24',
            'department' => '""@length:2,12',
            'director'=> '""@length:2,12'
        ];
        $field_null = ['id' => '0@min:1', 'picture'=> '""@length:2,200'];
        if (!checkValue($response, $check_field, false, $field_null)) {
            return $response;
        }
        $check_field['user_id'] = $user_id;
        $check_field['create_time'] = time();
        $result = Blogger::createOrUpdate($check_field);
        if ($result) {
            return getRsp(200);
        }
        return getRsp(500);
    }

}