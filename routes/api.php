<?php

header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Accept, Content-Type, X-Auth-Token, Origin, Authorization');



Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () {

      // Permissions
  //  Route::apiResource('permissions', 'PermissionsApiController');

    // Roles
  //  Route::apiResource('roles', 'RolesApiController');

    // Users
  //  Route::apiResource('users', 'UsersApiController');

    // Inscripciones
   // Route::apiResource('inscripciones', 'InscripcionesApiController');
//    Route::post('inscripciones', 'InscripcionesApiController@inscritos');

      Route::post('inscripciones_insert', 'InscripcionNormalApiController@inscripciones_insert');
      Route::post('/verificar-inscripcion', 'InscripcionNormalApiController@verificar');


      


    // Expensereports
   // Route::apiResource('expense-reports', 'ExpenseReportApiController');
});

