<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model{

	protected $fillable = ['group_id', 'user_id', 'role', 'status'];
}