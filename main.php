<?php 

/*
 * plugin name: Linked In authentication control
 * author: Mahibul Hasan Sohag
 * Description: It controls the linkedin authentication
 * */

class LinkedInAuthentication{
	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('init', array($this, 'authenticate_linkedin_application'));
	}	
	
	function admin_menu(){ 		
		add_menu_page('linkedin groups authentication in wordpress', 'LinkedIn', 'manage_options', 'ln_auth', array($this, 'linkedin_authentication'));
	}
	
  //menupage for groups
	function linkedin_authentication(){

		if($_POST['linkedlin-auth-form-submitted'] == "Y"){
			$keys = array(
				'api_key' => trim($_POST['linkedin-api-key']),
				'api_secret' => trim($_POST['linkedin-api-secret'])
			);
			
			update_option('LinkedInGlobalKeys', $keys);
		}
		
		$keys = $this->get_global_api_keys();
		
		include dirname(__FILE__) . '/includes/linkedin-auth.php';
	}
	
	
	
	/*
	 * get global api keys
	 * */
	
	function get_global_api_keys(){
		return get_option('LinkedInGlobalKeys');
	}
	
	
	/*
	 * if a request is come remotely it will return the api keys and secrets
	 * */
	function authenticate_linkedin_application(){
		if($_GET['get'] == 'linkedin-credentials' && $_GET['type'] == 'json'){
			$keys = $this->get_global_api_keys();
			echo json_encode($keys);
			die();
		}
	}
	

	
}


$LnAuth = new LinkedInAuthentication();


?>