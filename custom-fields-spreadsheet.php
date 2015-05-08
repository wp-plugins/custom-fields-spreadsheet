<?php
/*

Plugin Name: Custom Fields Spreadsheet

Plugin URI: http://www.williambixler.com/product/custom-fields-spreadsheet/

Description: A WordPress plugin which gives you all of your custom fields in a single, customizable spreadsheet view.

Version: 1.0.0
 
Author: William Bixler

Author URI: http://williambixler.com/

License: Proprietary license

*/
 
//Add plugin to wordpress admin menu
add_action( 'admin_menu', 'register_custom_fields_spreadsheet' );

//Link the styles and scripts
add_action( 'admin_enqueue_scripts', 'enqueue_styles' );

function enqueue_styles($hook) {
	
	wp_register_style( 'mainCSS', plugin_dir_url( __FILE__ ).'style.css' );
	wp_enqueue_style( 'mainCSS' );
	
	if ( 'toplevel_page_custom-fields-spreadsheet' == $hook ) {
		
		wp_register_style( 'handsonTableCSS', plugin_dir_url( __FILE__ ).'assets/handsontable/dist/handsontable.full.css' );
		wp_enqueue_style( 'handsonTableCSS' );
		
		wp_enqueue_script( 'handsonTableScript', plugin_dir_url( __FILE__ ).'assets/handsontable/dist/handsontable.full.js', array( 'jquery') );
			
	}
	
}
 
function register_custom_fields_spreadsheet() {
	
	//Add primary menu
	add_menu_page( 'Custom Fields Spreadsheet', 'Custom Fields Spreadsheet', 'manage_options', 'custom-fields-spreadsheet', 'custom_fields_init' );
	//Add Go Pro in submenu
	add_submenu_page('custom-fields-spreadsheet', 'Go Pro', 'Go Pro', 'manage_options', 'go-pro', 'custom_fields_go_pro');
	
}

