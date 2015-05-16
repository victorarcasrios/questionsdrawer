<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model{
    
    protected $fillable = ['question_id', 'author_id', 'text'];

    public function question(){
    	return $this->belongsTo('App\Models\Question', 'question_id');
    }

    public function author(){
    	return $this->belongsTo('App\Models\User', 'author_id');
    }

    public function votes()
    {
    	return $this->hasMany('App\Models\Vote', 'answer_id');
    }

}