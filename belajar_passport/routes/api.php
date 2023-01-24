<?php
Route::prefix('v1')->group(function(){
    Route::post('register','Api\V1\AuthController@register');
    Route::post('login','Api\V1\AuthController@login');
    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('logout','Api\V1\AuthController@logout');
    });
});
