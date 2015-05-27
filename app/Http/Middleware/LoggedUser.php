<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\User;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class LoggedUser implements Middleware{
    
    public function handle($request, Closure $next) {

        // Incorrect data or user does not exist
        if( !$request->input('csrf_token') || !$request->input('user_id') 
            || !( $user = User::find( $request->input('user_id') ) ) ){
            return json_encode(array('status' => env('STATUS_AUTH_ERROR'), 'exception' => 'TokenMismatchException'));
        }        
        // Incorrect token for user
        $user_token = $user->remember_token;
        if( !isset( $user_token ) || $user_token !== $request->input('csrf_token') ){
            return json_encode(array('status' => env('STATUS_AUTH_ERROR'), 'exception' => 'TokenMismatchException'));
        }            
        return $next($request);
    }

}
