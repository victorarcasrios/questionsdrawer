<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\Vote;
use App\Models\User;
use App\Models\Answer;
use DB;

/**
 * VotesController
 *
 * @author victor
 */
class VotesController extends Controller{

	/**
	 * Sets a vote of a user for the answer with the given answer id
	 * @param integer $answerId
	 * @return json array [status]
	 */
	public function set($answerId){
		$user = User::find(Input::get('user_id'));
		$answer = Answer::find($answerId);
		$selectedVote = Input::get('vote');

		if($this->isNotValidVote($selectedVote))
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'InvalidVoteType']);

		$this->setVote($user, $answer, $selectedVote);
		
		return json_encode(['status' => env('STATUS_OK')]);
	}

	/**
	 * Checks if the vote exists, if exists calls updateVote(), else calls createVote()
	 * @param User $user that votes
	 * @param Answer $answer to vote
	 * @param string $vote [Postive/Negative]
	 */
	private function setVote(User $user, Answer $answer, $vote)
	{
		($user->hasVoted($answer)) 
			? $this->updateVote($user, $answer, $vote) 
			: $this->createVote($user, $answer, $vote);
	}

	/**
	 * Updates the vote with the given (user and $answer) ids to the given $vote value
	 * @param User $user that votes
	 * @param Answer $answer to vote
	 * @param string $vote [Postive/Negative]
	 */
	private function updateVote(User $user, Answer $answer, $vote)
	{
		DB::table('votes')->where('author_id', '=', $user->id)
						->where('answer_id', '=', $answer->id)
						->update(['type' => $vote]);
	}

	/**
	 * Creates a vote with the given (user and $answer) ids and the given $vote value
	 * @param User $user that votes
	 * @param Answer $answer to vote
	 * @param string $vote [Postive/Negative]
	 */
	private function createVote(User $user, Answer $answer, $vote)
	{
		DB::table('votes')->insert([
			'author_id' => $user->id, 'answer_id' => $answer->id, 'type' => $vote
		]);
	}

	/**
	 * Checks if the given $vote value is a valid vote type
	 * @param string $vote [Postive/Negative]
	 * @return boolean TRUE if is NOT valid, TRUE either
	 */
	private function isNotValidVote($vote)
	{
		$validator = Validator::make(
			['type' => $vote],
			['type' => 'required|in:Positive,Negative']
		);
		return $validator->fails();
	}

	/**
	 * Returns the report of votes of the answer with the given answer id
	 * @param integer $answerId
	 * @return json array [status, votes => [Negative, Positive]]
	 */
	public function getReport($answerId)
	{
		$answer = Answer::find($answerId);

		return ([
			'status' => env('STATUS_OK'), 
			'votes' => $answer->votes()
							->select('type', \DB::raw('count(*) as count'))
							->groupBy('type')
							->lists('count', 'type')
		]);
	}
}   
