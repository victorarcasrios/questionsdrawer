<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\User;
use App\Models\Group;
use App\Models\Question;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class QuestionsController extends Controller{

    const ANSWERED = 1;
    const NOT_ANSWERED = 2;
    
    public function listAll(){
        $user = User::find(23);
        return json_encode(array("success" => 1, 'questions' => Question::get()));
    }

	/**
		Creation
	*/

    public function create($groupId){
    	$userId = Input::get('user_id');
    	$text = Input::get('question_text');
    	$group = Group::find($groupId);

    	$userDoesNotHavePermission = $group->creator->id != $userId;
		if($userDoesNotHavePermission) return json_encode(['success' => 0, 'exception' => 'UserDoesNotHavePermission']);
    	
    	if( $this->isNotValidQuestionText($text) ) 
    		return json_encode(['success' => 0, 'exception' => 'InvalidQuestionText']);

    	if( $group->hasQuestion($text) )
    		return json_encode(['success' => 0, 'exception' => 'GroupAlreadyHasQuestion']);

    	return json_encode(['success' => 1, 
    		'id' => $this->createItAndReturnHisId($userId, $groupId, $text)]);
    }   

    private function isNotValidQuestionText($text){
    	$validator = Validator::make(
    		['text' => $text],
    		['text' => 'required|min:10|max:300']
    	);
    	return $validator->fails();
    } 

    private function createItAndReturnHisId($userId, $groupId, $text){
    	$question = new Question([
    		'id_author' => $userId, 
    		'id_group' => $groupId, 
    		'text' => $text
    		]);
    	$question->save();
    	return $question->id;
    }

    /**
        Searches
    */

    public function search($groupId){
        $user = User::find(Input::get('user_id'));
        $group = Group::find($groupId);
        
        $questionStatus = Input::get('question_status');
        $searchString = Input::get('search_string');
        
        $notSearchString = !$searchString;
        $userIsNotCreator = $group->creator->id != $user->id;
        $userIsNotMember = !$group->hasMember($user);
        $userDoesNotHavePermission = $userIsNotCreator && $userIsNotMember;

        if($userDoesNotHavePermission)
            return json_encode(['success' => 0, 'exception' => 'UserDoesNotHavePermission']);
        
        $questions = $this->getQuestionsToSearch($user, $group, $questionStatus);
        
        if($notSearchString){
            $questions = $questions->orderBy('updated_at', 'desc')->get();
            return json_encode(['success' => 1, 'questions' => $questions]);
        }

        $questions = $this->searchQuestions($questions, $searchString);
        return json_encode(['success' => 1, 'questions' => $questions]);
    }

    private function getQuestionsToSearch($user, $group, $status){
        switch ($status) {
            case null: default: 
                $questions = $group->questions();    
                break;
            case self::ANSWERED:
                $questions = $this->getAnsweredQuestions($user, $group);
                break;
            case self::NOT_ANSWERED:
                $answeredQuestions = $this->getAnsweredQuestions($user, $group);
                $questions = $this->getNotAnsweredQuestions($group, $answeredQuestions);
                break;
        }
        return $questions->select('id', 'text');
    }

    private function getAnsweredQuestions($user, $group){
        return $group->questions()->whereHas(
                    'answers', function($query) use($user){
                        $query->where('id_author', '=', $user->id);
                });
    }

    private function getNotAnsweredQuestions($group, $answeredQuestions){
        return $group->questions()->whereNotIn('id', $answeredQuestions->lists('id'));
    }

    private function searchQuestions($questions, $searchString){
        $words = explode(' ', $searchString);
        $questions = $questions->where('text', 'LIKE', "%{$words[0]}%");
        for ($i = 1; $i < sizeof($words); $i++) {
           $questions = $questions->orWhere('text', 'LIKE', "%{$words[$i]}%"); 
        }
            
        return $questions->orderBy('updated_at', 'desc')->get();
    }
}
