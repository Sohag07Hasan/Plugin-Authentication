<div class="wrap">

	<h2>Licenses</h2>
	
	<form method="post" action="">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
			$License_List->prepare_items();
		//	echo $License_List->search_box('search', 'athlete');		
			$License_List->display();
		?>
	
	</form>

</div>