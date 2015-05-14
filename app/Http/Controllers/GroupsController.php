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

	/**
		VIEW
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

	// // NOT USED BY THE APP. PLEASE BE CAREFUL, NOT TESTED
	// public function getAll(){
	// 	$groups = Group::get()->lists('name', 'id');
	// 	return json_encode(['success' => 1, "groups" => $groups]);		
	// }

	// // NOT USED BY THE APP. PLEASE BE CAREFUL, NOT TESTED
	// public function getAllForMember($id, $roleName){
	// 	$user = User::find($id);

	// 	if( !$user ) return json_encode(['success' => 0, 'exception' => 'UserNotFound']);

	// 	$roleId = Role::where( 'name', '=', $roleName )->pluck( 'id' );
	// 	if( !$roleId ) return json_encode(['success' => 0, 'exception' => 'RoleNotFound']);

	// 	$groups = $user->groups()->select('id', 'name')->where('id_role', '=', $roleId)->get();
	// 	return json_encode(['success' => 1, 'groups' => $groups]);
	// }

	/**
		SEARCHES
	**/
	public function search(){
		$user =  User::find( Input::get('user_id') );
		$role = Input::get('role_name');
		$status = Input::get('status');
		$search = Input::get('search_string');

		$groups = $this->getGroupsToSearch($user, $role, $status);
		$names = (isset($search)) ? explode(' ', $search) : false;
		$notTextToSearch = !$names;

		if( $notTextToSearch ){
			return json_encode([
				'status' => env('STATUS_OK'), 'groups' => $this->returnCurrentGroupsOrAllIfAny($groups)
				]);
		}
		
		return json_encode([
			'status' => env('STATUS_OK'), 'groups' => $this->executeSearch($groups, $names)->get()]);
	}

	private function executeSearch($groups, $names){
		if(!$groups)
			$groups = Group::select('groups.id', 'groups.name')->where('groups.name', 'LIKE', "%{$names[0]}%");
		else
			$groups = $groups->select('groups.id', 'groups.name')->where('groups.name', 'LIKE', "%{$names[0]}%");

		for( $i = 1; $i < sizeof($names); $i++ ){
			$groups = $groups->orWhere('groups.name', 'LIKE', "%{$names[$i]}%");
		}
		return $groups;
	}

	private function getGroupsToSearch($user, $role, $status){
		switch($role){			
			case null:
				return false;
			case env('CREATOR'):
				return $user->createdGroups();
			case env('NOT_MEMBER'):
				return Group::notRelatedTo($user->id);
			default:
				return $user->getGroupsAs($role, $status);
		}
	}

	private function returnCurrentGroupsOrAllIfAny($groups){
		if(!$groups)
			return Group::select('groups.id', 'groups.name')->get();
		else
			return $groups->select('groups.id', 'groups.name')->get();
	}


	/**
		CREATION
	*/
	public function create(){
		$groupName = Input::get('group_name');
		$user = User::find(Input::get('user_id'));

		$userNotExistsOrCannotCreateGroup = !$user || !$user->canCreateGroup();
		
		if( $userNotExistsOrCannotCreateGroup )
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'GroupsLimitReached']);				
		if( !$this->isValidGroupData($groupName) ) 
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'InvalidName']);

		return $this->createIt($groupName, $user);
	}

	private function isValidGroupData($name){
		$validator = Validator::make(
				array('name' => $name),
				array('name' => 'required|min:8|max:45|unique:groups')
			);
		return !$validator->fails();
	}

	private function createIt($name, $creator){
		$group = new Group(['name' => $name ]);
		$creator->createdGroups()->save($group);
		return json_encode([ 'status' => env('STATUS_OK'), 'group_id' => $group->id ]);		
	}

	public function update($groupId)
	{
		$group = Group::find($groupId);
		$groupName = Input::get('group_name');
		$user = User::find(Input::get('user_id'));

		if( !$user )
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserNotFound']);
		if( !$this->isValidGroupData($groupName) )
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'InvalidName']);
		if( !$user->isCreator($group) )
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserCanNotUpdateGroup']);

		$group->name = $groupName;
		$group->save();
		return json_encode(['status' => env('STATUS_OK')]);
	}


	public function delete($groupId){
		$user = User::find(Input::get('user_id'));
		$group = Group::find($groupId);
		$userIsNotCreator = !$user->isCreator($group);

		if($userIsNotCreator) 
			return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserDoesNotHavePermission']);

		$group->questions()->delete();
		$group->delete();
		return json_encode(['status' => env('STATUS_OK')]);
	}

}   
