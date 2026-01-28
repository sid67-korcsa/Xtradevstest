<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Example API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'namespace' => '\App\Http\Controllers\Api\v2',
    'prefix' => 'v2'
], function(){

    //Authenticated API routes
    Route::group(['middleware' => ['auth:api', 'api_incoming']], function () {
        Route::apiResources([
            'heartbeats' => 'HeartbeatController',
            'employee-workstate-changes' => 'EmployeeWorkStateChangeController',
            'leave-requests' => 'LeaveRequestController'
        ]);
    });

});
