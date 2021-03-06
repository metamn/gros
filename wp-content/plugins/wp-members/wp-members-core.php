<?php
/**
 * WP-Members Core Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://butlerblog.com/wp-members
 * Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler 
 * @copyright 2006-2011
 */


/*****************************************************
 * PRIMARY FUNCTIONS
 *****************************************************/
 

if ( ! function_exists( 'wpmem' ) ):
/**
 * The Main Action Function
 *
 * Does actions required at initialization
 * prior to headers being sent.
 *
 * @since 0.1 
 *
 * @global $wpmem_a the action variable also used in wpmem_securify
 * @global $wpmem_regchk contains messages returned from $wpmem_a action functions, used in wpmem_securify
 */
function wpmem()
{	
	global $wpmem_a, $wpmem_regchk;

	if( isset( $_REQUEST['a'] ) ) { $wpmem_a = trim( $_REQUEST['a'] ); }

	switch ($wpmem_a) {

	case ("login"):
		$wpmem_regchk = wpmem_login();
		break;

	case ("logout"):
		wpmem_logout();
		break;

	case ("register"):
		include_once('wp-members-register.php');
		$wpmem_regchk = wpmem_registration('register');
		break;
	
	case ("update"):
		include_once('wp-members-register.php');
		$wpmem_regchk = wpmem_registration('update');
		break;
	
	case ("pwdchange"):
		$wpmem_regchk = wpmem_change_password();
		break;
	
	case ("pwdreset"):
		$wpmem_regchk = wpmem_reset_password();
		break;

	} // end of switch $a (action)

}
endif;


if ( ! function_exists( 'wpmem_securify' ) ):
/**
 * The Securify Content Filter
 *
 * This is the primary function that picks up where wpmem() leaves off.
 * Determines whether content is shown or hidden for both post and
 * pages, and handles special pages such as Member Settings, Register,
 * and Login.
 *
 * @since 2.0
 *
 * @global var $wpmem_a the action variable received from wpmem()
 * @global string $wpmem_regchk contains messages returned from wpmem() action functions
 * @global string $wpmem_themsg
 * @global string $wpmem_captcha_err
 * @global array $post
 * @param string $contnet
 * @return $content
 *
 * @todo update/evaluate for cleaner shortcode, shortcode used to execute twice due to mmembers area/register legacy shortcodes; wpmem_test_shortcode should prevent that/
 * @todo continue testing wpmem_do_excerpt - designed to insert an excerpt if no 'more' tag is found.
 */
