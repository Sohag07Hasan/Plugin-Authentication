<?php 

/*
 * plugin name: Linked In authentication control
 * author: Mahibul Hasan Sohag
 * Description: It controls the linkedin authentication
 * */


//including the digital download 
include dirname(__FILE__) . '/digital-download/class.digital-downloads.php';


class LinkedInAuthentication{
	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('init', array($this, 'authenticate_linkedin_application'));
		
		
		/*
		 * table creation to hold the keys
		 * 
		 * */
		register_activation_hook(__FILE__, array(get_class(), 'activate_the_plugin'));
		
		
		//digital downolad
		LinkedinDownload_control::init();
	}	
	
	function admin_menu(){ 		
		add_menu_page('linkedin groups authentication in wordpress', 'Ln Api Keys', 'manage_options', 'ln_auth', array($this, 'linkedin_authentication'));
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
	
	
	/*
	 * tabel creation
	 * */
	static function activate_the_plugin(){
		$tables = self::get_tables();
		extract($tables);
		
		$sql[] = "CREATE TABLE IF NOT EXISTS $a(
			ID bigint unsigned AUTO_INCREMENT,
			l_key varchar(50) NOT NULL,
			email varchar(50) NOT NULL,
			primary key(ID)
		)";
	
		$sql[] = "CREATE TABLE IF NOT EXISTS $b(
			ln_id bigint unsigned NOT NULL,
			meta_key varchar(50) NOT NULL,
			meta_value text NOT NULL
		)";
		
		if(!function_exists('dbDelta')) :
			include ABSPATH . 'wp-admin/includes/upgrade.php';
		endif;
		
		foreach($sql as $s){
			dbDelta($s);
		}
	}
	
	static function get_tables(){
		global $wpdb;
		return array(
			'a' => $wpdb->prefix . 'ln_liscesnse',
			'b' => $wpdb->prefix . 'ln_liscense_meta'
		);
	}

	
}


$LnAuth = new LinkedInAuthentication();


?>