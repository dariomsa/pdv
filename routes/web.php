<?php

Route::redirect('/', '/login');
Route::redirect('/home', '/admin');
Auth::routes();

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
      Route::redirect('/', '/admin/inscripciones');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

   // Inscripciones
    Route::delete('inscripciones/destroy', 'InscripcionesController@massDestroy')->name('inscripciones.massDestroy');
    
    Route::resource('inscripciones', 'InscripcionesController');
    Route::post('inscripciones/resumen', 'InscripcionesController@resumen')->name('inscripciones.resumen');
    Route::post('inscripciones/finalizar', 'InscripcionesController@finalizar')->name('inscripciones.finalizar');
   
    
    // Inscripciones Gratuitas
    Route::resource('inscripciones_gratuitas', 'InscripcionesGratuitaController');
    Route::post('inscripciones_gratuitas/resumen', 'InscripcionesGratuitaController@resumen')->name('inscripciones_gratuitas.resumen');
    Route::post('inscripciones_gratuitas/finalizar', 'InscripcionesGratuitaController@finalizar')->name('inscripciones_gratuitas.finalizar');

    
    Route::delete('inscripciones/destroy', 'ParticipanteController@massDestroy')->name('participantes.destroy');
	//Route::resource('participantes', 'ParticipanteController'::class);
     

    	//Cierre de Caja
	Route::resource('cierrecaja', 'CierreCajaController');
    	//Cierre de Caja
	Route::resource('cajadetalle', 'CajaDetalleController');

  	// Reporte de Ventas Generales
	Route::resource('reporteventasgenerales', 'ReporteVentasGeneralesController');
  	// Reporte de Ventas Diarias
	Route::resource('reporteventasdiarias', 'ReporteVentasDiariasController');
  
      
	 // Corporativas
    Route::get('corporativas/resumen', 'CorporativasController@resumen')->name('corporativas.resumen');
    
    Route::post('corporativas/finalizar', 'CorporativasController@finalizar')->name('corporativas.finalizar');
    Route::post('corporativas/gratuitas', 'CorporativasController@gratuitas')->name('corporativas.gratuitas');
    Route::post('corporativas/linkpago', 'CorporativasController@linkpago')->name('corporativas.linkpago');
       
    
    Route::resource('corporativas', 'CorporativasController');
   


    

    //Parametros

    Route::get('/parametros', 'ParametrosController@index')->name('parametros.index');



});