function wpmem_securify( $content = null ) 
{ 

	// this is being tested...
	// $content = wpmem_do_excerpt( $content );


	if ( ! wpmem_test_shortcode() ) {
		
		global $wpmem_regchk, $wpmem_themsg, $wpmem_a;
		
		if( $wpmem_regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = __("There was an error with the CAPTCHA form.")."<br /><br />".$wpmem_captcha_err;
		}

		// Block/unblock Posts
		if( !is_user_logged_in() && wpmem_block() == true ) {
		
			// protects comments if user is not logged in
			global $post;
			$post->post_password = wp_generate_password();
		
			include_once('wp-members-dialogs.php');
			
			// show the login and registration forms
			if( $wpmem_regchk ) {
				
				// empty content in any of these scenarios
				$content = '';
	
				switch($wpmem_regchk) {
	
				case "loginfailed":
					$content = wpmem_inc_loginfailed();
					break;
	
				case "success":
					$content = wpmem_inc_regmessage( $wpmem_regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;
	
				default:
					$content = wpmem_inc_regmessage( $wpmem_regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}
	
			} else {
			
				// toggle shows excerpt above login/reg on posts/pages
				if (WPMEM_SHOW_EXCERPT == 1) {
				
					$len = strpos($content, '<span id="more');
					$content = substr($content, 0, $len);
					
				} else {
				
					// empty all content
					$content = '';
				
				}
	
				$content = $content . wpmem_inc_login();
				
				if (WPMEM_NO_REG != 1) { $content = $content . wpmem_inc_registration(); } // toggle turns off reg process for all but registration page.
			}
	

		// For expirations
		// NOTE: there is some reworking needed before exp module final release
		} elseif ( is_user_logged_in() && $chk_securify == 'block' ){
			
			if (WPMEM_USE_EXP == 1) { $content = wpmem_do_expmessage( $content ); }
			
		}
	
		// Members Area and Regitration special pages
		// This section pertains to the special pages using legacy codes ( i.e. <!--members-area--> )
		// This version is the final version to update these - they will be deprecated and 
		// most likely removed in future versions. Start updating to new shortcode versions.
		
		if (is_page('members-area')) { $wpmem_page = "members-area"; }
		if (is_page('register')) { $wpmem_page = "register"; }
		
		if ( $wpmem_page == 'members-area' || $wpmem_page == 'register' ) {
		
			include_once('wp-members-dialogs.php');
			
			if ($wpmem_regchk == "loginfailed") {
				return wpmem_inc_loginfailed();
			}
			
			if (!is_user_logged_in()) {
				if ($wpmem_a == 'register') {
	
					$content = ''; // start with empty content
					
					switch($wpmem_regchk) {
	
					case "success":
						$content = wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
						$content = $content . wpmem_inc_login();
						break;
	
					default:
						$content = wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
						$content = $content . wpmem_inc_registration();
						break;
					}
	
				} elseif ($wpmem_a == 'pwdreset') {
	
					switch($wpmem_regchk) {
	
					case "pwdreseterr":
						$content = wpmem_inc_regmessage( $wpmem_regchk );
						break;
	
					case "pwdresetsuccess":
						$content = wpmem_inc_regmessage( $wpmem_regchk );
						break;
	
					default:
						$content = wpmem_inc_resetpassword();
						break;
					}
	
				} else {
	
					if ( $wpmem_page == 'members-area' ) { $content = wpmem_inc_login( 'members' ); }
					
					if ( $wpmem_page == 'register' || WPMEM_NO_REG != 1 ) { $content = $content . wpmem_inc_registration(); }
				}
	
			} elseif (is_user_logged_in() && $wpmem_page == 'members-area') {
	
				$edit_heading = __('Edit Your Information', 'wp-members');
			
				switch($wpmem_a) {
	
				case "edit":
					$content = wpmem_inc_registration( 'edit', $heading );
					break;
	
				case "update":
	
					// determine if there are any errors/empty fields
	
					if ($wpmem_regchk == "updaterr" || $wpmem_regchk == "email") {
	
						$content = wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
						$content = $content . wpmem_inc_registration( 'edit', $edit_heading );
	
					} else {
	
						//case "editsuccess":
						$content = wpmem_inc_regmessage($wpmem_regchk,$wpmem_themsg);
						$content = $content . wpmem_inc_memberlinks();
	
					}
					break;
	
				case "pwdchange":
	
					switch ($wpmem_regchk) {
					
					case "pwdchangempty":
						$content = $content . wpmem_inc_regmessage( 'pwdchangempty', __('Password fields cannot be empty', 'wp-members') );
						$content = $content . wpmem_inc_changepassword();
						break;
	
					case "pwdchangerr":
						$content = $content . wpmem_inc_regmessage($wpmem_regchk);
						$content = $content . wpmem_inc_changepassword();
						break;
	
					case "pwdchangesuccess":
						$content = $content . wpmem_inc_regmessage($wpmem_regchk);
						break;
	
					default:
						$content = $content . wpmem_inc_changepassword();
						break;				
					}
					break;
	
				// placeholder for expirations
				//case "renew":
					//$content = "insert the renewal process...";
					//wpmem_renew;
					//break;
	
				default:
					$content = $content . wpmem_inc_memberlinks();
					
					// placeholder for expirations
					if (WPMEM_USE_EXP == 1) {
						$addto  = wpmem_user_page_detail(); 
						$output = $output.$addto;
					}
					break;					  
				}
	
			} elseif (is_user_logged_in() && $wpmem_page == 'register') {
			
				$content = $content . wpmem_inc_memberlinks('register');
			
			}	
	
			if ( is_page('members-area') ) { $replacestr = "/\<!--members-area-->/"; }
			if ( is_page('register') )     { $replacestr = "/\<!--reg-area-->/"; }
			
			// the conditional here is allow for use of either the legacy version or 
			// the shortcode on members-area or register pages without preg_replace error
			if ( !$wpmem_sc_page ) { $content = preg_replace( $replacestr, $output, $content ); }
				
		}
		
	}
	
	return $content;
	
} // end wpmem_securify
endif;


add_shortcode ('wp-members', 'wpmem_shortcode');
/**
 * Executes shortcode for settings, register, and login pages
 *
 * @since 2.4 
 *
 * @param array $attr page and status
 * @return string returns the result of wpmem_do_sc_pages
 * @return string returns $content between open and closing tags
 */
function wpmem_shortcode( $attr, $content = null )
{
	if( $attr['page'] ) {
		return wpmem_do_sc_pages( $attr['page'] ); 
	}
	
	if( $attr['status'] ) {
		if( $attr['status'] == 'in' && is_user_logged_in() ) {
			return $content;
		} elseif ( $attr['status'] == 'out' && ! is_user_logged_in() ) {
			return $content;
		}
	}
}


if ( ! function_exists( 'wpmem_login' ) ):
/**
 * Logs in the user
 *
 * Logs in the the user using wp_signon (since 2.5.2). If login 
 * is successful, it redirects and exits; otherwise "loginfailed"
 * is returned.
 *
 * @since 0.1 
 */
function wpmem_login()
{
	$redirect_to = $_POST['redirect_to'];
	if (!$redirect_to) {
		$redirect_to = $_SERVER['PHP_SELF'];
	}

	if ( $_POST['log'] && $_POST['pwd'] ) {
		
		$user_login = sanitize_user( $_POST['log'] );
		
		$creds = array();
		$creds['user_login']    = $user_login;
		$creds['user_password'] = $_POST['pwd'];
		$creds['remember']      = $_POST['rememberme'];
		
		$user = wp_signon( $creds, false );
	
		if ( !is_wp_error($user) ) {
			if ( !$using_cookie )
				wp_setcookie($user_login, $user_pass, false, '', '', $rememberme);
			wp_redirect($redirect_to);
			exit();
		} else {
			return "loginfailed"; // $wpmem_regchk = "loginfailed";
		}
	
	} else {
		//login failed
		return "loginfailed"; // $wpmem_regchk = "loginfailed";
	}	

} // end of login function
endif;


if ( ! function_exists( 'wpmem_logout' ) ):
/**
 * Logs the user out, puts user on home page
 *
 * @since 2.0
 */
function wpmem_logout()
{
	//take 'em to the blog home page
	$redirect_to = get_bloginfo('url');

	wp_clearcookie();
	do_action('wp_logout');
	nocache_headers();

	wp_redirect($redirect_to);
	exit();
}
endif;


if ( ! function_exists( 'wpmem_login_status' ) ):
/**
 * Displays the user's login status
 *
 * @since 2.0
 *
 * @uses wpmem_inc_memberlinks()
 */
function wpmem_login_status()
{
	include_once('wp-members-dialogs.php');
	if (is_user_logged_in()) { echo wpmem_inc_memberlinks( 'status' ); }
}
endif;


if ( ! function_exists( 'wpmem_inc_sidebar' ) ):
/**
 * Displays the sidebar
 *
 * @since 2.0
 *
 * @uses wpmem_do_sidebar()
 */
function wpmem_inc_sidebar()
{
	include_once('wp-members-sidebar.php');
	wpmem_do_sidebar();
}
endif;


if ( ! function_exists( 'widget_wpmemwidget_init' ) ):
/**
 * Initializes the widget
 *
 * @since 2.0
 */
function widget_wpmemwidget_init()
{
	include_once('wp-members-sidebar.php');
	wp_register_sidebar_widget ( 'WP-Members', 'WP-Members', 'widget_wpmemwidget', ''); 
	wp_register_widget_control ( 'WP-Members', 'WP-Members', 'widget_wpmemwidget_control', '' );	
}
endif;


if ( ! function_exists( 'wpmem_change_password' ) ):
/**
 * Handles user password change (not reset)
 *
 * @since 2.1
 *
 * @global $user_ID
 * @return string the value for $wpmem_regchk
 */
function wpmem_change_password()
{
	global $user_ID;
	if ($_POST['formsubmit']) {

		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];
		
		if ( ! $pass1 && ! $pass2 ) { // check for both fields being empty
		
			return "pwdchangempty";

		} elseif ( $pass1 != $pass2 ) { // make sure the fields match

			return "pwdchangerr";

		} else { // update password in db (wp_update_user hashes the password)

			wp_update_user( array ( 'ID' => $user_ID, 'user_pass' => $pass1 ) );
			return "pwdchangesuccess";

		}
	}
	return;
}
endif;


if ( ! function_exists( 'wpmem_reset_password' ) ):
/**
 * Resets a forgotten password
 *
 * @since 2.1
 */
function wpmem_reset_password()
{ 
	// make sure native WP registration functions are loaded
	require_once( ABSPATH . WPINC . '/registration-functions.php');

	if ($_POST['formsubmit']) {

		$username = $_POST['user'];
		$email    = $_POST['email'];

		if (!$username || !$email) { 

			// there was an empty field
			return "pwdreseterr";

		} else {

			if (username_exists($username)) {

				$user = get_userdatabylogin($username);
				
				if( $user->user_email !== $email || ( (WPMEM_MOD_REG == 1) && (get_user_meta($user->ID,'active','true') != 1) ) ) {
					// the username was there, but the email did not match OR the user hasn't been activated
					return "pwdreseterr";
					
				} else {
					
					// generate a new password
					$new_pass = wp_generate_password();
					
					// update the users password
					wp_update_user( array ( 'ID' => $user->ID, 'user_pass' => $new_pass ) );

					// send it in an email
					require_once('wp-members-email.php');
					wpmem_inc_regemail($user->ID,$new_pass,3);
					
					return "pwdresetsuccess";
				}
			} else {

				// username did not exist
				return "pwdreseterr";
			}
		}
	}
	return;
}
endif;


if ( ! function_exists( 'wpmem_no_reset' ) ):
/**
 * Keeps users not activated from resetting their password 
 * via wp-login when using registration moderation.
 *
 * @since 2.5.1
 *
 * @return bool
 */
function wpmem_no_reset() {

	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by_email(trim($_POST['user_login']));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_userdatabylogin($login);
	}
	
	if (WPMEM_MOD_REG == 1) { 
		if (get_user_meta($user_data->ID,'active','true') != 1) { 			
			return false;
		}
	}
	
	return true;
}
endif;


