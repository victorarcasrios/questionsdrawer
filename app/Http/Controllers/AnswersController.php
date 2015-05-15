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

	private function isNotValidAnswerText($text){
		$validator = Validator::make(
			['text' => $text],
			['text' => 'required|min:10|max:500']
		);
		return $validator->fails();
	}

	private function createAnswer($question, $user, $text){
		$answer = new Answer([
			"question_id" => $question->id,
			"author_id" => $user->id,
			"text" => $text
			]);
		$answer->push();		
	}

}