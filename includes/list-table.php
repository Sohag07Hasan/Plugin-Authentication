<div class="wrap">

	<h2>Licenses</h2>
	
	<?php 
		$action = admin_url('/admin.php?page=ln_license');
		
		if($License_List->get_pagenum()){
			$action = add_query_arg(array('paged'=>$License_List->get_pagenum()), $action);
		}	
	?>
	
	<form method="post" action="<?php echo $action; ?>">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
			$License_List->prepare_items();
		//	echo $License_List->search_box('search', 'athlete');		
			$License_List->display();
		?>
	
	</form>

</div>