/**
 * Anything that gets added to the the <html> <head>
 *
 * @since 2.2
 */
function wpmem_head()
{ 
	echo "<!-- WP-Members version ".WPMEM_VERSION.", available at http://butlerblog.com/wp-members -->\r\n";
}


/*****************************************************
 * END PRIMARY FUNCTIONS
 *****************************************************/


/*****************************************************
 * UTILITY FUNCTIONS
 *****************************************************/


if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * @since 1.8
 */
function wpmem_create_formfield($name,$type,$value,$valtochk=null,$class='textbox')
{
	switch ($type) {

	case "checkbox":
		if ($class = 'textbox') { $class = "checkbox"; }
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" " . wpmem_selected($value,$valtochk,$type) . " />\n";
		break;

	case "text":
		$value = stripslashes($value);
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\" />\n";
		break;

	case "textarea":
		if ($class = 'textbox') { $class = "textarea"; }
		$str = "<textarea cols=\"20\" rows=\"5\" name=\"$name\" id=\"$name\" class=\"$class\">$value</textarea>";
		break;

	case "password":
		$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\" />\n";
		break;

	case "hidden":
		$str = "<input name=\"$name\" type=\"$type\" value=\"$value\" />\n";
		break;

	case "option":
		$str = "<option value=\"$value\" " . wpmem_selected($value, $valtochk, 'select') . " >$name</option>\n";

	}
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes)
 *
 * @since 0.1
 */
