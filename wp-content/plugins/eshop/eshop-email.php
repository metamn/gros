<?php
if ('eshop-email.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');


/*
See eshop.php for information and license terms
*/
if (file_exists(ABSPATH . 'wp-includes/l10n.php')) {
    require_once(ABSPATH . 'wp-includes/l10n.php');
}
else {
    require_once(ABSPATH . 'wp-includes/wp-l10n.php');
}
global $wpdb,$eshopoptions;
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');

// Back to our regularly scheduled script :)

echo '<div class="wrap">';
echo '<h2>'.__('eShop Customer Contact','eshop').'</h2><p>'.__('Use this form to notify the selected customer at any time, for any reason.','eshop').'</p>';

if(isset($_POST['thisemail']) && isset($_GET['viewemail'])){
	$_POST['thisemail'] = stripslashes($_POST['thisemail']);
	$_POST['subject'] = stripslashes($_POST['subject']);
	$body=wordwrap($_POST['thisemail'],75,"\n");
	$from=$_POST['from'];
	$subject=$_POST['subject'];
	$to=$_POST['email'];
	if(isset($from) && $from!=''){
		$headers='From: '.get_bloginfo('name').' <'.$from.">\n";
	}else{
		$headers=eshop_from_address();
	}
	wp_mail($to, $subject, $body, $headers);
	//in case admins want to be copied in on correspondence etc.
	do_action('eshop_copy_admin_order_email',$to, $subject, $body, $headers);
	
	$page='?page='.$_GET['page'].'&amp;view='.$_POST['id'];
	echo '<p class="success">'.__('Email sent successfully.','eshop').'</p>';
	echo '<p><a class="return" href="'.$page.'">'.__('&laquo; Return to Order Detail','eshop').'</a></p>';
	
}elseif(isset($_GET['viewemail'])){
	$view=$wpdb->escape($_GET['viewemail']);
	$dtable=$wpdb->prefix.'eshop_orders';
	$checked=$wpdb->get_var("Select checkid From $dtable where id='$view'");
	$email=$wpdb->get_var("Select email From $dtable where id='$view'");
	$array=eshop_rtn_order_details($checked);
	//grab the template
	$table=$wpdb->prefix.'eshop_emails';
	$thisemail=$wpdb->get_row("Select * From $table where id='2' LIMIT 1");
	$subject=$thisemail->emailSubject;
	$this_email=$thisemail->emailContent;
	// START SUBST
	//yes downloads
	$this_email = eshop_email_parse($this_email,$array,'yes');
	$this_email = str_replace('&#8230;', '...', $this_email);
// For system email - 
	if($eshopoptions['business']!=''){
		$from=$eshopoptions['business'];
	}else{
		//nicked from wp_mail function!
		$from="system@" . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
	}
	?>
	<div id="eshopemailform"><form id="emailer" action="<?php echo esc_url($_SERVER['REQUEST_URI']);?>" method="post">
	<fieldset><legend><?php _e('Send a notification to:','eshop'); ?> <strong><?php echo $email; ?></strong></legend>
	<label for="from"><?php _e('Select a reply-to address:','eshop'); ?><br />
    <select class="pointer" name="from" id="from">
	<?php
	if($eshopoptions['from_email']!=''){
	?>
		<option value="<?php echo $eshopoptions['from_email']; ?>"><?php echo $eshopoptions['from_email']; ?></option>
	<?php
	}
	if($eshopoptions['business']!=''){
	?>
		<option value="<?php echo $eshopoptions['business']; ?>"><?php echo $eshopoptions['business']; ?></option>
	<?php
	}
    if($eshopoptions['sysemails']!=''){
		$sysmailex=explode("\n",$eshopoptions['sysemails']);
		while (list(, $sysMail) = each($sysmailex)) {	
			echo '<option value="'.$sysMail.'">'.$sysMail.'</option>'."\n";  
		} 
	}
	?></select></label><br />
	<label for="subject"><?php _e('Enter your subject line:','eshop'); ?><br /><input type="text" id="subject" name="subject" size="60" value="<?php echo $subject; ?>" /></label><br />
	<label for="thisemail"><?php _e('Enter your custom message:','eshop'); ?><br /><textarea name="thisemail" id="thisemail" cols="70" rows="20"><?php echo $this_email; ?></textarea></label>
	<input type="hidden" id="email" name="email" value="<?php echo $email; ?>" />
	<input type="hidden" id="id" name="id" value="<?php echo $view; ?>" />

	<p class="submit eshop"><input type="submit" id="submit" value="<?php _e('Send Email','eshop'); ?>" /></p>
	</fieldset></form></div>
<?php
}else{

	echo '<p>'.__('Nothing here yet.','eshop').'</p>';
}
echo '</div>';

?>