<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model{

	protected $fillable = ['group_id', 'author_id', 'text'];

	/**
		Relations
	*/

	public function author(){
		return $this->belongsTo('App\Models\User', 'id_author');
	}

	public function group(){
		return $this->belongsTo('App\Models\Group', 'id_group');
	}    

	public function answers(){
		return $this->hasMany('App\Models\Answer', 'id_question');
	}

	/**
		Other getters
	*/

	public static function answeredBy($user){
		return self::whereHas('answers', function($query) use($user){
			$query->where('id_author', '=', $user->id);
		});
	}

	public static function notAnsweredBy($user){
		return self::whereNotIn('id', self::answeredBy($user)->lists('id'));
	}
}