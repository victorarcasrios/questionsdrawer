<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\User;
use App\Models\Group;
use DB;

/**
 * MembersController
 *
 * @author victor
 */
class MembersController extends Controller{

	/**
	 * Return all active memberships for a user
	 * @return json array [status, memberships]
	 */
	public function getActiveMemberships()
	{
		$user = User::find(Input::get('user_id'));
		$memberships = $user->members()
							->select(['groups.id', 'groups.name', 'role', 'status'])
							->join('groups', 'groups.id', '=', 'group_id')
							->where('status', '=', 'Active')
							->get();

		return json_encode(['status' => env('STATUS_OK'), 'memberships' => $memberships]);
	}

	/**
	 * Return all memberships for a user with the given role and status combination
	 * @param string $role
	 * @param string $status
	 * @return json array [status, memberships]
	 */
	public function getMemberships($role, $status)
	{
		$user = User::find(Input::get('user_id'));
		$memberships = $user->members()
							->select(['groups.id', 'groups.name'])
							->join('groups', 'groups.id', '=', 'group_id')
							->where('role', '=', $role)
							->where('status', '=', $status)
							->get();

		return json_encode(['status' => env('STATUS_OK'), 'memberships' => $memberships]);
	}

	/**
	 * Reply a not member application to the given group by creating a Demanded status student
	 * @param integer $groupId
	 * @return json array [status [exception]]
	 */
	public function apply($groupId)
	{
		$user = User::find(Input::get('user_id'));
		$query = DB::table('members')->where('user_id', '=', $user->id)->where('group_id', '=', $groupId);

		# If already exists a membership relationship of the user with the group returns a KO	
		if($query->exists()){
			$member = $query->first();
			return json_encode([
				'status' => env('STATUS_KO'), 
				'exception' => "Already{$member->status}{$member->role}"
			]);
		}
		else{ # Else creates it and return an OK
			DB::table('members')->insert([
				'group_id' => $groupId, 'user_id' => $user->id, 'role' => 'Student', 'status' => 'Demanded'
			]);
			return json_encode(['status' => env('STATUS_OK')]);
		}
	}

	/**
	 * Set the role value of an specific user to Teacher (and his status to Active)
	 * @param integer $groupId
	 * @return json array [status]
	 */
	public function makeTeacher($groupId)
	{
		DB::table('members')->where('group_id', '=', $groupId)
							->where('user_id', '=', Input::get('member_id'))
							->update(['role' => 'Teacher', 'status' => 'Active']);

		return json_encode(['status' => env('STATUS_OK')]);
	}

	/**
	 * Reply a member willing of leave the given group by removing him of the members list
	 * @param integer $groupId
	 * @return json array [status]
	 */
	public function leave($groupId)
	{
		DB::table('members')->where('group_id', '=', $groupId)
							->where('user_id', '=', Input::get('user_id'))
							->delete();
		return json_encode(['status' => env('STATUS_OK')]);
	}

	/**
	 * Set a member status to Active
	 * @param integer $groupId
	 * @return json array [status]
	 */
	public function active($groupId)
	{
		return $this->setStatus($groupId, 'Active');
	}

	/**
	 * Set a member status to Banned
	 * @param integer $groupId
	 * @return json array [status]
	 */
	public function ban($groupId)
	{
		return $this->setStatus($groupId, 'Banned');
	}

	/**
	 * Set a member status to Denied
	 * @param integer $groupId
	 * @return json array [status]
	 */
	public function deny($groupId)
	{
		return $this->setStatus($groupId, 'Denied');
	}

	/**
	 * Set the member status to the given value
	 * @param integer $groupId
	 * @param string $status
	 * @return json array [status, [exception]]
	 */
	private function setStatus($groupId, $status)
	{
		$member = User::find(Input::get('member_id'));

		if( !$member )
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'MemberNotFound']);

		DB::table('members')->where('group_id', '=', $groupId)
							->where('user_id', '=', $member->id)
							->update(['status' => $status]);

		return json_encode(['status' => env('STATUS_OK')]);
	}

	/**
	 * Return all the members of the group with the given role and status combination
	 * @param integer $groupId
	 * @param string $role
	 * @param string $status
	 * @return json array [status, members]
	 */
	public function index($groupId, $role, $status)
	{
		$group = Group::find($groupId);
		$members = DB::table('members')
						->select(['users.id', 'users.name', 'role', 'status'])
						->join('users', 'user_id', '=', 'users.id')
						->where('group_id', '=', $groupId)
						->where('role', '=', $role)
						->where('status', '=', $status)
						->get();

		return json_encode(['status' => env('STATUS_OK'), 'members' => $members]);
	}
}   
