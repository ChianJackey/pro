<?php
namespace app\controller\admin;

use think\Request;
use app\models\Department;
use think\facade\Config;

class DepartmentController
{
    public function departmentList(Request $request) {
        if ($request->method() == 'GET') {
            $token = $request->param('token');
            return view('Department/department_list', ['token' => $token]);
        } else {
            $page = $request->param('page', 1);
            if (!is_numeric($page) || $page <= 0) {
                $page = 1;
            }
            $result = Department::getDepartmentList($page);
            $count = Department::getDepartmentListCount();
            return getRsp(0, $result, $count);
        }
    }

    public function departmentDetail(Request $request) {
        if ($request->method() == 'POST') {
            $id = $request->param('id', 0);
            $name = $request->param('name', 0);
            Department::createOrUpdate($id, $name);
            return getRsp(200);
        } else {
            $id = $request->param('id','');
            if (is_numeric($id) && $id > 0) {
                Department::del($id);
            }
            return getRsp(200);
        }
        
    }
}
