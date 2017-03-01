<?php

/*
 * Plugin Name: Omari Omari Revolution
 * Version: 1.0
 * Plugin URI: http://vuband.com
 * Description: Free Omari!
 * Author: Tom Clemente, CSC '17
 * Author URI: http://www.github.com/tclemente
 * Requires at least: 6.0
 * Tested up to: 6.0
 */

date_default_timezone_set("America/New_York"); // server is in a different timezone so we have to account for that

defined( 'ABSPATH' ) or die( 'Good try' );  // don't allow direct access to file

define('API_NAMESPACE', 'omari/');

/**
 * Scripts
 *
 * Enqueue scripts at the right time
 *
 */

function omari_scripts() {

	wp_enqueue_style('omari_style', plugin_dir_url(__FILE__) . "style.css");
	wp_enqueue_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js');
}

add_action('wp_enqueue_scripts', 'omari_scripts');

/**
 * Install
 *
 * Upon plugin activation in wp-admin/plugins.php, installs tables with proper structure (w/ no data).
 *
 * @global object $wpdb The Wordpress database interaction object
 *
 */

function omari_install() {

	global $wpdb;             // allow for db talking through $wpdb global
	$charset = $wpdb->get_charset_collate();     // make sure our shit doesn't get fucked up by character set/collation fuckery
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // required for adding tables

	/*******************************************
	 *		  Create main database table.		*
	 *******************************************/

	$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "omari_scores` ( " . // check to make sure this shit doesn't exist already, in case of re-activation
	"`id` int(11) NOT NULL AUTO_INCREMENT," .   // unique identifier
	"`user_name` tinytext NOT NULL," .     // user name text
	"`score` int(11) NOT NULL," .     // user score
		"PRIMARY KEY (`id`)," .         // make `id` the primary key of the table
	"UNIQUE KEY `id` (`id`)" .       // make `id` a unique key
	") " . $charset . ";";          // set table charset

	dbDelta( $sql );           // commit database changes

}

register_activation_hook( __FILE__, 'omari_install' );  // register activation hook so database structure is created upon activation


/**
 * Handle AJAX requests from user-facing pages
 *
 * Analyze POST-provided parameters and route the request data to the proper database functions
 *
 * @global object $_POST The POST data provided by the webserver to wp-ajax.php
 *
 * TODO: test this function
 *
 */

function omari_handle_ajax() {
	// sanitize $_POST values to their known primitives
	omari_json_response(
				omari_scores($_POST["username"], (int)$_POST["score"])
	);
	wp_die(); // die the script, as per the wordpress codex, to prevent additional output
}

add_action("wp_ajax_omari", "omari_handle_ajax");  // register the vuber_handle_ajax() function as a callback for "vuber" wp-ajax.php requests
add_action('wp_ajax_nopriv_omari', 'omari_handle_ajax');



/**
 * Echo a JSON-compliant response to reply to frontend AJAX requests, and kill the script to prevent any additional output.
 *
 * @argument boolean $success Whether or not the requested operation was successful
 * @optional string $error A short explanation of the error that occurred, if applicable
 *
 * TODO: test this function
 *
 */

function omari_json_response($data, $error = null) {
	ob_clean();      // clean the output buffer

	$response = $data;

	if (is_null($data)) {
		$response["success"] = false;
		$response["error"] = "General error.";
	}

	if ($error && $error !== "") { // add an array member to explain any errors that might occur
		$response["error"] = $error;
	}

	echo json_encode($response); // put the json-ified array into the buffer

	wp_die();      // die using wordpress' built-in function to prevent any more script output
} 

/**
 * Score
 *
 * Inserts a row into the score table with the appropriate values
 *
 * @global object $wpdb The Wordpress database interaction object
 * @argument string $username 
 * @argument integer $score
 *
 *
 */

function omari_scores($username, $score) {
	global $wpdb;        // allow for database transactions via $wpdb global

	$wpdb->insert(
		$wpdb->prefix . "omari_scores", // select our log table for insertion (hehe)
		array(         // map arguments to their corresponding columns in log table
			"user_name"  => $username,
			"score"   => $score
		),
		array(         // map all variables to their proper formats (for behind-the-scenes prepared statements)
			"%s",
			"%d"
		)
	);
	return $score;
}

/**
 * Get scores
 *
 * Get scores from the table
 *
 * @global $wpdb The Wordpress Database object interface
 * 
 *
 * TODO: write this function
 * TODO: test this funciton
 *
 */

function omari_get_scores() {
	global $wpdb;

	$result = $wpdb->get_results(
		"SELECT * FROM `" . $wpdb->prefix . "omari_scores`
		ORDER BY `score` DESC;"
	);

	return $result;
}

