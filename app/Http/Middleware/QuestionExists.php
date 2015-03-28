<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\Question;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class QuestionExists implements Middleware{
    
    public function handle($request, Closure $next) {
        $question = Question::find($request->questionId);
        $questionNotFound = !$question;

        if( $questionNotFound )
            return json_encode(['success' => '0', 'exception' => 'QuestionNotFound']);
                    
        return $next($request);
    }

}
