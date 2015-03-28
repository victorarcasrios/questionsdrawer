<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;

use App\Models\User;
use App\Models\Group;
use App\Models\Role;

/**
 * GroupsController
 *
 * @author victor
 */
class GroupsController extends Controller{

	const CREATOR = 'creator';
	const NOT_MEMBER = 'not_member';

	/**
		Basic Retrieving data
	**/
	public function get($id){
		$group = Group::find($id);
		
		return json_encode([ 
			'success' => 1,
			'group_id' => $group->id,
			'group_name' => $group->name,
			'creator_id' => $group->creator->id,
			'creator_name' => $group->creator->name
			]);
	}

	// NOT USED BY THE APP. PLEASE BE CAREFUL, NOT TESTED
	public function getAll(){
		$groups = Group::get()->lists('name', 'id');
		return json_encode(['success' => 1, "groups" => $groups]);		
	}
	// NOT USED BY THE APP. PLEASE BE CAREFUL, NOT TESTED
	public function getAllForMember($id, $roleName){
		$user = User::find($id);

		if( !$user ) return json_encode(['success' => 0, 'exception' => 'UserNotFound']);

		$roleId = Role::where( 'name', '=', $roleName )->pluck( 'id' );
		if( !$roleId ) return json_encode(['success' => 0, 'exception' => 'RoleNotFound']);

		$groups = $user->groups()->select('id', 'name')->where('id_role', '=', $roleId)->get();
		return json_encode(['success' => 1, 'groups' => $groups]);
	}

	/**
		Advanced retrieving data (searches)
	**/
	public function search(){
		$userId = Input::get('user_id');
		$roleName = Input::get('role_name');
		$search = Input::get('search_string');
		
		$user =  User::find($userId);
		$names = (isset($search)) ? explode(' ', $search) : false;
		$notTextToSearch = !$names;

		$groups = $this->getGroupsToSearch($user, $roleName);
				
		if( $notTextToSearch ){
			return json_encode([
				'success' => 1, 'groups' => $this->returnCurrentGroupsOrAllIfAny($groups)
				]);
		}
		
		return json_encode([
			'success' => 1, 'groups' => $this->executeSearch($groups, $names)->get()]);
	}

	private function executeSearch($groups, $names){
		if(!$groups)
			$groups = Group::select('Group.id', 'Group.name')->where('Group.name', 'LIKE', "%{$names[0]}%");
		else
			$groups = $groups->select('Group.id', 'Group.name')->where('Group.name', 'LIKE', "%{$names[0]}%");

		for( $i = 1; $i < sizeof($names); $i++ ){
			$groups = $groups->orWhere('Group.name', 'LIKE', "%{$names[$i]}%");
		}
		return $groups;
	}

	private function getGroupsToSearch($user, $roleName){
		switch($roleName){			
			case null:
				return false;
			case self::CREATOR:
				return $user->createdGroups();
			case self::NOT_MEMBER:
				return Group::notRelatedTo($user->id);
			default:
				$roleId = Role::where('name', '=', $roleName)->pluck('id');
				return $user->groups()->where('id_role', '=', $roleId);
		}
	}

	private function returnCurrentGroupsOrAllIfAny($groups){
		if(!$groups)
			return Group::select('Group.id', 'Group.name')->get();
		else
			$groups->select('Group.id', 'Group.name')->get();
	}


	/**
		Group creation
	**/
	public function create(){
		$groupName = Input::get('group_name');
		$user = User::find(Input::get('user_id'));

		$userNotExistsOrCannotCreateGroup = !$user || !$user->canCreateGroup();
		
		if( $userNotExistsOrCannotCreateGroup )
			return json_encode(['success' => 0, 'exception' => 'GroupsLimitReached']);				
		if( !$this->isValidGroupData($groupName) ) 
			return json_encode(['success' => 0, 'exception' => 'InvalidName']);

		return $this->createIt($groupName, $user);
	}

	private function isValidGroupData($name){
		$validator = Validator::make(
				array('name' => $name),
				array('name' => 'required|min:8|max:45|unique:Group')
			);
		return !$validator->fails();
	}

	private function createIt($name, $creator){
		$group = new Group(['name' => $name ]);
		$creator->createdGroups()->save($group);
		return json_encode([ 'success' => 1, 'group_id' => $group->id ]);		
	}

	/**
		Deletion
	*/

	public function delete($groupId){
		$userId = Input::get('user_id');

		$group = Group::find($groupId);
		$groupNotFound = !$group;
		if($groupNotFound) return json_encode(['success' => 0, 'exception' => 'GroupNotFound']);

		$userDoesNotHavePermission = $group->creator->id != $userId;
		if($userDoesNotHavePermission) return json_encode(['success' => 0, 'exception' => 'UserDoesNotHavePermission']);

		$group->questions()->delete();
		$group->delete();
		return json_encode(['success' => 1]);
	}

}   
