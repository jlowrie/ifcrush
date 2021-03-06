<?php
/**
 **  This file contains all the support functions for report functions
 **/
 
/**  This is the short code ifcrush_report_rusheesbyfrat entry point **/
function ifcrush_display_reports(){

	if (!is_user_logged_in()) {
		echo "sorry you must be logged use reporting";
		return;
	}
	
	$current_user = wp_get_current_user();
	
	if (is_user_an_rc($current_user)){
		/* pass user info to reporting function so only
		 * that frat's info is displayed
		 */
		$fratLetters =  get_frat_letters($current_user);
		ifcrush_display_frat_events($fratLetters);
	} else {
		/* assume its an admin */
		ifcrush_display_events_by_frat_form(isset($_POST['letters'])?$_POST['letters']:"" );
		if (isset($_POST['reportype']))
			ifcrush_report_handle_form();
	}
}

function ifcrush_display_eventsbypnms_form($frat_letters){
?>
	<legend>PNMS by Fraternity Event </legend>
	<fieldset>
	<form method="post">
		<?php ifcrush_create_frat_menu($frat_letters); ?>
		<input type="hidden" name="reportype" value="pnmsbyfratevent" />
		<input type="submit" value="Create Report"/>
	</form>
	</fieldset>
<?php
}

function ifcrush_display_events_by_frat_form($frat_letters){
?>
	<legend>Select a Fraternity to display their events</legend>
	<fieldset>
	<form method="post">
		<?php ifcrush_create_frat_menu($frat_letters); ?>
		<input type="hidden" name="reportype" value="displayfratevents" />
		<input type="submit" value="Create Report"/>
	</form>
	</fieldset>
<?php
}
function ifcrush_report_handle_form() { 
// 
	global $debug;
	if ($debug){
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
		
	switch($_POST['reportype']) {
		case 'pnmsbyfratevent':
			ifcrush_report_pnmsbyfratevent($_POST['letters']);
			break;
		case 'displayfratevents':
			ifcrush_display_frat_events($_POST['letters']);
			break;
		default:
			echo "no report specified";
	} 
	
} 

function ifcrush_display_frat_events($frat) {

	global $wpdb;

	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT * FROM $event_table_name where fratID='$frat'";
					
	$allresults = $wpdb->get_results($query);
	//echo "<pre>"; print_r($allresults); echo "</pre>";
	
	if ($allresults) {
		foreach ($allresults as $result) {
		?>
			<div class="reportrow"><?php echo "$result->fratID $result->title "; ?></div>
		<?php
		}
	} 
	else { 
		?><h2>No results!</h2><?php
	}
} 

/** This is a report function.  A function should be added for 
 ** each desired report, and the appropriate case should be added to handle form.
 **/ 
function ifcrush_create_frat_report($frat_letters) {
	global $wpdb;

	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT * FROM $eventreg_table_name 
					JOIN $event_table_name on 
					$eventreg_table_name.eventID = $event_table_name.eventID
					where fratID='$frat_letters' order by pnm_netID";
		
	//echo "query is $query";			
	$allresults = $wpdb->get_results($query);
	
	if ($allresults) {
	
		foreach ($allresults as $r){
			?><div class="reportrow">
				<?php echo get_pnm_name_by_netID($r->pnm_netID) . " $r->title "; ?>
			</div>
			<?php
		}

	} else {
		echo "no results!";
	}
} 

/** creates html for a selection menu for frats
 **/
function ifcrush_create_frat_menu($current){
	$frats = get_all_frats();
?>
	<select name="letters">
<?php
	echo "<option value=\"none\">select fraternity</option>\n";
	foreach ($frats as $frat) {
		$ifcrush_frat_letters = $frat['ifcrush_frat_letters'];
		$ifcrush_frat_fullname = $frat['ifcrush_frat_fullname'];
		if ($ifcrush_frat_letters == $current) {
			echo "<option value=\"$ifcrush_frat_letters\" selected=\"selected\">$ifcrush_frat_fullname</option>\n";
		} else {
			echo "<option value=\"$ifcrush_frat_letters\">$ifcrush_frat_fullname</option>\n";
		}
	}
?>
	</select>
<?php
}

// 		/* get a timestamp */
// 		$date = new DateTime();
// 		$ts = $date->format( 'Y-m-d H:i:s' );
// 		// A name with a time stamp, to avoid duplicate filenames
// 		$filename = "report-$ts.csv";
// 	
// 		// Tells the browser to expect a CSV file and bring up the
// 		// save dialog in the browser
// 		header( 'Content-Type: text/csv' );
// 		header( 'Content-Disposition: attachment;filename='.$filename);
// 
// 		// This opens up the output buffer as a "file"
// 		$fp = fopen('php://output', 'w');
// 
// 		// Get the first record
// 		$hrow = array($allresults[0]);
// 
// 		// Extracts the keys of the first record and writes them
// 		// to the output buffer in CSV format
// 		fputcsv($fp, array_keys($hrow));
// 
// 		// Now put the rest of the data in the file
// 		foreach ($allresults as $result) {
// 			//echo "loop<br>";
// 			$resultarray = (array)$result;
// 			fputcsv($fp, $resultarray);
// 		}
// 		// Close the output buffer (Like you would a file)
//     	fclose($fp);
//     
//     	// Send the size of the output buffer to the browser
//         $contLength = ob_get_length();
//         header( 'Content-Length: '.$contLength);
?>