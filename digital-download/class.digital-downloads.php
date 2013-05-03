<?php 

/*
 * this class will handle the digital downlads
 * */

class LinkedinDownload_control{
	
	static $liscense_key = null;
	static $email = null;

	//initialize
	static function init(){
		
		//when download starts
		add_action('edd_update_payment_status', array(get_class(), 'generate_license_key'), 10, 3);
		add_action('edd_send_test_email', array(get_class(), 'generate_test_license_key'));
		
		//sending email with download links. Using this hook we are also sending a license key
		//add_filter('edd_purchase_receipt', array(get_class(), 'send_liscense_key'), 10, 3);
		
		
	}
	
	
	//test generation liscense key
	static function generate_test_license_key(){
		return self::generate_license_key(null, null, null);
	}
	
	
	/*process download*/
	static function generate_license_key($payment_id, $new_status, $old_status){		
		
			// Make sure we don't send a purchase receipt while editing a payment
		if ( isset( $_POST['edd-action'] ) && $_POST['edd-action'] == 'edit_payment' )
			return;
	
		// Check if the payment was already set to complete
		if ( $old_status == 'publish' || $old_status == 'complete' )
			return; // Make sure that payments are only completed once
	
		// Make sure the receipt is only sent when new status is complete
		if ( $new_status != 'publish' && $new_status != 'complete' )
			return;

		
		self::$email = edd_get_payment_user_email( $payment_id );
			
		self::create_a_liscense();
		//hook to edit the email
		add_action('edd_email_body_header', array(get_class(), 'attach_liscense_key'));
		
	}
	
	
	
	//@hook edd_purchase_receipt
	static function attach_liscense_key(){
		if(self::$liscense_key){
			echo "<h2 style='text-align: center'>LicenseKey: " . self::$liscense_key . "</h2>";		
		}
	}
	
	//creat a liscense
	static function create_a_liscense(){
		$uniq_id = uniqid();
		
		//var_dump($uniq_id); die();

		if(self::is_unique($uniq_id)){
			self::$liscense_key = $uniq_id;
			if(!self::insert_key()){
				self::$liscense_key = null;
			}
		}
		else {
			return self::create_a_liscense();
		}
						
	}
	
	
	//unique key test
	static function is_unique($key){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		global $wpdb;		
		$id = $wpdb->get_var("SELECT ID FROM $a WHERE l_key = '$key'");
		
		return ($id) ? false : true;
		
	}
	
	
	//insert a key
	static function insert_key(){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		$wpdb->insert($a, array('l_key'=>self::$liscense_key, 'email'=>self::$email), array('%s', '%s'));
		
		if($wpdb->insert_id){
			self::update_license_meta($wpdb->insert_id, 'generation_date', current_time('timestamp'));
		}
		
		return $wpdb->insert_id;
	}
	
	
	//update license meta
	static function update_license_meta($id, $key, $value){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);

		$wpdb->insert($b, array('ln_id'=>$id, 'meta_key'=>$key, 'meta_value'=>$value), array('%d','%s', '%s'));
	}
	
}

