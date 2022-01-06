<?php

use Illuminate\Http\Request;
//use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route;
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([
    'prefix' => 'v1'
], function () {
    Route::post('login', 'RegisterController@login');
    Route::post('signup', 'RegisterController@signup');
    Route::get('set','CalendarController@set');
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'RegisterController@logout');
        Route::get('user', 'RegisterController@user');
        Route::get('todolist', 'TodoModelController@todolist');
        Route::post('addtodolist', 'TodoModelController@addtodolist');
        Route::post('updatetodolist', 'TodoModelController@updatetodolist');
        Route::get('alarm','CalendarController@alarm');
        Route::post('addalarm','CalendarController@addalarm');
        Route::post('updatealarm','CalendarController@updatealarm');
        Route::get('calendar','CalendarController@calendar');
        Route::post('addcalendar','CalendarController@addcalendar');
        Route::post('updatecalendar','CalendarController@updatecalendar');
    });
});


// //用户注册不需要Oauth
// Route::post('/register','RegisterController@register');