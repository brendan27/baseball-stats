<?php
/**
 * @package LMSA_Scores
 * @version 0.1
 */
/*
Plugin Name: LMSA Scores
Plugin URI: 
Description: This plugin is used for scheduling as well as stat and score tracking.
Author: Brendan Coffey
Version: 0.1
Author URI: http://polluxtechnology.com
*/

global $baseball_stats_db_version;
$baseball_stats_db_version='1.5';

function baseball_stats_db_check() {
    global $baseball_stats_db_version;
    if (get_site_option( 'baseball_stats_db_version' ) != $baseball_stats_db_version) {
        baseball_stats_db_install();
    }
}

add_action( 'plugins_loaded', 'baseball_stats_db_check' );

function baseball_stats_db_install() {
	global $wpdb, $baseball_stats_db_version;

	$table1_name = $wpdb->prefix.'lmsa_games';
	$table2_name = $wpdb->prefix.'lmsa_teams';
	$table3_name = $wpdb->prefix.'lmsa_logs';

	$sql1 = "CREATE TABLE $table1_name (
		id int(4) unsigned NOT NULL AUTO_INCREMENT,
		home_team int(4) unsigned NOT NULL,
		away_team int(4) unsigned NOT NULL,
		datetime datetime DEFAULT NULL,
		diamond int(2) DEFAULT NULL,
		home_score int(2) DEFAULT NULL,
		away_score int(2) DEFAULT NULL,
		forfeit tinyint(1) DEFAULT '0',
		rescheduled tinyint(1) DEFAULT '0',
		deleted timestamp NULL DEFAULT NULL,
		PRIMARY KEY  (id)
	) ENGINE=InnoDB;";

	$sql2 = "CREATE TABLE $table2_name (
		id int(4) unsigned NOT NULL AUTO_INCREMENT,
		team_number int(4) DEFAULT NULL,
		name varchar(200) DEFAULT NULL,
		division varchar(50) DEFAULT NULL,
		pts int(4) DEFAULT NULL,
		contact_name_one varchar(50) DEFAULT NULL,
		contact_phone_one bigint(10) DEFAULT NULL,
		contact_altphone_one bigint(10) DEFAULT NULL,
		contact_email_one varchar(50) DEFAULT NULL,
		contact_name_two varchar(50) DEFAULT NULL,
		contact_phone_two bigint(10) DEFAULT NULL,
		contact_altphone_two bigint(10) DEFAULT NULL,
		contact_email_two varchar(50) DEFAULT NULL,
		gp int(4) DEFAULT NULL,
		w int(4) DEFAULT NULL,
		l int(4) DEFAULT NULL,
		t int(4) DEFAULT NULL,
		rf int(4) DEFAULT NULL,
		ra int(4) DEFAULT NULL,
		rfa int(4) DEFAULT NULL,
		pct decimal(4,3) DEFAULT NULL,
		PRIMARY KEY  (id)
	) ENGINE=InnoDB;";

	$sql3 = "CREATE TABLE $table3_name (
		id int(4) unsigned NOT NULL AUTO_INCREMENT,
		type varchar(100) DEFAULT NULL,
		time_submitted timestamp NULL DEFAULT CURRENT_TIMESTAMP,
		gametime datetime DEFAULT NULL,
		diamond varchar(100) DEFAULT NULL,
		home_team varchar(100) DEFAULT NULL,
		away_team varchar(100) DEFAULT NULL,
		home_score int(2) DEFAULT NULL,
		away_score int(2) DEFAULT NULL,
		home_forfeit varchar(10) DEFAULT NULL,
		away_forfeit varchar(10) DEFAULT NULL,
		email_body text,
		PRIMARY KEY (id)
	) ENGINE=InnoDB;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	dbDelta( $sql1 );
	dbDelta( $sql2 );
	dbDelta( $sql3 );

	update_option('baseball_stats_db_version',$baseball_stats_db_version);
}

register_activation_hook( __FILE__, 'baseball_stats_db_install' );