function omari_generate_table_row($score, $number) {

	echo "<tr>"; // start a new row in the table
	echo "<td>" . $number . ".</td>";  // output the event's name
	echo "<td>" . $score->user_name . "</td>";  // output the event's name
	echo "<td>" . $score->score . "</td>";  // output the event's name
	echo "</tr>";
}


/**
 * Shortcode output
 *
 * @param array $atts Attibutes passed in by shortcode incovation
 *
 * TODO: write admin functions
 * TODO: test this funciton
 *
 */

function omari_shortcode($atts) {

	wp_enqueue_script(
		"jsRev",
		plugin_dir_url(__FILE__) . "jsRev.js",
		array('jquery'),
		'3.0.0',
		false
	);

		echo "
	<div class='img_container'>
		<a href='http://vuband.com/freeomari'><img src='../tomsDir/assets/logo.png' class='image' style='display: block'/></a>
		<img id='omariLeft' src='../tomsDir/assets/omarileft.gif' class='image' style='float: left;margin-left: 20px;display: block;height: 450px;'/>
		<img id='omariRight' src='../tomsDir/assets/omariright.gif' class='image' style='float: right;margin-right: 20px;display: block;height: 450px;'/>
		<h1 id='endScore' style='    position:absolute;
    display: none;
    color:black;
    margin: auto;
    top: 100px;
    left:0;
    right:0;
    bottom:0;
    text-align: center;
    font-family: NES;
    font-size: 60px;' >Score: </h1>
		<img id='winner' src='../tomsDir/assets/trump.jpg' class='image' width='412px' height='512px'/>
		<h1 id='submitScore' class='submit'>Submit Score</h1>
		<div class='img_container'>
			<input id='myinput'  name='usrname' maxlength='12' class='textBox'>
			<img id='submitButton' src='../tomsDir/assets/submit.png' class='button' onclick='omari_score();' style='top: 610px;' />
			<img id='replayButton' src='../tomsDir/assets/play_again.png' onclick='setGameState(1)' class='button' style='top: 670px'/>
			<img id='homeButton' src='../tomsDir/assets/home.png' onclick='setGameState(0)' class='button' style='top: 730px'/>
		</div>
	</div> <!-- ENDS POST GAME -->
	<div class='img_container'>
		<h1 id='newsHeader' class='image' style='position:relative;'>News: V1.0 Launch!</h1>
		<img id='playButton' src='../tomsDir/assets/play.png' class='image' style='display: block; margin: 20px auto; ' onclick='setGameState(1)'/>
		<img id='aboutButton' src='../tomsDir/assets/about.png' class='image' style='display: block; margin: 20px auto' onclick='setGameState(3)'/>
		<img id='hiScoresButton' src='../tomsDir/assets/hi_scores.png' class='image' style='display: block; margin: 20px auto' onclick='setGameState(4)'/>
	</div> <!-- ENDS PRE GAME -->
	<div id='sheet' style='display: none;overflow:auto; height: 500px; width: 530px'>

        <table  style='margin:0 auto;border: 5px solid black;background-color: white;height: 500px; width:500px;font-family:NES;font-size:40px;'>
        <tr>
        <td>
        No.
        </td>
        <td> Name </td>
        <td> Score </td>
        </tr>
         ";

        $num = 0;
		$scores = omari_get_scores();
			foreach($scores as $score) {
				$num += 1;
				omari_generate_table_row($score, $num);
			}
	echo
    "</table>
	</div>
 <div id='about' class='about'>
		ABOUT<br/>
		Created by:<br/>
		Tom Clemente '17<br/>
		@tomclemente_<br/>
		<br/>
		Credits:<br/>
		Julia Vanzelli<br/>
		TH BINN<br/>
		@binncat<br/>
		Ryan Bowman<br/>
		The Villanova Band<br/>
	</div>
	<img id='aboutBackButton' src='../tomsDir/assets/back.png' class='button' style='top: 630px;' onclick='setGameState(0)'/> <!-- ENDS ABOUT -->

	<div id ='stage' class='stage'>
		<h1 id='score'>Score: </h1>
		<div id='wrapper'>
    	<div class='bar green' id='bar'></div>
	</div>
	</div> <!-- ENDS .STAGE -->
	<div id='controls' style='background-color: black;width: 500px; margin: 0 auto' >
		<img id='left' src='../tomsDir/arrows/staticLeft.png'>
		<img id='up' src='../tomsDir/arrows/staticUp.png'>
		<img id='down' src='../tomsDir/arrows/staticDown.png'>
		<img id='right' src='../tomsDir/arrows/staticRight.png'>
	</div> <!-- ENDS #CONTROLS -->
	<audio id='song' loop>
	  <source src='../tomsDir/v8bitfinal.mp3' type='audio/mpeg'>
	</audio>
";



}
add_shortcode("omari", "omari_shortcode");     // register function as a shortcode

?>