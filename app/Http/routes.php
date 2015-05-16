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
            Route::get('/{id}/groups/created/count', 'UsersController@countOwnGroups');
        });

        /**
            AUTH USERS ONLY ROUTES
        **/
        Route::group(['middleware' => 'loggedUser'], function(){

            Route::group(['prefix' => 'memberships'], function()
            {
                Route::post('/active', 'MembersController@getActiveMemberships');
                Route::post('/{role}/{status}', 'MembersController@getMemberships');
            });

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
                        Route::post( '/searches', 'QuestionsController@search' );
                    });   

                    // vvv @TOTEST
                    Route::group(['prefix' => '/members'], function(){
                        Route::post( '/', 'MembersController@apply' );
                        Route::post( '/leave', 'MembersController@leave');

                        Route::group(['middleware' => 'onlyGroupCreator'], function()
                        {
                            Route::post('/actives', 'MembersController@active');
                            Route::post('/bans', 'MembersController@ban');
                            Route::post('/denials', 'MembersController@deny');
                            Route::post('/teachers', 'MembersController@makeTeacher');
                            Route::post('/{role}/{status}/index', 'MembersController@index');
                        });
                    });
                    // ^^^ @TOTEST
                });
                
                               
            });

            /**
                A SPECIFIC QUESTION
            **/

            # Just Questions
            Route::group(['prefix' => 'questions/{questionId}', 'middleware' => 'questionExists'], function(){
                
                Route::put( '/', 'QuestionsController@update' );
                Route::post( '/delete', 'QuestionsController@delete' );

                # Its Answers
                Route::group(['prefix' => '/answers', 'middleware' => 'userCanSeeQuestion'], function(){
                   Route::post( '/', 'AnswersController@create' ); 
                   Route::post( '/list', 'AnswersController@index');

                   # It best Answer
                   Route::group(['prefix' => 'best'], function(){
                        Route::post( '/get', 'QuestionsController@getBestAnswer');
                        Route::post( '/set', 'QuestionsController@setBestAnswer');
                   });
                });
            });

            /**
                ANSWERS
            **/

            # Just Answers
            Route::group(['prefix' => 'answers/{answerId}', 'middleware' => 'answerExists'], function(){
                Route::group(['middleware' => 'userIsAnswerAuthor'], function(){
                    Route::put( '/', 'AnswersController@update' );
                    Route::post( '/delete', 'AnswersController@delete' );
                });

                # Its Votes
                Route::group(['prefix' => '/votes'], function(){
                    Route::post( '/set', 'VotesController@set');
                    Route::post( '/report', 'VotesController@getReport');
                });
            });
        });

        /**
            NOTIFICATIONS
        **/

        // vvv @TODO
        // Route::group(['prefix' => '/notifications'], function()
        // {
        //     Route::post('/report', 'UserController@getNotificationsReport');
        //     Route::post('/index', 'UsersController@getNotifications');
        // });
        // ^^^ @TODO
});


/*
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
*/