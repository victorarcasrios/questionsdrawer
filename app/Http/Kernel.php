<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		//'App\Http\Middleware\VerifyCsrfToken',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		//'auth' => 'App\Http\Middleware\Authenticate',
		//'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		//'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'loggedUser' => 'App\Http\Middleware\LoggedUser',
        'userExists' => 'App\Http\Middleware\UserExists',
        'groupExists' => 'App\Http\Middleware\GroupExists',
        'questionExists' => 'App\Http\Middleware\QuestionExists',
        'userCanSeeQuestion' => 'App\Http\Middleware\UserCanSeeQuestion',
        'answerExists' => 'App\Http\Middleware\AnswerExists',
        'userIsAnswerAuthor' => 'App\Http\Middleware\UserIsAnswerAuthor',
        'onlyGroupCreator' => 'App\Http\Middleware\OnlyGroupCreator'
	];

}
