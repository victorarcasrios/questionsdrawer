<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model{
    
    protected $fillable = array('name', 'cretor_id');

    /**
        Relations
    */ 
    public function creator(){
    	return $this->belongsTo('App\Models\User', 'id_creator');
    }

    public function questions(){
        return $this->hasMany('App\Models\Question', 'id_group');        
    }

    public function members(){
        return $this->belongsToMany('App\Models\User', 'Membership', 'id_group', 'id_user');
    }

    /**
        Aditional GETTERS
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

    /**
        Boolean return methods
    */

    public function hasMember($user){
        return $this->members()->where('id', '=', $user->id)->exists();
    }

    public function hasQuestion($text){
        return $this->questions()->where('text', '=', $text)->exists();
    }
}