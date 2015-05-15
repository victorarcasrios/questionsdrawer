<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AnswersController extends Controller{

	/**
	 * Creates a new answer for the question with the given $questionId
	 * @param integer $questionId
	 * @return json [status, [exception]]
	 */
	public function create($questionId){
		$user = User::find(Input::get('user_id'));
		$question = Question::find($questionId);
		$text = Input::get('answer_text');

		$userHasAlreadyAnswered = $user->answerFor($question)->exists();

		if($userHasAlreadyAnswered)
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserHasAlreadyAnswered']);
		if($this->isNotValidAnswerText($text))
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'InvalidAnswerText']);

		$this->createAnswer($question, $user, $text);
		return json_encode(['status' => env('STATUS_OK')]);
	}

	/**
	 * Uses \Validator to check if $text is a valid answer text
	 * @param string $text
	 * @return boolean TRUE if is not a valid answer test, else FALSE
	 */
	private function isNotValidAnswerText($text){
		$validator = Validator::make(
			['text' => $text],
			['text' => 'required|min:10|max:500']
		);
		return $validator->fails();
	}

	/**
	 * Creates a new answer with the given params as its data
	 * @param Question $question to be answered
	 * @param User $user to answered it
	 * @param string $text of the answer
	*/
	private function createAnswer(Question $question, User $user, $text){
		$answer = new Answer([
			"question_id" => $question->id,
			"author_id" => $user->id,
			"text" => $text
			]);
		$answer->push();		
	}

	/**
	 * Returns a list of the answers of the Question with the given $questionId
	 * @param integer $questionId
	 * @return array of Answer objects related to the question
	 */
	public function index($questionId)
	{
		$user = User::find(Input::get('user_id'));
		$question = Question::find($questionId);

		$answers = $question->answers()
					->select('name as author_name', 'answers.id', 'text', 'answers.created_at', 'answers.updated_at')
					->join('users', 'author_id', '=', 'users.id')
					->orderBy('created_at', 'ASC')->get();

		return json_encode([
			'status' => env('STATUS_OK'), 
			'answers' => $answers
		]);
	}

}