function lmsa_scores_settings() {
	register_setting( 'myplugin', 'myplugin_setting_1', 'intval' );
    register_setting( 'myplugin', 'myplugin_setting_2', 'intval' );
}

add_action('admin_init','lmsa_scores_settings');


/* Edit Scores admin page */

function lmsa_scores_settings_page() {
	require_once(dirname(__FILE__) . '/scores-admin.php');
}

/* Edit Teams admin page */

function lmsa_teams_settings_page() {
	require_once(dirname(__FILE__) . '/teams-admin.php');
}

function my_plugin_menu() {
	$scores_page = add_menu_page( 'LMSA Scores', 'LMSA Scores', 'edit_pages', 'scores-admin', 'lmsa_scores_settings_page', 'div', 30 );
	$teams_page = add_submenu_page( 'scores-admin', 'Edit Teams', 'Edit Teams', 'edit_pages', 'teams-admin', 'lmsa_teams_settings_page' );
	/* Using registered $page handle to hook script load */
	add_action('admin_print_scripts-' . $scores_page, 'admin_scripts_method');
}

add_action( 'admin_menu', 'my_plugin_menu' );

function admin_scripts_method() {
	/*
	 * It will be called only on your plugin admin page, enqueue our script here
	 */
	
	//For datetimepicker
	$dependants = array(
		'jquery',
		'jquery-ui-core',
		'jquery-ui-datepicker',
		'jquery-ui-draggable',
		'jquery-ui-slider'
	);
	wp_enqueue_script('datetimepicker-js', plugins_url() . '/scores/datetimepicker/jquery-ui-timepicker-addon.js', $dependants, 1, false);
	wp_enqueue_style('datetimepicker-css', plugins_url() . '/scores/datetimepicker/jquery-ui-timepicker-addon.css', '', 1, false);
	wp_enqueue_style('datetimepicker-theme-css', plugins_url() . '/scores/datetimepicker/ui-lightness/jquery-ui-1.10.1.custom.min.css', '', 1, false);

	wp_enqueue_script('admin-scripts-lmsa', plugins_url() . '/scores/admin-scripts-lmsa.js', '', 1, false);

}

function plugin_table_scripts() {
	//For tablesorter
	wp_enqueue_script('tablesorter-js', plugins_url() . '/scores/tablesorter/jquery.tablesorter.min.js', '', 1, false);
	wp_enqueue_script('table-scripts-lmsa', plugins_url() . '/scores/table-scripts-lmsa.js', '', 1, false);
	wp_enqueue_style('tablesorter-css', plugins_url() . '/scores/tablesorter/css/style.css', '', 1, false);
}

add_action('wp_enqueue_scripts','plugin_table_scripts');

function my_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}

// We need some CSS for the admin icon
function admin_scores_script() {
	?>
	<style type='text/css'>
		#adminmenu #toplevel_page_scores-admin div.wp-menu-image {
			background-image: url('<?=plugin_dir_url( __FILE__ ).'img/trophy--pencil.png';?>');
			background-position: 7px -18px;
		}
		#adminmenu #toplevel_page_scores-admin:hover div.wp-menu-image,
		#adminmenu #toplevel_page_scores-admin.wp-has-current-submenu div.wp-menu-image,
		#adminmenu #toplevel_page_scores-admin.current div.wp-menu-image {
			background-position: 7px 6px;
		}
		#edit_game table, #edit_team table {
			margin-bottom:20px;
		}
		#edit_game .form-field input, #edit_game .form-field select {
			width:25em;
		}
	</style>
	<?php
}

add_action( 'admin_head', 'admin_scores_script' );

// We need some CSS for table things
function scores_css() {
	?>
	<style type='text/css'>
		td span.alignright {
			margin-right:5px;
		}
		.win {
			font-weight:bold;
		}
		.layout-fixed {
			table-layout:fixed;
		}
	</style>
	<?php
}

add_action( 'wp_head', 'scores_css' );

/*
* Get team name from ID
*/
function get_team_name($id) {
	global $wpdb;
	$query = 'SELECT name FROM '.$wpdb->prefix.'lmsa_teams WHERE id = '.$id;
	return $team_name = $wpdb->get_var($query);
}

