<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\User;
use App\Models\Question;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class UserCanSeeQuestion implements Middleware{
    
    public function handle($request, Closure $next) {
        $user = User::find($request->user_id);
        $question = Question::find($request->questionId);

		$userIsNotGroupMember = !$question->group->hasActiveMember($user);
		$userIsNotGroupCreator = !$user->isCreator($question->group);
		$userIsNotAllowedToPost = $userIsNotGroupMember AND $userIsNotGroupCreator;

		if($userIsNotAllowedToPost)
			return json_encode([
				'status' => env('STATUS_ROUTING_ERROR'), 
				'exception' => 'NotMemberUsersNotAllowed'
			]);
                    
        return $next($request);
    }

}