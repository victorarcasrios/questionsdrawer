<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\User;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class UserExists implements Middleware{
    
    public function handle($request, Closure $next) {
        $user = User::find($request->id);
        $userNotFound = !$user;
        
        if( $userNotFound )
            return json_encode(['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'UserNotFound']);
                    
        return $next($request);
    }

}