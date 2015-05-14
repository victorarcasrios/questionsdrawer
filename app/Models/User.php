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

    public function questions(){
        return $this->hasMany('App\Models\Question', 'author_id');
    }

    public function answers(){
        return $this->hasMany('App\Models\Answer', 'author_id');
    }
    
    /**
    	Methods
    **/

    /**
     * Returns TRUE if this user is the creator of the given group, FALSE else
     * @param Group $group
     * @return boolean TRUE if is the creator, FALSE if not
     */
    public function isCreator($group)
    {
        return $this->id == $group->creator_id;
    }

    public function isTeacher($group)
    {
        return Member::where('group_id', '=', $group->id)
                        ->where('user_id', '=', $this->id)
                        ->where('role', '=', 'Teacher')
                        ->where('status', '=', 'Active')
                        ->exists();
    }

    /**
     * Return a query that selects all groups where this user have the given role and status as member
     * @param string $role [Teacher, Student]
     * @param string $status [Active, Demanded, Denied, Banned]
     * @return a select query with all the groups with the user as member with those $role and $status
     */
    public function  getGroupsAs($role, $status){
        return $this->groups()
                    ->where('role', '=', $role)
                    ->where('status', '=', $status);
    }

    /**
     * Returns TRUE if the number of groups created by the user is under the limit, 
     * FALSE if is equal or higher
     * @return TRUE if can create more, FALSE if not
     */
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