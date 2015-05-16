<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\Answer;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class AnswerExists implements Middleware{
    
    public function handle($request, Closure $next) {
        $answer = Answer::find($request->answerId);
        $answerNotFound = !$answer;

        if( $answerNotFound )
            return json_encode(['status' => env('STATUS_ROUTING_ERROR'), 'exception' => 'AnswerNotFound']);
                    
        return $next($request);
    }

}
