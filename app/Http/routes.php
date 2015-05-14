<?php

use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::group(['prefix' => 'api'], function(){    
        Route::get( '/', function(){ 
            return Redirect::route('users');             
        });
        
        /**
            USERS MANAGEMENT ROUTES
        **/
        Route::group(['prefix' => 'users'], function(){
            // Debugging routes
            Route::get( '/', ['as' => 'users', 'uses' => 'UsersController@listAll'] );
            // END 

            // Auth routes
            Route::post( '/', 'UsersController@signup' );
            Route::post( '/signin', 'UsersController@signin' );
            Route::post( '/signout', ['middleware' => 'loggedUser', 'uses' => 'UsersController@signout'] );
            Route::post( '/check', 'UsersController@isLoggedUser');

            // Other routes
            Route::group(['prefix' => '/{id}', 'middleware' => 'userExists'], function(){
                Route::get('/', 'UsersController@get');
                Route::get('/roles', 'UsersController@getRolesFor');
                Route::get('/statuses', 'UsersController@getStatusesFor');
            });
            // Route::get('/{id}/groups/created/count', 'UsersController@countOwnGroups');
            // Route::get('/{id}/groups/', 'UsersController@getRelatedGroups');
            // Route::get('/{id}/{membership}/groups', 'GroupsController@getAllForMember');
        });

        /**
            INFO ROUTES (No auth required)
        **/

        Route::group(['prefix' => 'groups'], function(){
            Route::get( '/', 'GroupsController@getAll' );
            
        //     Route::group( ['prefix' => '/{groupId}', 'middleware' => 'groupExists'], function(){
        //         Route::get( '/', 'GroupsController@get');     
        //     });            
        // });

        // Route::group(['prefix' => 'questions'], function(){
        //     Route::get( '/', 'QuestionsController@listAll' );
        });

        /**
            AUTH USERS ONLY ROUTES
        **/
        Route::group(['middleware' => 'loggedUser'], function(){

            /**
                GROUPS
            **/
            Route::group(['prefix' => 'groups'], function(){
                Route::post( '/searches', 'GroupsController@search' );
                Route::post( '/', 'GroupsController@create' );

                # A Group
                Route::group(['prefix' => '/{groupId}', 'middleware' => 'groupExists'], function(){
                    Route::put( '/', 'GroupsController@update' );
                    Route::post( '/delete', 'GroupsController@delete' );

                    # Its Questions
                    Route::group(['prefix' => '/questions'], function(){
                        Route::post( '/', 'QuestionsController@create' );
                    // Route::post( '/searches', 'QuestionsController@search' );
                });     
                });
                
                               
            });

            ## Just Questions
            // Route::group(['prefix' => 'questions'], function(){
            //     ## Answers of a specific question
            //     Route::group(['prefix' => '{questionId}/answers', 'middleware' => 'questionExists'], function(){
            //        Route::post( '/', 'AnswersController@create' ); 
            //     });
            // });
        });
});


/*
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
*/