<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model{

    protected $fillable = array('name', 'email', 'password');

    /**
    	Relations
    **/

    public function members()
    {
        return $this->hasMany('App\Models\Member', 'user_id');
    }

    public function groups(){
        return Group::join('members', 'members.group_id', '=', 'groups.id')
                    ->where('members.user_id', '=', $this->id);
    }

    public function createdGroups(){
        return $this->hasMany('App\Models\Group', 'creator_id');
    }

    public function roles(){
        return $this->members()->select(\DB::raw('DISTINCT(role)'));
    }

    public function statuses()
    {
        return $this->members()->select(\DB::raw('DISTINCT(status)'));
    }

    // public function questions(){
    //     return $this->hasMany('App\Models\Question', 'author_id');
    // }

    // public function answers(){
    //     return $this->hasMany('App\Models\Answer', 'author_id');
    // }
    
    /**
    	Methods
    **/

    public function  getGroupsAs($role){
        return $this->groups()
                    ->where('role', '=', $role)
                    ->where('status', '=', 'Active');
    }

    public function canCreateGroup(){
    	$max_groups = intval( env('MAX_GROUPS_CREATED_BY_USER') );
    	$groups_count = $this->createdGroups()->count();

    	return ( $groups_count < $max_groups );
    }

    // public function answerFor(Question $question){
    //     return Answer::where('id_author', '=', $this->id)
    //                     ->where('id_question', '=', $question->id);
    // }

}