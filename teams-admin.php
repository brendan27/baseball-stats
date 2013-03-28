<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Teams_List_Table extends WP_List_Table {
	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct() {
		parent::__construct( array(
		'singular'=> 'wp_list_game', //Singular label
		'plural' => 'wp_list_games', //plural label, also this well be one of the table css class
		'ajax'  => false //We won't support Ajax for this table
		) );
	}

    /**
	* Get a list of CSS classes for the <table> tag
	*
	* @since 3.1.0
	* @access protected
	*
	* @return array
	*/
	function get_table_classes() {
	   return array( 'widefat fixed', $this->_args['plural'] );
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return $columns= array(
			'col_name'=>__('Team Name'),
			'col_division'=>__('Division'),
			'col_contact_name_one'=>__('Contact 1'),
			'col_contact_name_two'=>__('Contact 2')
		);
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			'col_name'=>array('name',true),
			'col_division'=>array('division',true)
		);
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();

		/* -- Preparing your query -- */
	        $query = 'SELECT '.$wpdb->prefix.'lmsa_teams.* FROM '.$wpdb->prefix.'lmsa_teams';

	    /* -- Check division in query string -- */

		    $filterdivision = !empty($_GET["div"]) ? $wpdb->prefix.'lmsa_teams.division = "'.mysql_real_escape_string($_GET["div"]) : '';
		    if(!empty($filterdivision)){ $query.=', '.$wpdb->prefix.'WHERE '.$filterdivision; }

		/* -- Ordering parameters -- */
		    //Parameters that are going to be used to order the result
		    $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
		    $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
		    if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

		/* -- Pagination parameters -- */
	        //Number of elements in your table?
	        $totalitems = $wpdb->query($query); //return the total number of affected rows
	        //How many to display per page?
	        $perpage = 25;
	        //Which page is this?
	        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
	        //Page Number
	        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
	        //How many pages do we have in total?
	        $totalpages = ceil($totalitems/$perpage);
	        //adjust the query to take pagination into account
		    if(!empty($paged) && !empty($perpage)){
			    $offset=($paged-1)*$perpage;
	    		$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
		    }

		/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );
			//The pagination links are automatically built according to those parameters

		/* -- Register the Columns -- */
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);

		/* -- Fetch the items -- */
			$this->items = $wpdb->get_results($query);
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display_rows() {

		//Get the records registered in the prepare_items method
		$records = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden ) = $this->get_column_info();

		//Loop for each record
		if(!empty($records)){foreach($records as $rec){

			//Open the line
	        echo '<tr id="record_'.$rec->id.'">';
			foreach ( $columns as $column_name => $column_display_name ) {

				//Style attributes for each col
				$class = "class='$column_name column-$column_name'";
				$style = "";
				if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				$attributes = $class . $style;

				$div = $rec->division;

				//edit link
				$editlink  = admin_url('admin.php?page=teams-admin&edit=1&id='.(int)$rec->id);
				$divlink = admin_url('admin.php?page=teams-admin&div='.(string)$div);

				//Display the cell
				switch ( $column_name ) {
					case "col_name": echo '<td '.$attributes.'><strong>'.$rec->name.'</strong> <a href="'.$editlink.'" title="Edit">[edit]</a></td>'; break;
					case "col_division": echo '<td '.$attributes.'>'.$div.' <a href="'.$divlink.'" title="View only '.$div.' division">[View '.$div.']</a></td>'; break;
					case "col_contact_name_one": echo '<td '.$attributes.'>'.$rec->contact_name_one.'</td>'; break;
					case "col_contact_name_two": echo '<td '.$attributes.'>'.$rec->contact_name_two.'</td>'; break;
				}
			}

			//Close the line
			echo'</tr>';
		}}
	}
}

function strip_phone ($string) {
	$new_string = ereg_replace("[^0-9]", "", $string );
	return $new_string;
}

function select_division($set_division=false, $options = array('A','B','C','D','E','R')) {
	global $wpdb;
	?>
	
	<select id="division" name="division">
	<?php
	foreach ($options as $division) {
		//check to see if the current dropdown item is the already saved division for this game
		$selected = $set_division==$division?'selected="selected"':'';
		?>
		<option value="<?php echo $division ?>" <?=$selected?>><?php echo $division ?></option>
	<?php }	?>
	</select>
	
	<?php
}


