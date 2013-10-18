<?php

include('../../../wp-blog-header.php');

global $wpdb, $table_prefix;

$sql = "SELECT * FROM {$table_prefix}themes WHERE id = '{$_GET["id"]}'";

$res = $wpdb->get_row($sql);
header('HTTP/1.1 200 OK');
echo "
<h3>Editing $res->name</h3>
<form method='post' action='' enctype='multipart/form-data' id='edit_form'>
<table class='form-table' id='themes_edit'>
	<tr valign='top'>
			<th scope='row'><label for='themes_name'>Name:</label></th>
				<td>
					<input name='themes_name' type='text' id='themes_name' value='$res->name' class='regular-text code'>
				</td>
			</tr>
			<tr valign='top'>
				<th scope='row'>
					<label for='themes_version'>Version:</label>
				</th>
					<td>
						<input name='themes_version' type='text' value='$res->version' class='small-text code'>
					</td>
				</tr>
			<tr valign='top'>
				<th scope='row'><label for='themes_zip'>Zipfile:</label></th>
				<td>
					<input name='themes_zip' type='file' id='themes_zip'>
				</td>
			</tr>
			<tr valign='top'>
			<th scope='row'><label for='themes_thumb'>Screenshot:</label></th>
				<td>
					<input name='themes_thumb' type='file' id='themes_thumb'>
				</td>
			</tr>
			<tr valign='top'>
			<th scope='row'><label for='themes_description'>Description:</label></th>
				<td>
					<textarea style='width:300px' name='themes_description' id='themes_description' cols='45' rows='4'>$res->description</textarea>
				</td>
			</tr>
			<tr valign='top'>
			<td scope='row'><input type='button' name='themes_delete' id='themes_delete' class='button-primary' value='Delete'></td>
				<td>
				
				<input type='button' name='themes_delete_cancel' id='themes_delete_cancel' class='button-primary' value='Cancel'>
				<input type='submit' name='themes_edit_submit' id='themes_edit_submit' class='button-primary' value='Save Theme'>
				</td>
			</tr>
			<input type='hidden' name='theme_id' id='theme_id' value='{$_GET["id"]}' />
			<input type='hidden' name='theme_action' id='theme_action' value='' />
			
	</table>
</form>";