function wpmem_selected($value,$valtochk,$type=null)
{
	if($type == 'select') {
		$issame = 'selected';
	} else {
		$issame = 'checked';
	}
	if($value == $valtochk){ return $issame; }
}
endif;


if ( ! function_exists( 'wpmem_chk_qstr' ) ):
/**
 * Checks querystrings
 *
 * @since 2.0
 */
function wpmem_chk_qstr($url = null)
{
	$permalink = get_option('permalink_structure');
	if (!$permalink) {
		if (!$url) { $url = get_option('home') . "/?" . $_SERVER['QUERY_STRING']; }
		$return_url = $url."&amp;";
	} else {
		if (!$url) { $url = get_permalink(); }
		$return_url = $url."?";
	}
	return $return_url;
}
endif;




/*************************************************************************
	New 2.6 features (these functions may be moved later
**************************************************************************/


if ( ! function_exists( 'wpmem_test_shortcode' ) ):
/**
 * Tests $content for the presence of the [wp-members] shortcode
 *
 * @since 2.6
 *
 * @return bool 
 */
function wpmem_test_shortcode()
{
	global $post;
	
	$pattern = get_shortcode_regex();
	
    preg_match('/'.$pattern.'/s', $post->post_content, $matches);
	
    if (is_array($matches) && $matches[2] == 'wp-members') {
		return true;
    }
}
endif;