function edit_team_form($team_id=false) {
	global $wpdb;

	$submitname = $team_id ? "edit_team" : "add_team";//if no team id, then the add_team() function will be run instead of edit_team
	$query = 'SELECT * FROM '.$wpdb->prefix.'lmsa_teams WHERE id = '.$team_id;
	$team = $wpdb->get_row($query,OBJECT);
	?>
		<form id="edit_team" method="POST" action="<?php echo admin_url('admin.php?page=teams-admin'); ?>">
			<input type="hidden" name="id" value="<?=$team_id?>"/>
			<table class="form-table">
		        <tbody>
		        	<tr class="form-field">
				        <th><label for="name">Team Name</label></th>
				        <td><input id="name" type="text" name="name" value="<?=$team->name?>" required="required" /></td>    
		            </tr>
		        	<tr class="form-field"
		        		<?php /* we don't want to allow changing of divisions, only selecting a brand new one for a new team */ if ($_GET['edit'] == 1) { echo ' style="display:none"'; } ?>
		        	>
				        <th><label for="division">Division</label></th>
				        <td><?php select_division($team->division); ?></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_name_one">Contact 1 Name</label></th>
				        <td><input id="contact_name_one" type="text" name="contact_name_one" value="<?=$team->contact_name_one?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_email_one">Contact 1 Email</label></th>
				        <td><input id="contact_email_one" type="email" name="contact_email_one" value="<?=$team->contact_email_one?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_phone_one">Contact 1 Phone</label></th>
				        <td><input id="contact_phone_one" type="text" name="contact_phone_one" value="<?=$team->contact_phone_one?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_altphone_one">Contact 1 Alternate Phone</label></th>
				        <td><input id="contact_altphone_one" type="text" name="contact_altphone_one" value="<?=$team->contact_altphone_one?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_name_two">Contact 2 Name</label></th>
				        <td><input id="contact_name_two" type="text" name="contact_name_two" value="<?=$team->contact_name_two?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_email_two">Contact 2 Email</label></th>
				        <td><input id="contact_email_two" type="email" name="contact_email_two" value="<?=$team->contact_email_two?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_phone_two">Contact 2 Phone</label></th>
				        <td><input id="contact_phone_two" type="text" name="contact_phone_two" value="<?=$team->contact_phone_two?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="contact_altphone_two">Contact 2 Alternate Phone</label></th>
				        <td><input id="contact_altphone_two" type="text" name="contact_altphone_two" value="<?=$team->contact_altphone_two?>" /></td>    
		            </tr>
		    </table>

			<?php if ($_GET['edit']==1 && 'wedecide' == 'toletthemdeleteteams') { ?>
				<input class="button-secondary" type="submit" name="delete_game" value="Delete this team" id="deletebutton" />
				<br/>
				<br/>
			<?php } ?>
			<a class="button-secondary" href="<?=htmlspecialchars($_SERVER['HTTP_REFERER'])?>" title="Cancel">Cancel</a>
			<input class="button-primary" type="submit" name="<?php echo $submitname ?>" value="<?php _e("Save"); ?>" id="submitbutton" />
 
	</form>
	<?php
}

function edit_team() {
	global $wpdb;

	$success = $wpdb->update(
		$wpdb->prefix.'lmsa_teams',
		array(
			'name'=>stripslashes_deep($_POST['name']), //string
			'division'=>stripslashes_deep($_POST['division']), //string
			'contact_name_one'=>stripslashes_deep($_POST['contact_name_one']), //string
			'contact_email_one'=>stripslashes_deep($_POST['contact_email_one']), //string
			'contact_phone_one'=>strip_phone($_POST['contact_phone_one']), //integer
			'contact_altphone_one'=>strip_phone($_POST['contact_altphone_one']), //integer
			'contact_name_two'=>stripslashes_deep($_POST['contact_name_two']), //string
			'contact_email_two'=>stripslashes_deep($_POST['contact_email_two']), //string
			'contact_phone_two'=>strip_phone($_POST['contact_phone_two']), //integer
			'contact_altphone_two'=>strip_phone($_POST['contact_altphone_two']) //integer
		),
		array(
			'id'=>$_POST['id'] //integer
		),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%d',
			'%s',
			'%s',
			'%d',
			'%d'
		),
		array(
			'%d'
		)
	);
	return $success;
}

function add_team() {
	global $wpdb;

	$success = $wpdb->insert(
		$wpdb->prefix.'lmsa_teams',
		array(
			'name'=>stripslashes_deep($_POST['name']), //string
			'division'=>stripslashes_deep($_POST['division']), //string
			'contact_name_one'=>stripslashes_deep($_POST['contact_name_one']), //string
			'contact_email_one'=>stripslashes_deep($_POST['contact_email_one']), //string
			'contact_phone_one'=>strip_phone($_POST['contact_phone_one']), //integer
			'contact_altphone_one'=>strip_phone($_POST['contact_altphone_one']), //integer
			'contact_name_two'=>stripslashes_deep($_POST['contact_name_two']), //string
			'contact_email_two'=>stripslashes_deep($_POST['contact_email_two']), //string
			'contact_phone_two'=>strip_phone($_POST['contact_phone_two']), //integer
			'contact_altphone_two'=>strip_phone($_POST['contact_altphone_two']) //integer
		),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%d',
			'%s',
			'%s',
			'%d',
			'%d'
		)
	);
	return $success;
}
?>

<div class="wrap">
	<?php //screen_icon(); ?>
	<h2>LMSA Teams <a href="<?php echo admin_url('admin.php?page=teams-admin&new=1') ?>" class="add-new-h2">Add New Team</a></h2>
	<p>Here you can edit team information</p>
	<?php
	if ($_POST['edit_team'] || $_POST['add_team']) {
		if ($_POST['edit_team']) {
			$success = edit_team();
		}
		if ($_POST['add_team']) {
			$success = add_team();
		}
		//display message
		if ($success) {
			$messageClass='updated';
			$message='Team information updated successfully.';
		}
		else {
			$messageClass='error';
			$message='Team information failed to update or was not changed. Try again and if the problem persists, contact Brendan (<a href="mailto:brendan@polluxtechnology.com" target="_blank">brendan@polluxtechnology.com</a>)';
		}
		
		echo '<div id="message" class="'.$messageClass.'"><p>'.$message.'</p></div>';
	}
	if ($_GET['edit']==1 && !empty($_GET['id'])) {
		edit_team_form($_GET['id']);
	}
	else if ($_GET['new']==1) {
		edit_team_form();
	}
	else {
		$Teams_List_Table = new Teams_List_Table();
		$Teams_List_Table->prepare_items();
		$Teams_List_Table->display();
	}
	?>
</div>
