<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

use App\Models\Group;

/**
 * Description of LoggedUser
 *
 * @author victor
 */
class GroupExists implements Middleware{
    
    public function handle($request, Closure $next) {
        $group = Group::find($request->groupId);
        $groupNotFound = !$group;

        if( $groupNotFound )
            return json_encode(['success' => '0', 'exception' => 'GroupNotFound']);
                    
        return $next($request);
    }

}
