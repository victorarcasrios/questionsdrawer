<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model{

    protected $fillable = array('name', 'email', 'password');

    /**
    	Relations
    **/

    public function roles(){
        return $this->belongsToMany('App\Models\Role', 'Membership', 'id_user', 'id_role');
    }

    public function groups(){
        return $this->belongsToMany('App\Models\Group', 'Membership', 'id_user', 'id_group');
    }

    public function createdGroups(){
        return $this->hasMany('App\Models\Group', 'id_creator');
    }

    public function questions(){
        return $this->hasMany('App\Models\Question', 'id_author');
    }

    public function answers(){
        return $this->hasMany('App\Models\Answer', 'id_author');
    }
    
    /**
    	Methods
    **/

    public function canCreateGroup(){
    	$max_groups = intval( env('MAX_GROUPS_CREATED_BY_USER') );
    	$groups_count = $this->createdGroups()->count();

    	return ( $groups_count < $max_groups );
    }

    public function answerFor(Question $question){
        return Answer::where('id_author', '=', $this->id)
                        ->where('id_question', '=', $question->id);
    }

    // public function  getGroupsAs($membership){
    //     return $this->groups()->where('Role.name', '=', $membership);
    // }

}