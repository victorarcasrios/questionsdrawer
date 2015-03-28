<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model{
    
    protected $table = 'Answer';
    protected $fillable = ['id_question', 'id_author', 'text'];

    public function question(){
    	return $this->belongsTo('App\Models\Question', 'id_question');
    }

    public function author(){
    	return $this->belongsTo('App\Models\User', 'id_author');
    }

}