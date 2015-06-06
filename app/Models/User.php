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

    public function votes()
    {
        return $this->hasMany('App\Models\Vote', 'author_id');
    }
    
    /**
    	Methods
    **/

    /**
     * Returns TRUE if this user is the creator of the given group, FALSE else
     * @param Group $group
     * @return boolean TRUE if is the creator, FALSE if not
     */
    public function isCreator(Group $group)
    {
        return $this->id == $group->creator_id;
    }

    /**
     * Returns TRUE if this user has the role teacher (active) in the given $group, FALSE else
     * @param Group $group
     * @return boolean TRUE if is a group teacher, FALSE if not
     */
    public function isTeacher(Group $group)
    {
        return Member::where('group_id', '=', $group->id)
                        ->where('user_id', '=', $this->id)
                        ->where('role', '=', 'Teacher')
                        ->where('status', '=', 'Active')
                        ->exists();
    }

    /**
     * Returns TRUE if the given question was posted by this user, else FALSE
     * @param Question $question
     * @return boolean TRUE if this is the author of the questions, FALSE if not
     */
    public function isQuestionAuthor(Question $question)
    {
        return $question->author_id === $this->id;
    }

    /**
     * Returns TRUE if the given answer was posted by this user, else FALSE
     * @param Answer $answer
     * @return boolean TRUE if this is the author of the answers, FALSE if not
     */
    public function isAnswerAuthor(Answer $answer)
    {
        return $answer->author_id === $this->id;
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
     * Return all the groups where the current user as creator or active teacher
     * @return a select query to retrieve array of Group objects
     */
    public function groupsWithMeAsStaff()
    {
        return $this->getGroupsAs('Teacher', 'Active')
                    ->orWhere('creator_id', '=', 'user_id')
                    ->select('groups.id', 'groups.name')->distinct()
                    ->get();
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

    /**
     * Returs a query selecting the answer of this user to the given question
     * @param Question $question
     * @return Eloquent query selecting the user's answer for this question
     */
    public function answerFor(Question $question){
        return Answer::where('author_id', '=', $this->id)
                        ->where('question_id', '=', $question->id);
    }

    /**
     * Returns true if exists a vote of this user for the given answer
     * @param Answer $answer
     * @return boolean TRUE if user has voted, FALSE if not
     */
    public function hasVoted(Answer $answer){
        return Vote::where('author_id', '=', $this->id)
                        ->where('answer_id', '=', $answer->id)
                        ->exists();
    }

}