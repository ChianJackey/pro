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


Route::get('login', 'index/login');
Route::post('login-in', 'index/login');
Route::get('index', 'index/index');
//API
Route::group(function(){
    Route::any('monitor-link', 'index/monitorLink');
    Route::any('monitor-detail', 'index/monitorDetail');
    Route::post('dele-monitor', 'index/deleMonitor');
    Route::post('add-redirect', 'index/addRedirect');
    Route::post('add-monitor', 'index/addMonitor');
    Route::post('dele-redirect', 'index/deleRedirect');
    Route::post('save-redirect', 'index/saveRedirect');
    Route::get('redorect-record', 'index/redorectRecord');
    Route::post('redorect-record', 'index/redorectRecord');
});

//->middleware(app\middleware\CheckToken::class)