<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\User;
use DB;

/**
 * MembersController
 *
 * @author victor
 */
class MembersController extends Controller{

	/**
	 *	Return all memberships for a user
	 */
	public function myMemberships()
	{
		$user = User::find(Input::get('user_id'));
		$memberships = $user->members()
							->select(['group.id', 'groups.name', 'role', 'status'])
							->join('groups', 'groups.id', '=', 'group_id')
							->get();

		return json_encode(['status' => env('STATUS_OK'), 'memberships' => $memberships]);
	}

	/**
	 *	Return all memberships for a user with the given role and status combination
	 */
	public function getMemberships($role, $status)
	{
		$user = User::find(Input::get('user_id'));
		$memberships = $user->members()
							->select(['group.id', 'groups.name', 'role', 'status'])
							->join('groups', 'groups.id', '=', 'group_id')
							->where('role', '=', $role)
							->where('status', '=', $status)
							->get();

		return json_encode(['status' => env('STATUS_OK'), 'memberships' => $memberships]);
	}

	/**
	 * Reply a not member application to the given group by creating a Demanded status member
	 */
	public function apply($groupId)
	{
		DB::table('members')->where('group_id', '=', $groupId)
							->where('user_id', '=', Input::get('user_id'))
							->insert(['status' => 'Demanded']);
		return json_encode(['status' => env('STATUS_OK')]);
	}

	/**
	 * Reply a member willing of leave the given group by removing him of the members list
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
	 */
	public function active($groupId)
	{
		return $this->setStatus($groupId, 'Active');
	}

	/**
	 * Set a member status to Banned
	 */
	public function ban($groupId)
	{
		return $this->setStatus($groupId, 'Banned');
	}

	/**
	 * Set a member status to Denied
	 */
	public function deny($groupId)
	{
		return $this->setStatus($groupId, 'Denied');
	}

	/**
	 * Set the member status to the given value
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
	 */
	public function index($groupId, $role, $status)
	{
		$group = Group::find($groupId);
		$members = $group->members()
						->select(['users.id', 'users.name', 'role', 'status'])
						->join('users', 'users_id', '=', 'user.id')
						->where('role', '=', $role)
						->where('status', '=', $status)
						->get();

		return json_encode(['status' => env('STATUS_OK'), 'members' => $members]);
	}
}   
