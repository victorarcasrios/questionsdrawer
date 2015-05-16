<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\User;
use App\Models\Group;
use App\Models\Question;
use App\Models\Answer;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class QuestionsController extends Controller{

	/**
		Creation, Edition and Deletion
	*/

    /**
     * Response to POST route creating a new question for a group if the user is the creator or a teacher
     * @param $groupId id of the group
     * @return json [status, questionId]
     */
    public function create($groupId){
    	$user = User::find( Input::get('user_id') );
    	$text = Input::get('question_text');
    	$group = Group::find($groupId);
        $userCanNotCreate = !$user->isCreator($group) && !$user->isTeacher($group);

        # KOs
		if($userCanNotCreate) 
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission']);    	
    	if( $this->isNotValidQuestionText($text) ) 
    		return json_encode(['status' => env('STATUS_KO'), 'exception' => 'InvalidQuestionText']);
    	if( $group->hasQuestion($text) )
    		return json_encode(['status' => env('STATUS_KO'), 'exception' => 'GroupAlreadyHasQuestion']);

        # OK
    	return json_encode(['status' => env('STATUS_OK'), 
    		'id' => $this->createItAndReturnHisId($user->id, $groupId, $text)]);
    }   

    /**
     * Use Validator to validate the $text of the question and returns true if is not valid
     * @param string $text the text of the question
     * @return boolean TRUE if OK (is not valid), FALSE if KO (is valid)
     */
    private function isNotValidQuestionText($text){
    	$validator = Validator::make(
    		['text' => $text],
    		['text' => 'required|min:10|max:300']
    	);
    	return $validator->fails();
    } 
    /**
     * Creates a new questions for the specified group and returns its id
     * @param integer $userId author id
     * @param integer $groupId group id
     * @param string $text text
     * @return integer new question id
     */
    private function createItAndReturnHisId($userId, $groupId, $text){
    	$question = new Question([
    		'author_id' => $userId, 
    		'group_id' => $groupId, 
    		'text' => $text
    		]);
    	$question->save();
    	return $question->id;
    }

    /**
     * Updates the question with the given $questionId with the given $questionText
     * @param integer $questionId id of the question to be updated
     * @return json [status, [exception]]
     */
    public function update($questionId)
    {
        $user = User::find(Input::get('user_id'));
        $question = Question::find($questionId);
        $questionText = Input::get('question_text');
        $userCanNotUpdate = ! $user->isCreator($question->group) && ! $user->isQuestionAuthor($question);

        # KOs
        if($userCanNotUpdate)
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission']);
        if($this->isNotValidQuestionText($questionText))
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'InvalidQuestionText']);
        if($question->group->hasQuestion($questionText, $question->id))
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'GroupAlreadyHasQuestion']);

        # OK
        $question->text = $questionText;
        $question->save();
        return json_encode(['status' => env('STATUS_OK')]);
    }

    /**
     * Deletes the question with the given question id
     * @param integer $questionId the id of the question to be deleted
     * @return json [status, [exception]]
     */
    public function delete($questionId)
    {
        $user = User::find(Input::get('user_id'));
        $question = Question::find($questionId);
        $userIsNotCreator = ! $user->isCreator($question->group);
        $userIsNotAuthor = ! $user->isQuestionAuthor($question);
        $userCanNotDelete = $userIsNotCreator && $userIsNotAuthor;

        if($userCanNotDelete)
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission']);

        $question->delete();
        return json_encode(['status' => env('STATUS_OK')]);
    }

    /**
        Searches
    */

    /**
     * Searches in the group with the given group id for questions 
     * with the given status [all/answered/not_answered] and text
     * @param integer $groupId id of the group to be searched
     * @return json [status, [questions/exception]]
     */
    public function search($groupId){
        $user = User::find(Input::get('user_id'));
        $group = Group::find($groupId);
        $questionStatus = Input::get('question_status');
        $searchString = Input::get('search_string'); // Can be 0, 1 or 2 (all, answered, not_answered)
        
        $notSearchString = !$searchString;
        $userIsNotCreator = $group->creator->id != $user->id;
        $userIsNotMember = !$group->hasMember($user);
        $userDoesNotHavePermission = $userIsNotCreator && $userIsNotMember;

        if($userDoesNotHavePermission)
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission']);
        
        $questions = $this->getQuestionsToSearch($user, $group, $questionStatus);
        
        if($notSearchString){
            $questions = $questions->orderBy('updated_at', 'desc')->get();
            return json_encode(['status' => env('STATUS_OK'), 'questions' => $questions]);
        }

        $questions = $this->searchQuestions($questions, $searchString);
        return json_encode(['status' => env('STATUS_OK'), 'questions' => $questions]);
    }

    /**
     * Returns all the questions to search in
     * @param User $user user that makes the search
     * @param Group $group group to be searched
     * @param integer $status status of the question [all/answered/not_answered]
     * @return array of Question objects
     */
    private function getQuestionsToSearch($user, $group, $status){
        switch ($status) {
            case env('ALL_QUESTIONS'): case null: default: 
                $questions = $group->questions();    
                break;
            case env('ANSWERED_QUESTIONS'):
                # $questions = $group->questions->answeredBy($user);
                $questions = $this->getAnsweredQuestions($user, $group);
                break;
            case env('NOT_ANSWERED_QUESTIONS'):
                $answeredQuestions = $this->getAnsweredQuestions($user, $group);
                $questions = $this->getNotAnsweredQuestions($group, $answeredQuestions);
                break;
        }
        return $questions->select('id', 'text');
    }

    /**
     * Return a select Eloquent query with all the questions of the indicated
     * group answered by the user
     * @param User $user the user that answered the questions
     * @param Group $group the group of the questions
     * @return Eloquent select query
     */
    private function getAnsweredQuestions($user, $group){
        return $group->questions()->whereHas(
                    'answers', function($query) use($user){
                        $query->where('author_id', '=', $user->id);
                });
    }

    /**
     * Return all the questions in the indicated group that are not in the given array
     * @param Group $group the group of the questions
     * @param array of Question objects
     * @return Eloquent select query
     */
    private function getNotAnsweredQuestions($group, $answeredQuestions){
        return $group->questions()->whereNotIn('id', $answeredQuestions->lists('id'));
    }

    /**
     * Get an array of questions and return another one with the questions 
     * that contains at least one word of the given ones 
     * @param array of Question objects $questions questions to search the words in
     * @param array of strings $searchString words to search in the questions
     * @return array of Question objects questions that contains the given words
     */
    private function searchQuestions($questions, $searchString){
        $words = explode(' ', $searchString);
        $questions = $questions->where('text', 'LIKE', "%{$words[0]}%");
        for ($i = 1; $i < sizeof($words); $i++) {
           $questions = $questions->orWhere('text', 'LIKE', "%{$words[$i]}%"); 
        }
            
        return $questions->orderBy('updated_at', 'desc')->get();
    }

    /**
        ANSWERS
    **/

    /**
     * Retrieve the answer marked as best from all the answers of the question with the given id
     * @param integer $questionId
     * @return json [status, answer_id]
     */
    public function getBestAnswer($questionId)
    {
        $question = Question::find($questionId);

        $bestAnswerId = ($question->bestAnswer) ? $question->bestAnswer->id : NULL;

        return json_encode(['status' => env('STATUS_OK'), 'answer_id' => $bestAnswerId]);
    }

    /**
     * Mark the indicated answer as the best for the question with the given question id
     * @param integer $questionId
     * @return json [status, [exception]]
     */
    public function setBestAnswer($questionId)
    {
        $user = User::find(Input::get('user_id'));
        $question = Question::find($questionId);
        $selectedAnswer = Answer::find(Input::get('answer_id'));
        $answerNotFound = !$selectedAnswer;
        $canNotSetIt = !$user->isQuestionAuthor($question) && !$user->isCreator($question->group);
        
        if($answerNotFound)
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'AnswerNotFound']);
        if($canNotSetIt)
            return json_encode(['status' => env('STATUS_KO'), 'exception' => 'OnlyAuthorOrCreatorCanSetIt']);

        $question->best_answer_id = $selectedAnswer->id;
        $question->save();
        return json_encode(['status' => env('STATUS_OK')]);
    }
}
