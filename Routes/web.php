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
	        
			    Route::get('master', 'PostController@index')->middleware('can:menu-post')->name('cms.post-data.master');
			    Route::get('form', 'PostController@create')->name('cms.post-data.create');
			    Route::post('form', 'PostController@store')->middleware('can:create-post')->name('cms.post-data.store');
			    Route::put('form', 'PostController@store')->name('cms.post-data.store');
			    Route::delete('form', 'PostController@destroy')->name('cms.post-data.delete');

			    Route::group(['prefix' => 'api'], function() {
				    Route::get('master', 'PostController@serviceMaster')->middleware('can:menu-post')->name('cms.post-data.service-master');
			    });
	        
	        /*=====  End of Post CMS  ======*/
		});

		Route::group(['prefix' => 'category'], function() {
	        /*=============================================
	        =            Category CMS            =
	        =============================================*/
	        
			    Route::get('master', 'CategoryController@index')->middleware('can:menu-post')->name('cms.post-category.master');
			    Route::get('form', 'CategoryController@create')->name('cms.post-category.create');
			    Route::post('form', 'CategoryController@store')->middleware('can:create-post')->name('cms.post-category.store');
			    Route::put('form', 'CategoryController@store')->name('cms.post-category.store');
			    Route::delete('form', 'CategoryController@destroy')->name('cms.post-category.delete');

			    Route::group(['prefix' => 'api'], function() {
				    Route::get('master', 'CategoryController@serviceMaster')->middleware('can:menu-post')->name('cms.post-category.service-master');
			    });
	        
	        /*=====  End of Category CMS  ======*/
		});

		Route::group(['prefix' => 'tag'], function() {
	        /*=============================================
	        =            Tag CMS            =
	        =============================================*/
	        
			    Route::get('master', 'TagController@index')->middleware('can:menu-post')->name('cms.post-tag.master');
			    Route::get('form', 'TagController@create')->name('cms.post-tag.create');
			    Route::post('form', 'TagController@store')->middleware('can:create-post')->name('cms.post-tag.store');
			    Route::put('form', 'TagController@store')->name('cms.post-tag.store');
			    Route::delete('form', 'TagController@destroy')->name('cms.post-tag.delete');

			    Route::group(['prefix' => 'api'], function() {
                   Route::get('master', 'TagController@serviceMaster')->middleware('can:menu-post')->name('cms.post-tag.service-master');
                });

	        /*=====  End of Tag CMS  ======*/
		});
        
	});
});