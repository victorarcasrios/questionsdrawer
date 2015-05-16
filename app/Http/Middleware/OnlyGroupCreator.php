<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\User;
use App\Models\Group;

/**
 * Only group creator is allowed to pass through this interceptor
 *
 * @author victor
 */
class OnlyGroupCreator implements Middleware{
    
    public function handle($request, Closure $next) {
        $user = User::find($request->user_id);
        $group = Group::find($request->groupId);

		$userIsNotCreator = ! $user->isCreator($group);

		if($userIsNotCreator)
			return json_encode([
				'status' => env('STATUS_ROUTING_ERROR'), 
				'exception' => 'OnlyGroupCreatorAllowed'
			]);
                    
        return $next($request);
    }

}