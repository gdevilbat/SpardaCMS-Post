<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'control', 'middleware' => 'core.menu'], function() {
    
	Route::group(['middleware' => 'core.auth'], function() {

		Route::group(['prefix' => 'post'], function() {
	        /*=============================================
	        =            Post CMS            =
	        =============================================*/
	        
			    Route::get('master', 'PostController@index')->middleware('can:menu-post')->name('post');
			    Route::get('form', 'PostController@create')->name('post');
			    Route::post('form', 'PostController@store')->middleware('can:create-post')->name('post');
			    Route::put('form', 'PostController@store')->name('post');
			    Route::delete('form', 'PostController@destroy')->name('post');

			    Route::group(['prefix' => 'api'], function() {
				    Route::get('master', 'PostController@serviceMaster')->middleware('can:menu-post');
			    });
	        
	        /*=====  End of Post CMS  ======*/
		});

        
	});
});