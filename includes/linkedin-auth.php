<div class="wrap">
	
	<div id="linkdin-icon-div" class="icon32"><img style="height:40px; width: 40px" src="<?php echo LINKDINGROUP_URL . '/images/icon.jpg'?>"></div>
	<h2> Linkedin Groups Application Authentication </h2>
	
	<?php 
		if($_POST['linkedlin-auth-form-submitted'] == "Y") :
		?>
		<div class="updated"><p>Saved</p></div>
		<?php 
		endif;
	?>
	
	
	<p> Please insert your (developer) created appliation specific api key and secret and it will used by the users. You can get it from <a href="https://www.linkedin.com/secure/developer" target="_blank">here</a> </p>
	<form action="" method="post">
		
		<input type="hidden" name="linkedlin-auth-form-submitted" value="Y" />
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="api-key">API Key</label></th>
					<td colspan="2"> <input id="api-key" size="60" type="text" name="linkedin-api-key" value="<?php echo $keys['api_key']; ?>" /> </td>
				</tr>
				<tr>
					<th><label for="api-secret">API Secret</label></th>
					<td colspan="2"> <input id="api-secret" size="60" type="text" name="linkedin-api-secret" value="<?php echo $keys['api_secret']; ?>" /> </td>
				</tr>
								
				
				<tr>
					<td> <input class="button-primary" type="submit" value="Save"> </td>
				</tr>
				
			</tbody>
		</table>		
		
	</form>
	
	
</div>