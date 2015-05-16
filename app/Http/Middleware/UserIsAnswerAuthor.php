<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\User;
use App\Models\Answer;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class UserIsAnswerAuthor implements Middleware{
    
    public function handle($request, Closure $next) {
        $user = User::find($request->user_id);
        $answer = Answer::find($request->answerId);

		$userIsNotAuthor = ! $user->isAnswerAuthor($answer);

		if($userIsNotAuthor)
			return json_encode([
				'status' => env('STATUS_ROUTING_ERROR'), 
				'exception' => 'OnlyAnswerAuthorAllowed'
			]);
                    
        return $next($request);
    }

}