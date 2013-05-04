<div class="wrap">

	<h2> LinkedIn Groups for Wordpress Upgrade info </h2>
	
	<?php 
		
		if($_POST['linkedin-upgrade-form'] == 'Y'){
			echo "<div class='updated'><p>saved</p></div>";
		}
	
	?>
	
	<form action="" method="post">
		<input type="hidden" name="linkedin-upgrade-form" value="Y" />
	
		<table class="form-table">
			
			<tbody>
			
				<tr scope="row">
					<th> Latest version </th>
					<td> <input type="text" name="plugin-version" value="<?php echo $info['plugin-version']; ?>" /> </td>
				</tr>
				
				<tr scope="row">
					<th> Wp Version Required </th>
					<td> <input type="text" name="wp-version-required" value="<?php echo $info['wp-version-required']; ?>" /> </td>
				</tr>
				
				<tr scope="row">
					<th> Wp Version Tested </th>
					<td> <input type="text" name="wp-version-tested" value="<?php echo $info['wp-version-tested']; ?>" /> </td>
				</tr>
				
				<tr scope="row">
					<th> Description </th>
					<td> <textarea rows="3" cols="40" name="plugin-description"><?php echo $info['plugin-description']; ?></textarea> </td>
				</tr>
				
				<tr scope="row">
					<th>Change Log Description </th>
					<td> <textarea rows="3" cols="40" name="change-log"><?php echo $info['change-log']; ?></textarea> </td>
				</tr>
				
				<tr scope="row">
					<th> Last Updated </th>
					<td> <input type="text" name="last-updated" value="<?php echo $info['last-updated']; ?>" />  </td>
				</tr>
				
				<tr scope="row">
					<th> Download Link </th>
					<td> <input type="text" name="download-link" value="<?php echo $info['download-link']; ?>" /> </td>
				</tr>
				
			</tbody>
			
		</table>
		
		<p> <input type="submit" value="Save" class="button button-primary" /> </p>
	
	</form>
	

</div>