//Begin Plugin Code
function custom_fields_init() {
	
	$plugin_path = plugin_dir_url( __FILE__ );
	
	?>
<?
	
	//Header title
	
	echo '<h1>Custom Fields Spreadsheet</h1>';
	
	//Initialization
	
	?>
<!-- Create holding arrays -->
<script>
	var data = [];
	var dataCopy = [];
</script>
<?
	
	//Set defaults
	$parent = 0;
	$type = 'page';
	$count = -1;
	$status = 'Published';
	$customType = 'Custom';
	
	//Get posted values and override defaults
	if (isset($_POST['Parent'])) {
		$parent = sanitize_text_field( $_POST['Parent']);
	}
	if (isset($_POST['Type'])) {
		$type = sanitize_text_field( $_POST['Type']);
	}
	if (isset($_POST['Status'])) {
		$status = sanitize_text_field( $_POST['Status']);
	}
	
	if (isset($_POST['editAction'])) {
		$editAction = sanitize_text_field( $_POST['editAction']);
		$editActionValue = sanitize_text_field( $_POST[$editAction]);
	}
	if (isset($_POST['customFieldType'])) {
		$customType = sanitize_text_field( $_POST['customFieldType']);
	}
	
	//Set the get children args
	$args = array(
		'post_parent' => $parent,
		'post_type' => $type,
		'posts_per_page' => $count,
		'post_status' => $status,
		'trash' => false,
		'order' => 'ASC',
		'orderby' => 'id'
	);
	
	//Get children
	$children_array = get_children( $args );
	
	//Get keys
	
	//If there are children
	if (!empty($children_array)) {
		//Create holding array for meta keys
		$meta_keys = array();
		foreach ($children_array as $child) {
			//Get page meta values
			$metas = get_post_custom ( $child->ID );
			foreach ($metas as $meta_key => $meta_values) {
				$key = $meta_key;
				//If custom meta values is requested
				if ($customType == 'Custom') {
					//Filter out wordpress meta values
					if (!strpos($key, '_', 1)){
						//Check if key was already found
						if (!in_array($key, $meta_keys)){
							//Add it to the holding array if not
							array_push($meta_keys, $key);
						}
					}
				//If wordpress meta values are requested
				} else if ($customType == 'Wordpress') {
					//Filter out custom meta values
					if (strpos($key, '_', 1)){
						//Check if key was already found
						if (!in_array($key, $meta_keys)){
							array_push($meta_keys, $key);
						}
					}
				//Wordpress and custom meta values are requested
				} else {
					//Check if the key was already found
					if (!in_array($key, $meta_keys)){
						//Add it the the holding array
						array_push($meta_keys, $key);
					}
				}
			}
		}
		?>
		<script>
			var childCheck = true;
		</script>
		<?
	} else {
		?>
		<script>
			var childCheck = false;
		</script>
		<?
	}
	
	?>

<table>
	<!-- Query request form -->
	<form method="post">
		<tr>
			<td>Parent ID:</td>
			<!-- Parent ID with current ID value filled and title displayed -->
			<td><input type="number" name="Parent" value="<? echo $parent ?>">
				<span id="parentNameLabel"><? echo get_the_title( $parent ) ?></span></td>
		</tr>
		<tr>
			<td>Post Type:</td>
			<td><select name="Type">
					<?
									//Populate select with all available options
									$post_types = get_post_types();
									//Add 'any' option
									array_unshift($post_types, 'any');
									foreach ($post_types as $post_type) {
										//Auto select the current option
										if ($post_type == $type) {
											echo '<option value="'.$post_type.'" selected>'.$post_type.'</option>';
										} else {
											echo '<option value="'.$post_type.'">'.$post_type.'</option>';
										}
									}
			?>
				</select></td>
		</tr>
		<tr>
			<td>Post Status:</td>
			<td><select name="Status">
					<?
									//Populate select with all available options
									$post_statuses = get_post_statuses();\//Add 'any' option
									array_unshift($post_statuses, 'any');
									foreach ($post_statuses as $post_status) {
										//Auto select the current option
										if ($post_status == $status) {
											echo '<option value="'.$post_status.'" selected>'.$post_status.'</option>';
										} else {
											echo '<option value="'.$post_status.'">'.$post_status.'</option>';
										}
									}
								?>
				</select></td>
		</tr>
		<tr>
			<td>Custom Field Type:</td>
			<td><select name="customFieldType">
					<?
									//Set field type options
									$fieldTypes = array('Wordpress', 'Custom', 'Both');
									
									foreach ($fieldTypes as $fieldType) {
										//Auto select the current option
										if ($fieldType == $customType) {
											echo '<option selected>'.$fieldType.'</option>';
										} else {
											echo '<option>'.$fieldType.'</option>';
										}
									}
								?>
				</select></td>
		<tr>
		<tr>
			<td><input type="submit" value="Submit"></td>
		</tr>
	</form>
</table>
<br/>
<?
		
		//Populate Spreadsheet
		
		//Create Holding Values
		$data = array();
		$tempData = array();
		
		//Table head row
		array_push($tempData, 'Post ID', 'Post Title');
		foreach ($meta_keys as $key) {
			array_push($tempData, $key);
		}
		array_push($data, $tempData);
		//Table content rows
		foreach ($children_array as $child) {
			//Get title and IDs
			$tempData = array($child->ID, $child->post_title);
			foreach ($meta_keys as $key) {
				//Get meta values base on column's head row
				array_push($tempData, get_post_meta ($child->ID, $key, true));
			}
			array_push($data, $tempData);
		}
		?>
	<script>
		//Transfer from php to javascript
		colHeaders = <?php echo json_encode($data[0]); ?>;
		<? array_splice($data, 0, 1); ?>;
		data = <?php echo json_encode($data); ?>;
		dataCopy = <?php echo json_encode($data); ?>;
	</script> 

<!--Handson Table Functions--> 
<script>
			
			//Make ID column read only
			function hotReadOnlyIDs() {
				
				hot.updateSettings({
					cells: function(row, col, prop) {
						var cellProperties = {};
						
						cellProperties.readOnly = true;
						
						return cellProperties;
					}
				});
				
			}
			
		</script> 

<!--Display Spreadsheet Table-->

<div id="hot"></div>
<script>
			
			//Create the table container
			var container = document.getElementById('hot');
			
			if (childCheck) {
				//Create and populate the table
				var hot = new Handsontable(container,
				{
					data: data,
					colHeaders: colHeaders,
					contextMenu: true,
					stretchH: 'none',
					manualColumnResize: true,
					manualRowResize: true,
					fixedRowsTop: 1,
					wordWrap: true
				});
				
				//Make IDs read only
				hotReadOnlyIDs();
			} else {
				container.innerHTML = '<h2>No Children Found</h2>';
			}
			
		</script>
<?
	
}

function custom_fields_go_pro() {
	
	$plugin_path = plugin_dir_url( __FILE__ );
	
	?>

<!-- Header Title -->
<h1>Go Pro</h1>
<table style="vertical-align: top">
<tr>
	<td><a href="http://www.williambixler.com/product/custom-fields-spreadsheet/"><img src="<? echo $plugin_path ?>assets/images/thumbnail.png"></a></td>
	<td style="padding: 0 20px; vertical-align:top"><p style="font-weight:600;">With 'Custom Fields Spreadsheet Pro', you are given full editing capabilities. You are no longer restricted to only being able to view the values. It displays all of your custom values in the same way but it also allows you to do various editing actions:</p>
		<div style="padding: 0 20px;">
			<ul style="list-style-type:disc">
				<li>adding a custom field to selected pages</li>
				<li>deleting a page</li>
				<li>deleting a custom field from selected pages</li>
				<li>quickly viewing custom fields</li>
				<li>quickly editing custom fields</li>
				<li>filtering by parent ID, post type, or post status</li>
				<li>filtering out WordPress or non-WordPress fields</li>
				<li>copying the cells to other spreadsheet softwares</li>
			</ul>
		</div>
		<br/>
		<form action="http://www.williambixler.com/product/custom-fields-spreadsheet/" style="text-align: center;">
			<input type="submit" class="goPro" value="Go Pro" />
		</form></td>
</tr>
<?
}

?>
