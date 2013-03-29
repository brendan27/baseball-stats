<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Scores_List_Table extends WP_List_Table {
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
	   return array( 'widefat', $this->_args['plural'] );
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return $columns= array(
			'col_datetime'=>__('Date And Time'),
			'col_diamond'=>__('Diamond'),
			'col_division'=>__('Division'),
			'col_home_team'=>__('Home Team'),
			'col_home_score'=>__('Home Score'),
			'col_away_team'=>__('Away Team'),
			'col_away_score'=>__('Away Score')
			// 'col_forfeit'=>__('Forfeit')
		);
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			'col_datetime'=>array('datetime',true)
		);
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();

		/* -- Preparing your query -- */
	        $query = 'SELECT '.$wpdb->prefix.'lmsa_games.* ';
	        $query.= 'FROM '.$wpdb->prefix.'lmsa_games, '.$wpdb->prefix.'lmsa_teams ';
	        $query.= 'WHERE deleted IS NULL AND '.$wpdb->prefix.'lmsa_games.home_team = '.$wpdb->prefix.'lmsa_teams.id';

	    /* -- Check division in query string -- */

		    if (!empty($_GET["div"])) {
		    	$query.= ' AND '.$wpdb->prefix.'lmsa_teams.division = "'.mysql_real_escape_string($_GET["div"]);
		    }

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

				$div = strtoupper(get_division($rec->home_team));

				//edit link
				$editlink  = admin_url('admin.php?page=scores-admin&edit=1&id='.(int)$rec->id);
				$divlink = admin_url('admin.php?page=scores-admin&div='.(string)$div);

				//Display the cell
				switch ( $column_name ) {
					case "col_datetime": echo '<td '.$attributes.'><strong>'.date('D M jS, g:i A',strtotime($rec->datetime)).'</strong> <a href="'.$editlink.'" title="Edit">[edit]</a></td>'; break;
					case "col_diamond": echo '<td '.$attributes.'>'.$rec->diamond.'</td>'; break;
					case "col_division": echo '<td '.$attributes.'>'.$div.' <a href="'.$divlink.'" title="View only '.$div.' division">[View '.$div.']</a></td>'; break;
					case "col_home_team": echo '<td '.$attributes.'>'.get_team_name($rec->home_team).'</td>'; break;
					case "col_home_score": echo '<td '.$attributes.'>'.$rec->home_score.'</td>'; break;
					case "col_away_team": echo '<td '.$attributes.'>'.get_team_name($rec->away_team).'</td>'; break;
					case "col_away_score": echo '<td '.$attributes.'>'.$rec->away_score.'</td>'; break;
					// case "col_forfeit": echo '<td '.$attributes.'>'.$rec->forfeit.'</td>'; break;
				}
			}

			//Close the line
			echo'</tr>';
		}}
	}
}

function select_team($name,$team_id='',$division='',$div_exclude='') {
	global $wpdb;
	$query = 'SELECT id,name,division FROM '.$wpdb->prefix.'lmsa_teams';
	$query.= !empty($division)?' WHERE division = "'.$division.'"' : '';
	if (!empty($div_exclude)){ //if we are excluding a division from the dropdown
		$query.= !empty($division)?' AND':' WHERE';//append the query if there is a division, otherwise start the WHERE here
	$query.= ' division != "' . $div_exclude . '"';
	}
	$query.= ' ORDER BY division';
	
	$teams = $wpdb->get_results($query,OBJECT);
	?>
	
	<select id="<?php echo $name ?>" name="<?php echo $name ?>">
	<?php
	foreach ($teams as $team) {
		//check to see if the current dropdown item is the already saved team for this game
		$selected=$team->id==$team_id?'selected="selected"':'';
		?>
		<option value="<?=$team->id?>" <?=$selected?>><?php echo 'Division '.$team->division.' | '.$team->name ?></option>
	<?php }	?>
	</select>
	
	<?php
}