/*
* Get team division from ID
*/
function get_division($id=false) {
	global $wpdb;

	if (!$id) {
		return false;
	}

	$query = 'SELECT division FROM '.$wpdb->prefix.'lmsa_teams';
	$query.= !empty($id)?' WHERE id = '.$id:'';
	return $team_name = $wpdb->get_var($query);
}

/*
* select everything from the games table, where the division (retrieved from home team in teams table) is equal to the division set in the shortcode attribute
*/
function set_games_query($division='',$team_id=NULL) {
	global $wpdb;

	$lmsa_prefix = 'lmsa_';
	$pre = $wpdb->prefix.$lmsa_prefix;
				
	$query = 'SELECT '.$pre.'games.* ';
	$query.= 'FROM '.$pre.'games,'.$pre.'teams ';
	$query.= 'WHERE deleted IS NULL AND '.$pre.'games.home_team = '.$pre.'teams.id ';
	$query.= $division?'AND '.$pre.'teams.division = "'.$division.'" ':'';
	if (isset($team_id)) {//only get the games played by a certain team, this causes a prepared query to be needed.
		$query.= 'AND ('.$pre.'games.home_team = %d OR '.$pre.'games.away_team = %d) ';
	}
	$query.='ORDER BY '.$pre.'games.datetime';
	return $query;
}

/* Puts team points into the database so division leaders can be determined */
function update_pts($team_id, $pts) {
	global $wpdb;

	$update=$wpdb->update($wpdb->prefix.'lmsa_teams',array('pts'=>$pts),array('id'=>$team_id),array('%d'),array('%d'));
	return $update;
}

function format_phone($phone_number) {
	if(  preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $phone_number,  $matches )) {
	    $result = '('.$matches[1].') '.$matches[2].'-'.$matches[3];
	    return $result;
	}
	else {
		return $phone_number;
	}
}

function print_games($atts) {
	global $wpdb;
	extract(shortcode_atts(array(
		'division'=>'A'
	),$atts));

	$division = strtoupper($division);

	//if the query string specifies a certain team, then we prepare a query
	if (isset($_GET['team'])) {
		$games_query = set_games_query($division,$_GET['team']);
		$games_prepared_query = $wpdb->prepare($games_query,$_GET['team'],$_GET['team']);
		$games = $wpdb->get_results($games_prepared_query,OBJECT);
	}
	else {
		$games_query = set_games_query($division);
		$games = $wpdb->get_results($games_query,OBJECT);
	}

	if (!empty($games)) {//if games exist for the query

	?>
	<h2>
		Division <?=$division?>
		<?php
		if (isset($_GET['team'])) { ?>
			<span>Showing games for <?=get_team_name($_GET['team'])?></span>
		<?php }
		else { ?>
			<span>Click a team name to view selected team's games only</span>
		<?php } ?>
	</h2>

	<table class="layout-fixed">
		<thead>
			<tr>
				<th style="width:25%">Date and Time</th>
				<th style="width:12%">Diamond</th>
				<th>Home</th>
				<th>Away</th>
			</tr>
		</thead>
		<tbody>
			<?php
				
				foreach ($games as $game) {
				
					//variables
					$home_name = get_team_name($game->home_team);
					$away_name = get_team_name($game->away_team);

					//check winner
					unset($home_win, $away_win);
					if ($game->home_score>$game->away_score){
						$home_win=1;
					}
					if ($game->home_score<$game->away_score){
						$away_win=1;
					}
					if( current_user_can( 'edit_posts' ) ) {
						$edit_btn='<br/><a href="' . admin_url('admin.php?page=scores-admin&edit=1&id=' . $game->id) . '">[Edit Game/Enter Score]</a>';
					}
					
					$rescheduled='';
					if ($game->rescheduled) {
						$rescheduled = '<br/><em>Rescheduled</em>';
					}

					$submit_score_link=''; //reset

					// If the game has not had a score entered yet
					if (!$game->home_score && !$game->away_score) {
						$submit_score_link = '<a title="Submit score for this game" href="' . site_url() . '/games/submit-score/';//TODO: this is specific to the current LMSA site
						$submit_score_link.= '?datetime=' . urlencode($game->datetime);
						$submit_score_link.= '&home_team=' . $game->home_team;
						$submit_score_link.= '&away_team=' . $game->away_team;
						$submit_score_link.= '&diamond=' . $game->diamond;
						$submit_score_link.= '">&uarr;</a>';
					}
					?>
					
					<tr>
						<td><?php echo date('D M jS, g:i A',strtotime($game->datetime)) . ' ' . $submit_score_link . $rescheduled . $edit_btn; ?></td>
						<td><?=$game->diamond?></td>
						<td<?=isset($home_win) ? ' class="win"' : ''?>><?php if (! isset($_GET['team'])) { ?><a href="<?php echo the_permalink() ?>?team=<?php echo $game->home_team; ?>"><?php } ?><?php echo $home_name ?><?php if (! isset($_GET['team'])) { ?></a><?php } ?><span class="alignright"><?php echo $game->home_score ?><span></td>
						<td<?=isset($away_win) ? ' class="win"' : ''?>><?php if (! isset($_GET['team'])) { ?><a href="<?php echo the_permalink() ?>?team=<?php echo $game->away_team; ?>"><?php } ?><?php echo $away_name ?><?php if (! isset($_GET['team'])) { ?></a><?php } ?><span class="alignright"><?php echo $game->away_score?></span></td>
					</tr>
					<?php
				}
			?>
		</tbody>
	</table>
<?php
	}
}

