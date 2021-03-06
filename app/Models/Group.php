<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model{
    
    protected $fillable = array('name', 'cretor_id');

    /**
        Relations
    */ 
    public function creator(){
    	return $this->belongsTo('App\Models\User', 'creator_id');
    }

    public function questions(){
        return $this->hasMany('App\Models\Question', 'group_id');        
    }

    public function members(){
        return $this->belongsToMany('App\Models\User', 'members', 'group_id', 'user_id');
    }

    /**
        Boolean return methods
    */

    /**
     * Return TRUE if this group has the given user, else FALSE
     * @param User $user to check membership
     * @return boolean TRUE if is member, FALSE else
     */
    public function hasMember(User $user){
        return $this->members()->where('id', '=', $user->id)->exists();
    }

    /**
     * Return TRUE if this group has the given user as active member, else FALSE
     * @param User $user to check active membership
     * @return boolean TRUE if is active member, FALSE else
     */
    public function hasActiveMember(User $user){
        return $this->members()
                  ->where('id', '=', $user->id)
                  ->where('status', '=', 'Active')
                  ->exists();
    }

    /**
     * Returns TRUE if the groups has a question with the given $text, FALSE if not
     * @param string $text text of the question
     * @param integer $except id of an existent question that can have the $text value as text
     * @return boolean TRUE if group has question, FALSE if not
     */
    public function hasQuestion($text, $except = false){
        $query = $this->questions()->where('text', '=', $text);

        if($except !== false)
            $query = $query->where('id', '<>', $except);

        return $query->exists();
    }

    /**
        Aditional GETTERS
    */
        
    /**
     * Returns a query that selects all the groups where the indicated user is not creator nor member
     * @param integer $userId id of the user
     * @return Eloquent/Fluent select query
     */
    public static function notRelatedTo($userId){
    	return self::select('id', 'name')
    				->whereNotIn('id', function($query) use($userId){
    					$query->from('groups')
		    					->select('id')
		    					->leftJoin('members', 'groups.id', '=', 'members.group_id')
		    					->where( function($query) use($userId){
		    						$query->where('members.user_id', '=', $userId)
		    								->orWhere('groups.creator_id', '=', $userId);
		    					});
    				});

  		// SQL: SELECT Group.id, Group.name
		// FROM Group
		// WHERE Group.id NOT IN 
		// (SELECT id FROM Group
		// LEFT JOIN Membership ON (Group.id = Membership.id_group)
		// WHERE Membership.id_user = 23 OR Group.id_creator = 23)
    }
}