function edit_game_form($game_id=false) {
	global $wpdb;

	$submitname = $game_id ? "edit_game" : "add_game";//if no game id, then the add_game() processer will be run instead of edit_game

	//get game info if we are editing a game
	if ($_GET['edit']==1) {
		$query = 'SELECT * FROM '.$wpdb->prefix.'lmsa_games';
		$query.= !empty($game_id)?' WHERE id = '.$game_id:'';
		$game = $wpdb->get_row($query,OBJECT);
	}
	?>
		<form id="edit_game" method="POST" action="<?php echo admin_url('admin.php?page=scores-admin') ?>">
			<input type="hidden" name="id" value="<?=$game_id?>"/>
			<table class="form-table">
		        <tbody>
		        	<tr class="form-field">
				        <th><label for="datetime">Date and Time -- (24-hour time)<br/>(YYYY-MM-DD HH:MM:SS)</label></th>
				        <td><input class="datetimepicker" id="datetime" type="text" name="datetime" value="<?=$game->datetime?>" placeholder="YYYY-MM-DD HH:MM:SS" required="required" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="diamond">Diamond</label></th>
				        <td><input id="diamond" type="number" name="diamond" value="<?=$game->diamond?>" required="required" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="home_team">Home Team</label></th>
				        <td><?php select_team('home_team',$game->home_team,get_division($game->home_team),'R'); ?></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="home_score">Home Score</label></th>
				        <td><input id="home_score" type="number" name="home_score" value="<?=$game->home_score?>" /></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="away_team">Away Team</label></th>
				        <td><?php select_team('away_team',$game->away_team,get_division($game->away_team),'R'); ?></td>    
		            </tr>
		        	<tr class="form-field">
				        <th><label for="away_score">Away Score</label></th>
				        <td><input id="away_score" type="number" name="away_score" value="<?=$game->away_score?>" /></td>    
		            </tr>
		    </table>

			<?php if ($_GET['edit']==1) { ?>
				<input class="button-secondary" type="submit" name="delete_game" value="Delete this game" id="deletebutton" />
				<br/>
				<br/>
			<?php } ?>
			<a class="button-secondary" href="<?=htmlspecialchars($_SERVER['HTTP_REFERER'])?>" title="Cancel">Cancel</a>
			<input class="button-primary" type="submit" name="<?php echo $submitname ?>" value="<?php _e("Save"); ?>" id="submitbutton" />
 
	</form>
	<?php
}

function edit_game() {
	//TODO: wpdb seems to be preventing NULL from being inserted. Instead we get '0'
	global $wpdb;

	$home_score=!empty($_POST['home_score'])?$_POST['home_score']:'NULL';
	$away_score=!empty($_POST['away_score'])?$_POST['away_score']:'NULL';
		
	$success = $wpdb->update(
		$wpdb->prefix.'lmsa_games',
		array(
			'datetime'=>$_POST['datetime'], //string
			'diamond'=>$_POST['diamond'], //integer
			'home_team'=>$_POST['home_team'], //integer
			'home_score'=>$home_score, //integer or null
			'away_team'=>$_POST['away_team'], //integer
			'away_score'=>$away_score //integer or null
		),
		array(
			'id'=>$_POST['id'] //integer
		),
		array(
			'%s',
			'%d',
			'%d',
			'%d',
			'%d',
			'%d',
		),
		array(
			'%d'
		)
	);
	return $success;
}

function add_game() {
	global $wpdb;

	$home_score=!empty($_POST['home_score'])?$_POST['home_score']:'NULL';
	$away_score=!empty($_POST['away_score'])?$_POST['away_score']:'NULL';

	//if either of the scores are blank and it is just entered as a game
	if (empty($_POST['home_score']) || empty($_POST['away_score'])) {
		$success = $wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."lmsa_games
			( datetime, diamond, home_team, away_team, home_score, away_score )
			VALUES ( %s, %d, %d, %d, NULL, NULL )", 
	        $_POST['datetime'], $_POST['diamond'], $_POST['home_team'], $_POST['away_team'] ) );
	} else {
	//if scores are entered and it is entered as a game that has been played with a score submitted already
		$success = $wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."lmsa_games
			( datetime, diamond, home_team, away_team, home_score, away_score )
			VALUES ( %s, %d, %d, %d, %d, %d )", 
	        $_POST['datetime'], $_POST['diamond'], $_POST['home_team'], $_POST['away_team'], $_POST['home_score'], $_POST['away_score'] ) );
		}

	return $success;
}

function delete_game() {
	global $wpdb;

	$success = $wpdb->update(
		$wpdb->prefix.'lmsa_games',
		array(
			'deleted'=>date_i18n("Y-m-d H:i:s") //string
		),
		array(
			'id'=>$_POST['id'] //string
		),
		array(
			'%s',
		),
		array(
			'%d'
		)
	);
	
	return $success;
}

?>
<div class="wrap">
	<?php //screen_icon(); ?>
	<h2>LMSA Scores <a href="<?php echo admin_url('admin.php?page=scores-admin&new=1'); ?>" class="add-new-h2">Add New Game</a></h2>
	<p>Here you can edit schedules and submit scores</p>

	<?php
	if ($_POST['edit_game'] || $_POST['add_game'] || $_POST['delete_game']) {
		if ($_POST['edit_game']) {
			$success = edit_game();
		}
		if ($_POST['add_game']) {
			$success = add_game();
		}
		if ($_POST['delete_game']) {
			$success = delete_game();
		}
		//display message
		if ($success) {
			$messageClass='updated';
			$message='Game information updated successfully.';
		}
		else {
			$messageClass='error';
			$message='Game information failed to update or was not changed. Try again and if the problem persists, contact Brendan (<a href="mailto:brendan@polluxtechnology.com" target="_blank">brendan@polluxtechnology.com</a>)';
		}
		
		echo '<div id="message" class="'.$messageClass.'"><p>'.$message.'</p></div>';
	}
	if ($_GET['edit']==1 && !empty($_GET['id'])) {
		edit_game_form($_GET['id']);
	}
	else if ($_GET['new']==1) {
		edit_game_form();
	}
	else {
		$Scores_List_Table = new Scores_List_Table();
		$Scores_List_Table->prepare_items();
		$Scores_List_Table->display();
	}
	?>
</div>
