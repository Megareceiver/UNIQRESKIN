<?php
class MembershipApiLib {
	static $url = '';

	function __construct(){
        $url = $_SERVER['SERVER_NAME'];
        $domain_info = explode('.', $url);

        self::$url = ATAPI;

	}
	function url(){
		return self::$url.'/user';
	}

	function get_data($uri='',$array_return=false){
		$result = array();
		if( function_exists('curl_init') ){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, self::$url.$uri);
			$data = curl_exec($curl);
			curl_close($curl);
		} else {
			$data = file_get_contents(self::$url.$uri);
		}
		if( $data ){
			$result = json_decode($data,$array_return);
		}

		return $result;
	}

	function login($company,$username,$password){
		if( !isset($_SESSION["wa_current_user"]) ) {
			return false;
		}
		$url = self::$url.'user/login';
		$fields = array(
			'compnay' => $company,
			'username' => $username,
			'password'=>$password
		);

		$postvars = http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
		curl_setopt ($ch,CURLOPT_CONNECTTIMEOUT,60);
		$response = curl_exec ( $ch );
		$result = curl_exec($ch);
		$result = json_decode($result);
		if( isset($result->action) && $result->action && isset($result->data) ){
			$access = 2; /* for company admin*/

			$user = $_SESSION["wa_current_user"];

			$user->loginname = $result->data->username;
			$user->username = $result->data->username;
			$user->email = $result->data->email;
			$user->access = $access;
			$user->timeout = session_timeout();
			$user->last_act = time();

			$role = get_security_role($access);

			if (!$role) return false;
			foreach( $role['areas'] as $code )
				if (in_array($code&~0xff, $role['sections'])) $user->role_set[]= $code;

		}
		// close connection
		curl_close($ch);

	}
}