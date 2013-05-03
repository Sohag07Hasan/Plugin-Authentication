<?php

if( ! class_exists( 'WP_List_Table' ) ) {
	if(!class_exists('WP_Internal_Pointers')){
		require_once( ABSPATH . '/wp-admin/includes/template.php' );
	}
	require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );
}

class LnLiscence_List_Table extends WP_List_Table{
	
	
	private $per_page = 0;
	private $total_items = 0;
	private $current_page = 0;
	
	/*preparing items*/
	function prepare_items(){
								
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
				
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		//paginations
		$this->_set_pagination_parameters();
				
		$this->items = $this->populate_table_data();
					
	}
	
	
	/*columns of the talbe*/
	function get_columns(){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'l_key' => __('Liscence Key'),
			'email' => __('Email'),
			'domains' => __('Domains'),
			'generation_time' => __('Generation Time')
		);
		
		return $columns;
	}
	
	
	//make some column sortable
	function get_sortable_columns(){
		$sortable_columns = array(
		//	'email' => array('email', false),
			'generation_time' => array('generation_time', false)
		);
		
		return $sortable_columns;
	}
	
	
	/*
	 * total items
	 * */
	private function _set_pagination_parameters(){
		$this->per_page = 30;
		$this->current_page = $this->get_pagenum();
		
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		$sql = "SELECT COUNT(ID) FROM $a";
		
		
		if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
			$s = trim($_REQUEST['s']);
			$sql .= " WHERE email LIKE '%$s%'";
		}
		
		
		$this->total_items = $wpdb->get_var($sql);
		
		$this->set_pagination_args( array(
            'total_items' => $this->total_items,                  //WE have to calculate the total number of items
            'per_page'    => $this->per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($this->total_items/$this->per_page)   //WE have to calculate the total number of pages
        ) );
	}
	
	
	//populate talbe data
	function populate_table_data(){
		
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		$sql = "SELECT * FROM $a";
		
		
		if(isset($_REQUEST['s']) && !empty($_REQUEST['s'])){
			$s = trim($_REQUEST['s']);
			$sql .= " WHERE email LIKE '%$s%' ";
		}
		
		$order_by = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'email';
		$order = (isset($_GET['order'])) ? strtoupper($_GET['order']) : 'ASC';
		
		$sql .= " ORDER BY $order_by $order";
		
		//pagination
		$current_page = ($this->current_page > 0) ? $this->current_page - 1 : 0;
		$offset = (int) $current_page * (int) $this->per_page;
		
		$sql .= " LIMIT $this->per_page OFFSET $offset";
		
		
		$results = $wpdb->get_results($sql);
			
		
		$data = array();
		if($results){
			foreach($results as $result){
				
				$metas = self::get_license_meta($result->ID);
				
				$data[] = array(
					'ID' => $result->ID,
					'l_key' => $result->l_key,
					'email' => $result->email,
					'generation_time' => date("d M, Y", $metas['generation_time']),
					'domains' => 'http://google.com'
					
				);
			}
		}
		
		
		//var_dump($data); exit;
		
		return $data;
		
	}
	
	
	static function get_license_meta($id){
		global $wpdb;
		$tables = LinkedInAuthentication::get_tables();
		extract($tables);
		
		$results = $wpdb->get_results("SELECT * FROM $b WHERE ln_id = '$id'");
		
		$data = array();
		if($results){
			foreach($results as $result){
				$data[$result->meta_key] = $result->meta_value;
			}
		}
		
		return $data;
	}
	
	
	/* default column checking */
	function column_default($item, $column_name){
		switch($column_name){
							
			case "email":
			case 'ID':
			case 'l_key':
			case 'generation_time':
			case 'domains':
				return $item[$column_name];
				break;
			default: 
				var_dump($item);
			
		}
	}	
	
	
	/* checkbox for bulk action*/
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item['ID']
        );    
    }
    
    
    
	/*adding some extra actions links after the first column*/
	function column_l_key($item){
		
		//var_dump($item);
		
		
		$deactivate_href = sprintf('?page=%s&action=%s&id=%s', $_REQUEST['page'],'deactivate',$item['ID']);
		$del_href = sprintf('?page=%s&action=%s&l_key=%s', $_REQUEST['page'],'delete',$item['l_key']);
				
		if($this->get_pagenum()){
			$deactivate_href = add_query_arg(array('paged'=>$this->get_pagenum()), $deactivate_href);
			$del_href = add_query_arg(array('paged'=>$this->get_pagenum()), $del_href);
		}
		
		$actions = array(
			'deactivate' => "<a href='$deactivate_href'>Deactivate</a>",
			'delete' => "<a href='$del_href'>Delete</a>"
		);
		
		
  		return sprintf('%1$s %2$s', $item['l_key'], $this->row_actions($actions) );
	}
	
    
    
    
    
    
    
    
	//bulk actions initialization
	function get_bulk_actions() {
		$actions = array(
	    	'deactivate'    => 'Deactivate'
	  	);
	  	return $actions;
	}
		
}

/*
$a = new LnLiscence_List_Table();
add_action('init', array($a, 'populate_table_data'));
*/