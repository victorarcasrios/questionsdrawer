<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Validator;
use Hash;

use App\Models\User;
use App\Models\Group;

/**
 * UsersController
 *
 * @author victor
 */
class UsersController extends Controller{

    ## DEBUG METHOD ONLY    
    public function listAll(){
        return User::all();
    }
    
    ## DEBUG METHOD ONLY
    public function get($id){
        return User::find($id);
    }

    public function getRelatedGroups($userId){
        $user = User::find($userId);
        $asMember = json_decode($user->groups);
        $asCreator = json_decode($user->createdGroups);
        $relatedGroups = array_merge($asMember, $asCreator);
        return json_encode(['success' => 1, 'groups' => $relatedGroups]);
    }

    /**
        Returns user roles        
    **/
    public function getRolesFor($id){
        $user = User::find($id);
        if( !$user ) return json_encode(array( 'success' => 0, 'exception' => 'UserNotFound' ));

        $rolesArray = array();
        foreach( $user->roles as $key => $role )
            $rolesArray[$key] = array( "id" => $role->id, "role" => $role->name);
        return json_encode(['success' => 1, 'roles' => $rolesArray]);
    }

    /**
        Returns recount of groups created by user
        @return JSON (int success, int count, int max_allowed)
    **/ 
    public function countOwnGroups($id){
        $user = User::find($id);
        if( !$user ) return json_encode(array( 'success' => 0, 'exception' => 'UserNotFound' ));

        return json_encode([
            "success" => 1,
            "count" => $user->createdGroups()->count(), 
            "max_allowed" => intval(env("MAX_GROUPS_CREATED_BY_USER"))
        ]);
    }
    
    /** 
        Checks if user is logged
        @returns JSON (success, [exception])
    **/
    public function isLoggedUser(){
        $user = User::find(Input::get("user_id"));
        if( !$user || $user->remember_token !== Input::get('csrf_token')  )
            return json_encode(array('success' => 0, 'exception' => 'IncorrectData'));
        
        return json_encode(array( 'success' => 1 ));
    }
    
    /**
      If isProperSignupData returns OK creates a new user
      @return JSON (success, [exception])
     */
    public function signup(){
        if( !$this->isProperSignupData( Input::get('name'), Input::get('email'), Input::get('password') ) )
            return json_encode( array('success' => 0, 'exception' => 'IncorrectData') );
        User::create( array(
            'name' => Input::get('name'), 
            'email' => Input::get('email'), 
            'password' => Hash::make( Input::get('password') ) 
        ));
        return json_encode( array("success" => 1) );
    }

    /**
      Validates signup data 
      @param String $name
      @param String $email
      @param String $password
      @return 1 for OK, empty for KO
     */
    private function isProperSignupData($name, $email, $password){
        $validator = Validator::make(
            array(
                'name' => $name,
                'email' => $email,
                'password' => $password
            ),
            array(
                'name' => 'required|min:3|max:15|unique:User',
                'email' => 'required|email|unique:User',
                'password' => 'required|min:8'
            )
        );
        return !$validator->fails();
    }
    
    /**
      Signin of a user
      @return JSON (success, [id_and_token/exception])
     */
    public function signin(){
        $id_and_token = $this->getIdAndCSRFTokenOrFail( Input::get('name'), Input::get('email'), Input::get('password') );
        if( $id_and_token )
            return json_encode( array('success' => 1, 'id_and_token' => $id_and_token) );
        else
            return json_encode( array('success' => 0, 'exception' => 'IncorrectCredentials') );
    }
    
    /**
      Returns the id of the user and a token if credentials are OK
      @param String $name
      @param String $email
      @param String $password
      @return csrf_token if credentials combo is OK, false if KO
     */
    private function getIdAndCSRFTokenOrFail($name, $email, $password){
        $user = User::where('name', '=', $name)->get()->first();
        if( !$user )
            $user = User::where('email', '=', $email)->get()->first();
        if( !$user )
            return false;
        return ( Hash::check( $password, $user->password ) ) ? array( 
            "user_id" => $user->id, "csrf_token" => $this->getCSRFTokenFor($user) ) : false;
    }    
    
    /**
      Create a csrf_token for the $user and returns it
      @param User $user
      @return String new csrf_token (also saves it in users db)
     */
    private function getCSRFTokenFor($user){
        $token = csrf_token();
        $user->remember_token = $token;
        $user->save();
        return $token;
    }
    
    /**
        Signout user
    **/
    public function signout(){
        $user = User::find(Input::get('user_id'));
        $user->remember_token = null;
        $user->save();
        return json_encode(array("success" => 1));
    }
}
