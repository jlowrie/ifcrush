<?php
/**
 * @package IFCRush
 * @version 0.1
 */
/*
Plugin Name: IFC Rush
Description: This plugin stores data for Frats and Rushees for rush events.
Author: Lowrie
Version: 0.1
Author URI: nope
*/
global $ifcrush_db_version;
$ifcrush_db_version = "1.0";
global $debug;
$debug = 1;


/**
 * ifcrush_install() - creates the tables that store Frats/Rushees/Events
 **/
function ifcrush_install(){

	global $wpdb;
	global $ifcrush_db_version;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$event_table_name = $wpdb->prefix . "ifc_event";    
	$sql = 	"CREATE TABLE $event_table_name (
		eventDate date not null,
		title varchar(30) not null,
		eventID int not null auto_increment,
		fratID varchar(3) not null,
		PRIMARY KEY(eventID)
	) engine = InnoDB;";
   dbDelta( $sql );

	$table_name = $wpdb->prefix . "ifc_bid";    
	$sql = 	"CREATE TABLE $table_name(
		bidstat int not null,
		netID		varchar(6) not null,
		fratID		varchar(3) not null
	) engine = InnoDB;";
   dbDelta( $sql );

	$table_name = $wpdb->prefix . "ifc_eventreg";    
	$sql = 	"CREATE TABLE $table_name (
		pnm_netID		varchar(6) not null,
		eventID int not null auto_increment,
		PRIMARY KEY(pnm_netID, eventID),
		FOREIGN KEY (eventID) references $event_table_name(eventID)
	) engine = InnoDB;";
   	dbDelta( $sql );
   
   add_option( "ifcrush_db_version", $ifcrush_db_version );
}
register_activation_hook( __FILE__, 'ifcrush_install' );

//register_activation_hook( __FILE__, 'ifcrush_install_data' );


/** ifcrush_deactivate() - cleans up when the plugin is deactived, 
 ** delete database tables.  Careful of the order of deletion!
 **/
function ifcrush_deactivate()
{
    global $wpdb; 
    
	/** drop this first before deleting event **/    
	$table_name = $wpdb->prefix . "ifc_eventreg";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
 	/** drop this first before deleting frat **/       
    $table_name = $wpdb->prefix . "ifc_bid";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
    /** drop this first before deleting frat **/       
    $table_name = $wpdb->prefix . "ifc_event";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

}
register_deactivation_hook( __FILE__, 'ifcrush_deactivate');

/**
 * Add stylesheet to the page
 **/
function safely_add_stylesheet() {
	wp_enqueue_style( 'prefix-style', plugins_url('css/ifcrushstyle.css', __FILE__) );
}
add_action( 'wp_enqueue_scripts', 'safely_add_stylesheet' );

/* supposedly the correct way to load jquery */
add_action( 'wp_enqueue_scripts', 'load_jquery' );

function load_jquery() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', "http://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css");
}

/* supposedly the correct way to load jquery */
add_action( 'wp_enqueue_scripts', 'load_ifcrush' );
function load_ifcrush(){
    wp_enqueue_script( 'ifcrush_script', plugins_url( 'js/formvalidate.js' , __FILE__ ), array(), null, true);
}


/**
 * Redirect user after successful login. - this needs to be after the include
 * for ifcrush_user_support.php because it uses the user functions
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged users data.
 * @return string
 */

function my_login_redirect( $redirect_to, $request, $user ) {

	function is_user_an_rc1($current_user){
		$key = 'ifcrush_role';
		$single = true;
		$user_role = get_user_meta($current_user->ID, $key, $single ); 
		return($user_role == 'rc');
	}

	function is_user_a_pnm1($current_user){
		$key = 'ifcrush_role';
		$single = true;
		$user_role = get_user_meta($current_user->ID, $key, $single ); 
		return($user_role == 'pnm');
	}

	//is there a user to check?
	global $user;
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} else if (is_user_an_rc1($user)){
			return home_url()."/?page_id=64"; // HACK HACK HACK fix the number

		} else if (is_user_a_pnm1($user)){
			return home_url()."/?page_id=66";  // HACK HACK HACK fix the number
		}
	} else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );

include 'ifcrush_event.php';
include 'ifcrush_eventreg.php';
include 'ifcrush_reports.php';
/**
 * These are the functions to wire in the shortcodes
 **/ 
 
include 'ifcrush_user_support.php';
add_action( 'register_form', 'ifcrush_register_form' );

include 'ifcrush_frat.php';  /** This has all the Frat table support **/
add_shortcode( 'ifcrush_frat',   'ifcrush_frat' );

include 'ifcrush_pnm.php';  /** This has all the Rushee table support **/
add_shortcode( 'ifcrush_pnm',   'ifcrush_pnm' );

