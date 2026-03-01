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
   // routes/api.php o web.php
    Route::post('/inscripciones/eliminar-completa', [InscripcionesController::class, 'eliminarInscripcionCompleta']);
    Route::delete('inscripciones/destroy', 'InscripcionesController@massDestroy')->name('inscripciones.massDestroy');
    
    Route::resource('inscripciones', 'InscripcionesController');
    Route::post('inscripciones/resumen', 'InscripcionesController@resumen')->name('inscripciones.resumen');
    Route::post('inscripciones/finalizar', 'InscripcionesController@finalizar')->name('inscripciones.finalizar');
	
    Route::post('inscripciones/update-inline', 'InscripcionesController@updateInline')->name('inscripciones.update.inline');
	
	Route::get('/tallas', 'InscripcionesController@tallasDisponibles')->name('tallas');
	
	


   
    
    // Inscripciones Gratuitas
    Route::resource('inscripciones_gratuitas', 'InscripcionesGratuitaController');
    Route::post('inscripciones_gratuitas/resumen', 'InscripcionesGratuitaController@resumen')->name('inscripciones_gratuitas.resumen');
    Route::post('inscripciones_gratuitas/finalizar', 'InscripcionesGratuitaController@finalizar')->name('inscripciones_gratuitas.finalizar');
	
	
	
	

   Route::resource('participantes', 'ParticipanteController'::class);

  
    //Cierre de Caja
	Route::resource('cierrecaja', 'CierreCajaController');
    	//Cierre de Caja
	Route::resource('cajadetalle', 'CajaDetalleController');

  	// Reporte de Ventas Generales
	Route::resource('reporteventasgenerales', 'ReporteVentasGeneralesController');
  	// Reporte de Ventas Diarias

  
      
	 // Corporativas
    Route::get('corporativas/resumen', 'CorporativasController@resumen')->name('corporativas.resumen');
    
    Route::post('corporativas/finalizar', 'CorporativasController@finalizar')->name('corporativas.finalizar');
    Route::post('corporativas/gratuitas', 'CorporativasController@gratuitas')->name('corporativas.gratuitas');
    Route::post('corporativas/linkpago', 'CorporativasController@linkpago')->name('corporativas.linkpago');
       
    
    Route::resource('corporativas', 'CorporativasController');
	
	Route::post('corporativas/inscripcion/{id}', 'CorporativasController@marcarCertificado')->name('corporativas.inscripcion');
	

	

    

    //Parametros

    Route::get('/parametros', 'ParametrosController@index')->name('parametros.index');
    Route::post('/categoria/por-fecha', 'CategoriaController@obtenerPorFechaNacimiento')->name('categoria.porFecha');

   // recibo
    Route::resource('recibo', 'ReciboController');


});


	
	 // Certificaods
	 
	 Route::prefix('admin')->group(function () {
    	    Route::get('pdv', [\App\Http\Controllers\Admin\CorporativasController::class, 'pdv']);
});
	 

	
	

  //Facturacion

  Route::get('/facturar', 'FacturacionController@index')->name('facturar.index');
  Route::get('/facturar/{corp_id}', 'FacturacionController@show')->name('facturar.show');
   

  Route::post('/buscar/base2024', [App\Http\Controllers\Base2024Controller::class, 'buscar']);



Route::get('/ss', function () {
    return view('pdfs.recibo');
});



Route::prefix('admin/geo')->group(function () {
    Route::get('provincias', [\App\Http\Controllers\Admin\GeoController::class, 'provincias']);
    Route::get('cantones',   [\App\Http\Controllers\Admin\GeoController::class, 'cantones']);
    Route::get('parroquias', [\App\Http\Controllers\Admin\GeoController::class, 'parroquias']);
});

