<?php 

/*
 * plugin name: Linked In authentication control
 * author: Mahibul Hasan Sohag
 * Description: It controls the linkedin authentication
 * */


//including the digital download 
include dirname(__FILE__) . '/digital-download/class.digital-downloads.php';

include dirname(__FILE__) . '/includes/class.list-table.php';


class LinkedInAuthentication{
	function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('init', array($this, 'authenticate_linkedin_application'));
		
		
		add_action('init', array(get_class(), 'auto_update_controlling'), 100);
		
		
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
		add_submenu_page('ln_auth', 'license page', 'License', 'manage_options', 'ln_license', array(get_class(), 'license_management'));
		add_submenu_page('ln_auth', 'upgrade page', 'Update', 'manage_options', 'update_Ln', array(get_class(), 'upgrade_management'));
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
	
	
	//page to manage licesne
	static function license_management(){
		$License_List = self::get_list_table();
		
		if(strlen($License_List->current_action()) > 2){
			$id = $_REQUEST['id'];
			if(is_array($id)){
				$message = count($id) . ' ' . $License_List->current_action() . 'd';				
			}
			else{
				$message = $License_List->current_action() . 'd';
			}
			
			
			self::handle_actions($id, $License_List->current_action());
		}
		
		include dirname(__FILE__) . '/includes/list-table.php';
	}
	
	
	/*
	 * handle list talbe actions
	 * */
	
	static function handle_actions($id, $action){
		$ids = array();
		
		if(is_array($id)){
			$ids = $id;
			
		}
		else{
			$ids[] = $id;
		}
		
		//var_dump($ids);
		
		switch($action){
			case 'deactivate':
			case 'activate':
				foreach($ids as $id){
					self::update_license_meta($id, 'status', $action);
				}
				break;
			case 'delete':
				foreach($ids as $id){
					self::delete_license_key($id);
				}
				break;
		}		
	}
	
	
	
	
	static function get_list_table(){
		if(!class_exists('LnLiscence_List_Table')){
			include dirname(__FILE__) . '/includes/class.list-table.php';
		}
		
		$listtable = new LnLiscence_List_Table();
		return $listtable;
	}
	
	
	//handle version info
	static function upgrade_management(){
		
		if($_POST['linkedin-upgrade-form'] == 'Y'){
			$data = array(
				'plugin-version' => trim($_POST['plugin-version']),
				'wp-version-required' => trim($_POST['wp-version-required']),
				'wp-version-tested' => trim($_POST['wp-version-tested']),
				'plugin-description' => trim($_POST['plugin-description']),
				'download-link' => trim($_POST['download-link']),
				'last-updated' => trim($_POST['last-updated']),
				'change-log' => trim($_POST['change-log'])
			);
			
			update_option('linkedin_upgrade_info', $data);
		}
		
		$info = self::get_upgrade_info();
		
		include dirname(__FILE__) . '/includes/upgrade-management.php';
	}
	
	
	/*
	 * get the upgrade info
	 * **/
	static function get_upgrade_info(){
		return get_option('linkedin_upgrade_info');
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
			
			$license_key = $_GET['l_key'];
			$remote_site_url = $_GET['domain'];
			
			if($this->is_valid($license_key, $remote_site_url)){
				$keys = $this->get_global_api_keys();
			}
			else{
				$keys = array(
					'api_key' => null,
					'api_secret' => null,
					'is_error' => true,
					'message' => 'Validation Error! Please check the license key or contact with http://www.gourmetdesign.com.au/'
				);
			}
			
			echo json_encode($keys);
		//	echo 'a';
			
			die();
		}
	}
	
	
	
	
	//checking the validity
	function is_valid($license_key, $remote_site_url){
		global $wpdb;
		$tables = self::get_tables();
		extract($tables);
		
		$id = $wpdb->get_var("SELECT ID FROM $a WHERE l_key = '$license_key'");
		
		if($id){
			$status = $wpdb->get_var("SELECT meta_value FROM $b WHERE ln_id = '$id' AND meta_key = 'status'");
						
			if($status == 'deactivate'){
				return false;
			}
			else{

				$domains = $this->get_license_meta($id, 'domains');
				
				if($domains){
					$domains = unserialize($domains);
					$domains[$remote_site_url] = current_time('timestamp');
									
				}
				else{
					$domains[$remote_site_url] = current_time('timestamp');
				}
								
				$this->update_license_meta($id, 'domains', serialize($domains));
				$this->update_license_meta($id, 'newly_activated', current_time('timestamp'));
				
				return true;
			}
		}
		
		return false;
	}
	
	
	
	//update license meta
	function update_license_meta($id, $key, $value){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		$is_exist = $wpdb->get_var("SELECT meta_value FROM $b WHERE ln_id = '$id' AND meta_key = '$key'");
		if($is_exist){
			$wpdb->update($b, array('meta_value'=>$value), array('ln_id'=>$id, 'meta_key'=>$key), array('%s'), array('%d', '%s'));
		}
		else{
			$wpdb->insert($b, array('ln_id'=>$id, 'meta_key'=>$key, 'meta_value'=>$value), array('%d','%s', '%s'));
		}
	}
	
	
	
	//delete a license key
	function delete_license_key($id){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		$sql[] = "DELETE FROM $a WHERE ID = '$id'";
		$sql[] = "DELETE FROM $b WHERE ln_id = '$id'";
		
	//	var_dump($sql); exit;
		
		foreach($sql as $s){
			$wpdb->query($s);
		}
	}
	
	//get license meta
	function get_license_meta($id, $key){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		return $wpdb->get_var("SELECT meta_value FROM $b WHERE ln_id = '$license_key' AND meta_key = '$key'");
		
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
	
	
	
	
	
	
	/*
	 * autoupdate controlling
	 * */
	static function auto_update_controlling(){
		if($_REQUEST['autoupdate'] == 'autoupdate'){
			
			$info = self::get_upgrade_info();
			
			if (isset($_POST['action'])) {		  
			  
			  switch ($_POST['action']) {
			    case 'version':
			      echo $info['plugin-version'];
			      break;
			    case 'info':	      
			      
			      $obj = new stdClass();
			      $obj->slug = 'linkedin-groups';
			      $obj->plugin_name = 'LinkedIn Groups for Wordpress';
			      $obj->new_version = $info['plugin-version'];
			      $obj->requires = $info['wp-version-required'];
			      $obj->tested = $info['wp-version-tested'];
			     // $obj->downloaded = 12540;
			      $obj->last_updated = $info['last-updated'];
			      $obj->sections = array(
			        'description' => $info['plugin-description'],
			        //'another_section' => 'This is another section',
			        'changelog' => $info['change-log']
			      );
			      $obj->download_link = $info['download-link'];
			      echo serialize($obj);
			    case 'license':
			      echo 'false';
			      break;
			  }
			} else {
			    header('Cache-Control: public');
			    header('Content-Description: File Transfer');
			    header('Content-Type: application/zip');
			    readfile($info['download-link']);
					
			}
			
			exit;
		}
	}
	
		
}


$LnAuth = new LinkedInAuthentication();


?>