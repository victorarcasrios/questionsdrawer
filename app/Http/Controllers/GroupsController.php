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
	// public function get($id){
	// 	$group = Group::find($id);
		
	// 	return json_encode([ 
	// 		'success' => 1,
	// 		'group_id' => $group->id,
	// 		'group_name' => $group->name,
	// 		'creator_id' => $group->creator->id,
	// 		'creator_name' => $group->creator->name
	// 		]);
	// }

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

	/**
	 * Search groups with the given data (member role and status, words in title)
	 * @return json array [status, groups]
	 */
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
			'status' => env('STATUS_OK'), 'groups' => $this->executeSearch($groups, $names)->get()
		]);
	}

	/**
	 * Search the given words in the titles of the given groups
	 * @param array of Group objects $groups where to search
	 * @param string array $names words to search in the groups titles
	 * @return array of Group objects (found)
	 */
	private function executeSearch($groups, $names){
		if(!$groups)
			$groups = Group::select('groups.id', 'groups.name');
		else
			$groups = $groups->select('groups.id', 'groups.name');

		$groups = $groups->where(function($query) use($names){
			$query->where('groups.name', 'LIKE', "%{$names[0]}%");

			for( $i = 1; $i < sizeof($names); $i++ ){
				$query->where('groups.name', 'LIKE', "%{$names[$i]}%");
			}
		});
		
		return $groups;
	}

	/**
	 * Return the groups with the indicated criteria
	 * @param User $user that makes the search
	 * @param string $role member role
	 * @param string $status member status
	 * @return array of Group objects (found)
	 */
	private function getGroupsToSearch(User $user, $role, $status){
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

	/**
	 * Checks if the given query result is empty, if it is,
	 * return all groups, if not, returns the array
	 * @param Eloquent query select in groups table
	 * @return array of Group objects
	 */
	private function returnCurrentGroupsOrAllIfAny($groups){
		if(!$groups)
			return Group::select('groups.id', 'groups.name')->get();
		else
			return $groups->select('groups.id', 'groups.name')->get();
	}

	/**
	 * Returns all the groups where the current user is the creator or an active teacher
	 * @return [status, groups]
	 */
	public function getOnesWithMeAsStaffPerson(){
		$user = User::find(Input::get('user_id'));
		$groups = $user->groupsWithMeAsStaff();

		if( $groups->exists() )
			return json_encode(['status' => env('STATUS_OK'), 'groups' => $groups->get()]);

		return json_encode(['status' => env('STATUS_KO'), 'exception' => 'UserIsNotStaffAnywhere']);
	}


	/**
		CREATION, EDITION and DELETION
	*/

	/**
	 * Do some checks and calls createIt() if the given data pass them properly
	 * @return [status, [exception/group_id]]
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

	/**
	 * Checks that the given name is a valid group name, if NOT return TRUE, else FALSE
	 * @param string $name
	 * @return boolean TRUE if is NOT valid, FALSE either
	 */
	private function isValidGroupData($name){
		$validator = Validator::make(
				array('name' => $name),
				array('name' => 'required|min:8|max:45|unique:groups')
			);
		return !$validator->fails();
	}

	/**
	 * Creates a new group with the given data
	 * @param string $name title for the group
	 * @param User $creator of the group
	 * @return json array [status, group_id]
	 */
	private function createIt($name, User $creator){
		$group = new Group(['name' => $name ]);
		$creator->createdGroups()->save($group);
		return json_encode([ 'status' => env('STATUS_OK'), 'group_id' => $group->id ]);		
	}

	/**
	 * Updates the existent group with the given group id
	 * @param integer $groupId
	 * @return json array [status, [exception]]
	 */
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

	/**
	 * Deletes the group with the given group id
	 * @param integer $groupId id of the group to be deleted
	 * @return json array [status, [exception]]
	 */
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