if ( ! function_exists( 'wpmem_block' ) ):
/**
 * Determines if content should be blocked
 *
 * @since 2.6
 *
 * @return bool 
 */
function wpmem_block()
{
	if( is_single() ) {
		//$not_mem_area = 1; 
		if (WPMEM_BLOCK_POSTS == 1 && !get_post_custom_values('unblock')) { return true; }
		if (WPMEM_BLOCK_POSTS == 0 &&  get_post_custom_values('block'))   { return true; }
	}

	if( is_page() && !is_page('members-area') && !is_page('register') ) { 
		//$not_mem_area = 1; 
		if (WPMEM_BLOCK_PAGES == 1 && !get_post_custom_values('unblock')) { return true; }
		if (WPMEM_BLOCK_PAGES == 0 &&  get_post_custom_values('block'))   { return true; }
	}
	
	return false;
}
endif;


if ( ! function_exists( 'wpmem_do_sc_pages' ) ):
/**
 * Determines if content should be blocked
 *
 * @since 2.6
 *
 * @param string $page
 * @return $content 
 */
function wpmem_do_sc_pages( $page )
{
	global $wpmem_regchk, $wpmem_themsg, $wpmem_a;
	
	if ( $page == 'members-area' || $page == 'register' ) { 
	
		include_once( 'wp-members-dialogs.php' );
		
		if( $wpmem_regchk == "loginfailed" ) {
			return wpmem_inc_loginfailed();
		}
		
		if( ! is_user_logged_in() ) {
			if( $wpmem_a == 'register' ) {

				switch( $wpmem_regchk ) {

				case "success":
					$content = wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;

				default:
					$content = wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}

			} elseif( $wpmem_a == 'pwdreset' ) {

				switch( $wpmem_regchk ) {

				case "pwdreseterr":
					$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
					$wpmem_regchk = ''; // clear regchk
					break;

				case "pwdresetsuccess":
					$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
					$wpmem_regchk = ''; // clear regchk
					break;

				default:
					$content = $content . wpmem_inc_resetpassword();
					break;
				}

			} else {

				if( $page == 'members-area' ) { $content = $content . wpmem_inc_login( 'members' ); }
				
				// turn off registration on all but the register page.
				if( $page == 'register' || WPMEM_NO_REG != 1 ) { $content = $content . wpmem_inc_registration(); }
			}

		} elseif( is_user_logged_in() && $page == 'members-area' ) {

			$heading = __( 'Edit Your Information', 'wp-members' );
		
			switch( $wpmem_a ) {

			case "edit":
				$content = $content . wpmem_inc_registration( 'edit', $heading );
				break;

			case "update":

				// determine if there are any errors/empty fields

				if( $wpmem_regchk == "updaterr" || $wpmem_regchk == "email" ) {

					$content = $content . wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration( 'edit', $edit_heading );

				} else {

					//case "editsuccess":
					$content = $content . wpmem_inc_regmessage( $wpmem_regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_memberlinks();

				}
				break;

			case "pwdchange":

				switch( $wpmem_regchk ) { 
				
				case "pwdchangempty":
					$content = wpmem_inc_regmessage( $wpmem_regchk, __( 'Password fields cannot be empty', 'wp-members' ) );
					$content = $content . wpmem_inc_changepassword();
					break;

				case "pwdchangerr":
					$content = wpmem_inc_regmessage( $wpmem_regchk );
					$content = $content . wpmem_inc_changepassword();
					break;

				case "pwdchangesuccess":
					$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
					break;

				default:
					$content = $content . wpmem_inc_changepassword();
					break;				
				}
				break;

			// placeholder for expirations...
			//case "renew":
				//$content = "insert the renewal process...";
				//wpmem_renew;
				//break;

			default:
				$content = wpmem_inc_memberlinks();
				
				// placeholder for expirations...
				if (WPMEM_USE_EXP == 1) {
					$addto   = wpmem_user_page_detail(); 
					$content = $content . $addto;
				}
				break;					  
			}

		} elseif( is_user_logged_in() && $page == 'register' ) {

			//return wpmem_inc_memberlinks( 'register' );
			
			$content = $content . wpmem_inc_memberlinks( 'register' );
		
		}
			
	}
	
	if( $page == 'login' ) {
	
		include_once( 'wp-members-dialogs.php' );
		
		if( $wpmem_regchk == "loginfailed" ) {
			$content = wpmem_inc_loginfailed();
		}
		
		if( ! is_user_logged_in() ) {
			$content = $content . wpmem_inc_login( 'login' );
		} else {
			$content = wpmem_inc_memberlinks( 'login' );
		}
		
	}
	
	return $content;
} // end wpmem_do_sc_pages
endif;


if ( ! function_exists( 'wpmem_enqueue_style' ) ):
/**
 * Loads the stylesheet for tableless forms
 *
 * @since 2.6
 */
function wpmem_enqueue_style()
{		
	if ( WPMEM_CSSURL != null ) { 
		$css_path = WPMEM_CSSURL; 
	} else {
		$css_path = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
		$css_path = $css_path."css/wp-members.css";
	}

	wp_register_style('wp-members', $css_path);
	wp_enqueue_style( 'wp-members');
}
endif;




/**
 * Creates an excerpt on the fly if there is no 'more' tag
 *
 * @since 2.6
 */
function wpmem_do_excerpt( $content )
{
    if( ! is_single() && ! is_page() && ! is_search() ) {
    
        // test for 'more' tag or excerpt
		$test = stristr( $content, 'class="more-link"' );
		if( $test ) { 
			
		} else {	
			$content = substr( $content, 0, 300 );
		}
    }
	
	return $content;
	
}
?>