add_shortcode('print_games','print_games');

function generate_standings() {
	global $wpdb;

	$lmsa_prefix = 'lmsa_';
	$pre = $wpdb->prefix.$lmsa_prefix;

	//select everything from the games table, where the division (retrieved from home team in teams table) is equal to the division set in the shortcode attribute
	
	$teams_query = 'SELECT id, name, division FROM '.$pre.'teams';

	//echo $teams_query;

	// loop through teams in division
	$teams = $wpdb->get_results($teams_query);
	foreach ($teams as $team) {

		//reset variables
		$w=0;
		$l=0;
		$t=0;
		$rf=0;
		$ra=0;
		$pts=0;
		$pct=0;
		$rfa=0;
		
		$games_query = set_games_query('',$team->id);

		$games_prepared_query = $wpdb->prepare($games_query,$team->id,$team->id);

		$games = $wpdb->get_results($games_prepared_query,OBJECT);
		foreach ($games as $game) {
			$home_score=$game->home_score;
			$away_score=$game->away_score;
			$home_team=$game->home_team;
			$away_team=$game->away_team;

			//check if current team was home or away
			if ($home_team == $team->id) {
				$us=$home_score;
				$them=$away_score;
			}
			if ($away_team == $team->id) {
				$us=$away_score;
				$them=$home_score;
			}
			//check "us" beat "them"
			if ($us>$them){
				$w+=1;
			}
			//check if home team lost
			else if ($them>$us){
				$l+=1;
			}
			//if they neither won nor lost and both fields contain a number (a score has in fact been submitted), then tie.
			else if (!empty($them) && !empty($us)) {
				$t+=1;
			}

			$rf+=$us;
			$ra+=$them;

		}
		
		$gp=$w+$l+$t;
		$rfa=$rf-$ra;
		$pts=$w*2+$t;
		$pct=$gp!=0?number_format($w/$gp,3):'0.000';

		$success = $wpdb->update(
			$wpdb->prefix.'lmsa_teams',
			array(
				'pts'=>$pts, //integer
				'gp'=>$gp, //integer
				'w'=>$w, //integer
				'l'=>$l, //integer
				't'=>$t, //integer
				'rf'=>$rf, //integer
				'ra'=>$ra, //integer
				'rfa'=>$rfa, //integer
				'pct'=>$pct // decimal
			),
			array(
				'id'=>$team->id //integer
			),
			array(
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s'
			),
			array(
				'%d'
			)
		);

		$return=true;
		if (!$success) {
			$return=false;
		}
	}
	return $return;
}

