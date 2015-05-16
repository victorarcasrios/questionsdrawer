<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model{

	protected $fillable = ['group_id', 'author_id', 'text'];

	/**
		Relations
	*/

	public function author(){
		return $this->belongsTo('App\Models\User', 'author_id');
	}

	public function group(){
		return $this->belongsTo('App\Models\Group', 'group_id');
	}    

	public function answers(){
		return $this->hasMany('App\Models\Answer', 'question_id');
	}

	public function bestAnswer()
	{
		return $this->hasOne('App\Models\Answer', 'id', 'best_answer_id');
	}

	/**
		Other getters
	*/

	// public static function answeredBy($user){
	// 	return self::whereHas('answers', function($query) use($user){
	// 		$query->where('id_author', '=', $user->id);
	// 	});
	// }

	// public static function notAnsweredBy($user){
	// 	return self::whereNotIn('id', self::answeredBy($user)->lists('id'));
	// }
}