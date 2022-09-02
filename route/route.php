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

Route::any('login', 'admin.Index/login');
//API
Route::post('login', 'api.UsersController/login');                           //用户登录
Route::group(function(){
    Route::get('order-list', 'api.OrderController/orderList');                  //广告排期-订单列表
    Route::post('create-order', 'api.OrderController/createOrder');             //广告排期-添加&编辑订单
})->middleware(app\middleware\CheckToken::class, 1);