function print_standings($atts) {
	global $wpdb;
	extract(shortcode_atts(array(
		'division'=>'A'
	),$atts));

	$division = strtoupper($division);

	?>
	<h2>Division <?=$division?></h2>
	<table class="sortable">
		<thead>
			<tr>
 			 	<th style="width:25%">Team</th>
 			 	<th>GP</th>
				<th>Wins</th>
				<th>Losses</th>
				<th>Ties</th>
				<th>R/F</th>
				<th>R/A</th>
				<th>R F/A</th>
				<th>PTS</th>
				<th>PCT</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$lmsa_prefix = 'lmsa_';
			$pre = $wpdb->prefix.$lmsa_prefix;
			
			//select everything from the games table, where the division (retrieved from home team in teams table) is equal to the division set in the shortcode attribute
			$teams_query = 'SELECT * FROM '.$pre.'teams WHERE division = "'.$division.'" ORDER BY pts DESC, gp ASC, w DESC, rfa DESC';

			// loop through teams in division
			$teams = $wpdb->get_results($teams_query,OBJECT);
			foreach ($teams as $team) { ?>
				<tr>
					<td><?=$team->name?></td>
					<td class="textalignright"><?=$team->gp?></td>
					<td class="textalignright"><?=$team->w?></td>
					<td class="textalignright"><?=$team->l?></td>
					<td class="textalignright"><?=$team->t?></td>
					<td class="textalignright"><?=$team->rf?></td>
					<td class="textalignright"><?=$team->ra?></td>
					<td class="textalignright"><?=$team->rfa?></td>
					<td class="textalignright"><?=$team->pts?></td>
					<td class="textalignright"><?=ltrim($team->pct, '0')?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
<?php

}

add_shortcode('print_standings','print_standings');

function cap($string) {
	return ucwords(strtolower($string));
}

function print_teams($atts) {
	global $wpdb;
	extract(shortcode_atts(array(
		'division'=>'A'
	),$atts));

	$division = strtoupper($division);

	/*Just a hack to uncapitalize all of the entries
	//
	//

	$teams = $wpdb->get_results('SELECT * FROM wp_lmsa_teams');

	foreach ($teams as $team) {
		$wpdb->update( 
			'wp_lmsa_teams', 
			array( 
				'name' => cap($team->name),	// string
				'contact_name_one' => cap($team->contact_name_one),
				'contact_name_two' => cap($team->contact_name_two),
				'contact_email_one' => strtolower($team->contact_email_one),
				'contact_email_two' => strtolower($team->contact_email_two),
			), 
			array( 'ID' => $team->id ), 
			array( 
				'%s',	// value1
				'%s',	// value2
				'%s',	// value3
				'%s',	// value4
				'%s'	// value5
			), 
			array( '%d' ) 
		);
	}

	// end of hack
	//
	*/

	?>
	<h2>Division <?=$division?></h2>
	<table class="layout-fixed">
		<thead>
			<tr>
 			 	<th>Team</th>
 			 	<th>Primary Contact</th>
				<th>Secondary Contact</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$lmsa_prefix = 'lmsa_';
				$pre = $wpdb->prefix.$lmsa_prefix;
				
				//select everything from the games table, where the division (retrieved from home team in teams table) is equal to the division set in the shortcode attribute
				$teams_query = 'SELECT * FROM '.$pre.'teams WHERE division = "'.$division.'" ORDER BY name';

				//echo $teams_query;
				
				// loop through teams in division
				$teams = $wpdb->get_results($teams_query,OBJECT);
				foreach ($teams as $team) {
					?>
					<tr>
					<td><?=$team->name?></td>
					<td>
						<?=!empty($team->contact_name_one) ? $team->contact_name_one.'<br/>' : ''?>
						<?=!empty($team->contact_phone_one) ? format_phone($team->contact_phone_one).'<br/>' : ''?>
						<?=!empty($team->contact_altphone_one) ? format_phone($team->contact_altphone_one).'<br/>' : ''?>
						<?=!empty($team->contact_email_one) ? '<a target="_blank" href="mailto: '.$team->contact_email_one.'" title="Email '.$team->contact_name_one.'">'.$team->contact_email_one.'</a>' : ''?>
					</td>
					<td>
						<?=!empty($team->contact_name_two) ? $team->contact_name_two.'<br/>' : ''?>
						<?=!empty($team->contact_phone_two) ? format_phone($team->contact_phone_two).'<br/>' : ''?>
						<?=!empty($team->contact_altphone_two) ? format_phone($team->contact_altphone_two).'<br/>' : ''?>
						<?=!empty($team->contact_email_two) ? '<a target="_blank" href="mailto: '.$team->contact_email_two.'" title="Email '.$team->contact_name_two.'">'.$team->contact_email_two.'</a>' : ''?>
					</td>
					</tr>
					<?php
				}
			?>
		</tbody>
	</table>
<?php

}

add_shortcode('print_teams','print_teams');

function division_leaders() {
	global $wpdb;
	?>
	<table class="blue">
		<thead>
			<tr>
				<th>Div</th>
				<th>Team</th>
			</tr>
		</thead>
		<tbody>
			<?php
				//loop through divisions
				$division_query = 'SELECT DISTINCT division FROM '.$wpdb->prefix.'lmsa_teams WHERE division != "R" ORDER BY division ASC';
				$divisions = $wpdb->get_results($division_query,OBJECT);
				foreach ($divisions as $division) {
					?>
					<tr>
						<td><?=$division->division?></td>
						<td>
							<?php
							$leader_query = 'SELECT name, pts FROM '.$wpdb->prefix.'lmsa_teams WHERE pts = (SELECT MAX(pts) FROM '.$wpdb->prefix.'lmsa_teams WHERE division = "'.$division->division.'") AND division = "'.$division->division.'" ORDER BY pts DESC, gp ASC, w DESC, rfa DESC';
							$leaders = $wpdb->get_results($leader_query,OBJECT);
							//loop through in case there is more than 1 team tied for first.
							foreach($leaders as $leader) { 
								echo '<span class="team">'.$leader->name.'</span>';
							} ?>
						</td>
					</tr>
					<?php
				}
			?>
		</tbody>
	</table>
	<?php
}

add_shortcode('division_leaders','division_leaders');

function select_date($select_name="datetime") {
	global $wpdb;
	$date_query = 'SELECT DISTINCT datetime FROM '.$wpdb->prefix.'lmsa_games ORDER BY datetime ASC';
	$dates = $wpdb->get_results($date_query,OBJECT); ?>
		<select id="<?php echo $select_name ?>" name="<?php echo $select_name ?>">
			<option value="">-- Select a Gametime --</option>
		<?php
		$selectedcount=0;

		foreach ($dates as $date) {

			$selected='';
			$valuedate = date('Y-m-d H:i:s',strtotime($date->datetime));
			$nicedate = date('D M jS, Y - g:i A',strtotime($date->datetime));
			$comparedate = date('Y-m-d',strtotime($date->datetime));
			$today = date_i18n('Y-m-d');

			//check to see if the current dropdown item is today (or if the form was submitted with a different date)
			
			// echo 'get:' . $_GET[$select_name].'<br/>';
			// echo 'db:' . $date->datetime;

			//Make sure there isn't already an option selected
			if ($selectedcount<=0) {

				// if query string date matches
				if ($_GET[$select_name] == $date->datetime ) {
					$selected = 'selected="selected"';
					$selectedcount++;
				}

				// if there is no query string $datetime and the form has been submitted
				if (!$_GET[$select_name] && $_POST[$select_name] == $valuedate) {
					$selected = 'selected="selected"';
					$selectedcount++;
				}

				// if there is no query string $datetime, no form submission, and there is a game date for today's date
				if (!$_GET[$select_name] && !$_POST[$select_name] && $comparedate == $today) {
					$selected = 'selected="selected"';
					$selectedcount++;
				}

			}
			?>
			<option value="<?php echo $valuedate ?>" <?=$selected?>><?php echo $nicedate ?></option>
		<?php }	?>
		</select>
<?php }

function select_a_team($select_name,$team_id='',$division='') {
	global $wpdb;
	$query = 'SELECT id,name,division FROM '.$wpdb->prefix.'lmsa_teams';
	$query.= !empty($division)?' WHERE division = "'.$division.'"' : ' ORDER BY division';
	
	$teams = $wpdb->get_results($query,OBJECT);
	?>
	
	<select id="<?php echo $select_name ?>" name="<?php echo $select_name ?>">
		<option value="">-- Select a Team --</option>
	<?php
	foreach ($teams as $team) {
		//check to see if the current dropdown item is the already submitted team for this game, or the team from the query string
		
		// testing
		// echo "post: ".$_POST[$select_name]."</br>";
		// echo 'value: Division '.$team->division.' | '.$team->name;
		
		if (
			( $_GET[$select_name] == $team->id ) // query string contains team id
			||
			( stripslashes_deep( $_POST[$select_name] ) == 'Division '.$team->division.' | '.$team->name ) // form submitted with this team
		) {
			$selected = 'selected="selected"';
		} else { $selected = ''; }
		?>
		<option value="<?php echo 'Division '.$team->division.' | '.$team->name ?>" <?=$selected?>><?php echo 'Division '.$team->division.' | '.$team->name ?></option>
	<?php }	?>
	</select>
	
	<?php
}

function submit_score($atts) {
	global $wpdb;

	extract(shortcode_atts(array(
		'email_to' => get_option('admin_email')
    ), $atts));

    if ($_POST['submit_score'] && !empty($_POST['math_entry']) && ($_POST['math_answer'] == $_POST['math_entry'])) {
    	$to = $email_to;
    	$subject = 'Score Submission';
    	$headers = 'From: donotreply@lethmixedslowpitch.com';
    	$home_forfeit = $_POST['home_forfeit'] == 'on' ? "YES" : "No";
    	$away_forfeit = $_POST['away_forfeit'] == 'on' ? "YES" : "No";

    	$strtotime = strtotime($_POST['datetime']);
    	$nicedate = date('D M jS, Y - g:i A',$strtotime);

		$email_body = "Date: ".$nicedate."\nDiamond: ".$_POST['diamond']."\n\nHome Team: ".$_POST['home_team']."\nHome Score: ".$_POST['home_score']."\nHome Forfeit Checked? ".$home_forfeit."\n\nAway Team: ".$_POST['away_team']."\nAway Score: ".$_POST['away_score']."\nAway Forfeit Checked? ".$away_forfeit;

		// LOG SUBMISSION TO DB IN CASE OF EMAIL FAILURE OR ANYTHING
		$wpdb->insert(
			$wpdb->prefix.'lmsa_logs',
			array(
				'type'=>'submit-score-log', //string
				'time_submitted'=>date_i18n('Y-m-d H:i:s'), //string
				'gametime'=>stripslashes_deep($_POST['datetime']), //string
				'diamond'=>stripslashes_deep($_POST['diamond']), //string
				'home_team'=>stripslashes_deep($_POST['home_team']), //string
				'away_team'=>stripslashes_deep($_POST['away_team']), //string
				'home_score'=>stripslashes_deep($_POST['home_score']), //integer
				'away_score'=>stripslashes_deep($_POST['away_score']), //integer
				'home_forfeit'=>stripslashes_deep($home_forfeit), //string
				'away_forfeit'=>stripslashes_deep($away_forfeit), //string
				'email_body'=>stripslashes_deep($email_body) //string
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
			)
		);

    	$success = mail($to, $subject, $email_body);
    	if ($success) { ?>
    		<p class="success">Score submission successful, thank you.</p>
    	<?php }
    	else { ?>
    		<p class="error">Score submission failed, please try again.</p>
    	<?php }

    } else if ($_POST['submit_score']) { ?>
    	<p class="error">Incorrect answer to anti-spam math question. Try again.</p>
    <?php } ?>

	<?php
	//if the form has not been successfully submitted, display it:
	if (!$success) {
	?>
	
		<form method="post" action="?">
			<div class="inputfield">
				<label for="datetime">Game Date</label>
				<?php select_date() ?>
			</div>
			<div class="inputfield">
				<label for="diamond">Diamond</label>
				<select name="diamond" id="diamond">
					<option value="">-- Select a Diamond --</option>
					<?php
					$diamonds=array(1,2,3,4,5,6,7,8,9,10);
					foreach ($diamonds as $diamond) {
						if ( $_GET['diamond'] == $diamond || $_POST['diamond'] == $diamond ) { // if a link was clicked from the games page with a query string or this form was submitted to itself
							$selected =  'selected = "selected"';
						} else $selected = '' ?>
						<option <?php echo $selected ?> value="<?php echo $diamond ?>"><?php echo $diamond ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="inputfield">
				<label for="home_team">Home Team</label>
				<?php select_a_team("home_team") ?>
			</div>

			<div class="inputfield">
				<label for="home_score">Home Score</label>
				<input type="number" id="home_score" name="home_score" value="<?php echo $_POST['home_score'] ?>" style="width:50px"/>
				<input <?php if ($_POST['home_forfeit'] == "on") { echo 'checked="checked"'; } ?> type="checkbox" id="home_forfeit" name="home_forfeit"/> <label for="home_forfeit">Forfeit/No show</label>
			</div>

			<div class="inputfield">
				<label for="away_team">Away Team</label>
				<?php select_a_team("away_team") ?>
			</div>
			<div class="inputfield">
				<label for="away_score">Away Score</label>
				<input type="number" id="away_score" name="away_score" value="<?php echo $_POST['away_score'] ?>" style="width:50px"/>
				<input <?php if ($_POST['away_forfeit'] == "on") { echo 'checked="checked"'; } ?> type="checkbox" id="away_forfeit" name="away_forfeit"/> <label for="away_forfeit">Forfeit/No show</label>
			</div>
			<div class="inputfield">
				<label for="math_entry"><?php echo $num1 = rand(1,9).' + '. $num2 = rand(1,9).' =' ?></label>
				<input type="hidden" name="math_answer" value="<?php echo ($num1 + $num2) ?>"/>
				<input type="number" id="math_entry" name="math_entry" style="width:50px"/>
			</div>
			<div class="inputfield">
				<input name="submit_score" type="submit" value="Submit Score"/>
			</div>

		</form>
	<?php } ?>
<?php }
add_shortcode('submit_score','submit_score');


/*
Plugin Name: Division Leaders
Plugin URI:
Description: Adds a widget for displaying division leaders
Version: 0.0.1
Author: Brendan Coffey
Author URI: http://polluxtechnology.com/
*/

class division_leaders_widget extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
        parent::__construct('division_leaders', $name = 'Division Leaders', array( 'description' => 'This widget will show a table of division leaders.' ) );	
    }
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {	
        extract( $args );
        $standings_page = apply_filters('widget_title', $instance['standings_page']);
        $message 	= $instance['message'];
        ?>
        <li class="division_leaders">
        	<h3 class="widgettitle">Division Leaders</h3>
        	<?php division_leaders(); ?>
			<?php if ($standings_page) { ?>
				<a class="button" href="<?php echo get_page_link($standings_page); ?>" title="View full league standings">Full Standings</a>
			<?php } ?>
        </li>
        	
    <?php }
    /**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['standings_page'] = strip_tags( $new_instance['standings_page'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'standings_page' ] ) ) {
			$standings_page = $instance[ 'standings_page' ];
		}
		else {
			$standings_page = __( '1', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'standings_page' ); ?>"><?php _e( 'Standings Page ID:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'standings_page' ); ?>" name="<?php echo $this->get_field_name( 'standings_page' ); ?>" type="text" value="<?php echo esc_attr( $standings_page ); ?>" />
		</p>
		<?php 
	}
 
} // end class child_pages_widget
add_action('widgets_init', create_function('', 'return register_widget("division_leaders_widget");'));
?>
