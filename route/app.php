<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

//API
Route::post('login', 'api.UsersController/login');                           //用户登录
Route::group(function(){
    Route::get('order-list', 'api.OrderController/orderList');                  //广告排期-订单列表
    Route::post('create-order', 'api.OrderController/createOrder');             //广告排期-添加&编辑订单
    Route::get('order-info', 'api.OrderController/orderInfo');                  //订单详情
    Route::get('blogger-list', 'api.BloggerController/bloggerList');            //博主列表(博主排期)
    Route::get('profit-statistics', 'api.UsersController/profitStatistics');    //今日看板-数据统计
    Route::get('blogger-ranking', 'api.UsersController/bloggerRanking');        //今日看板-博主广告排行 -
    Route::get('my-order', 'api.UsersController/myOrder');                      //我的订单
    Route::get('money-schedule', 'api.UsersController/moneySchedule');          //金额排期
    Route::get('export-schedule', 'api.UsersController/exportSchedule');        //导出金额排期
    Route::post('upload-picture', 'api.BloggerController/uploadPicture');       //上传头像
    Route::post('create-blogger', 'api.BloggerController/createBlogger');       //创建博主
    Route::get('worker-manage', 'api.UsersController/workerManage');            //员工管理
    Route::any('api-customer-detail', 'admin.CustomerController/customerDetail');
})->middleware(app\middleware\CheckToken::class, 1);

//后台
Route::any('admin-login', 'admin.IndexController/login');
Route::get('admin-index', 'admin.IndexController/index');

Route::group(function(){
    Route::get('admin-getstatistics', 'admin.IndexController/getStatistics');
    Route::get('admin-today-statistics', 'admin.IndexController/todayStatistics');//今日看板
    Route::get('admin-blogger-ranking', 'admin.IndexController/bloggerRanking');//博主排行
    Route::get('admin-order-schedule', 'admin.IndexController/orderSchedule');//
    Route::get('admin-blogger-schedule', 'admin.IndexController/bloggerSchedule');//
    Route::any('admin-order-list', 'admin.OrderController/orderList');          //订单列表
    Route::any('admin-order-detail', 'admin.OrderController/orderDetail');      //订单详情，订单编辑
    Route::get('admin-order-export', 'admin.OrderController/orderExport');      //导出订单
    Route::get('admin-order-del', 'admin.OrderController/orderDel');            //删除订单
    Route::any('admin-blogger-list', 'admin.BloggerController/bloggerList');    //博主列表
    Route::any('admin-blogger-detail', 'admin.BloggerController/bloggerDetail');//博主详情
    Route::post('admin-blogger-disable', 'admin.BloggerController/disable');    //设置状太
    Route::post('admin-upload-picture', 'admin.BloggerController/uploadPicture'); //上传头像
    Route::any('admin-kpi-list', 'admin.UserKpiController/userKpi');              //查看用户KPI
    Route::post('admin-set-kpi', 'admin.UserKpiController/setKpi');               //修改KPI
    Route::any('admin-user-kpi', 'admin.UserKpiController/modUserKpi');           //添加，修改用户KPI
    Route::any('admin-user-data', 'admin.UserController/userData');               //员工数据
    Route::get('admin-user-export', 'admin.UserController/userExport');           //导出员工数据
    Route::any('admin-blogger-data', 'admin.BloggerController/bloggerData');      //博主数据
    Route::get('admin-blogger-export', 'admin.BloggerController/bloggerExport');  //导出员工数据
    Route::any('admin-rule-power', 'admin.PowerController/rulePower');            //成员列表(成员权限)
    Route::any('admin-user-detail', 'admin.UserController/userDetail');           //成员详情
    Route::any('admin-rule-list', 'admin.PowerController/ruleList');              //角色列表
    Route::any('admin-rule-detail', 'admin.PowerController/ruleDetail');          //角色详情
    Route::any('admin-department-list', 'admin.DepartmentController/departmentList');//部门列表
    Route::any('admin-department-detail', 'admin.DepartmentController/departmentDetail');//部门详情
    Route::any('admin-customer-list', 'admin.CustomerController/customerList');     //客户列表
    Route::any('admin-customer-detail', 'admin.CustomerController/customerDetail');
    Route::any('admin-business-list', 'admin.BusinessController/businessList');     //客户列表
    Route::any('admin-business-detail', 'admin.BusinessController/businessDetail');
    Route::any('admin-agreement', 'admin.UserController/agreement');
    Route::get('admin-blogger-dele', 'admin.BloggerController/del');
    Route::any('admin-platfrom', 'admin.IndexController/platfrom');
})->middleware(app\middleware\CheckToken::class, 2)->middleware(app\middleware\CheckPower::class)->middleware(app\middleware\CheckRoule::class);


//通用
/* 下拉数据 */
Route::get('business', 'api.BusinessController/businessAll');                           //获取对接商务
Route::get('customer', 'api.CustomerController/customerAll');                           //获取客户名称
Route::get('blogger', 'api.BloggerController/bloggerAll');                              //获取博主名称
Route::get('platfrom', 'api.UsersController/platfrom');                                  //平台
/* 下拉数据 */
