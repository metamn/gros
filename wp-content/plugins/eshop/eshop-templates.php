<?php
function eshop_template_email(){
	global $wpdb;
	$table=$wpdb->prefix.'eshop_emails';
	if(isset($_POST['edit'])){
		$subject=$wpdb->escape($_POST['subject']);
		$content=$wpdb->escape($_POST['templateContent']);
		$edit=$wpdb->escape($_POST['edit']);
 		$wpdb->query("UPDATE $table set emailSubject='$subject',emailContent='$content' where id='$edit'");
   		echo '<div id="message" class="updated fade"><p><strong>'.__('The Template Has Been Updated','eshop').'</strong></p></div>'."\n";
	}
	if(isset($_GET['eshopuse']) && is_numeric($_GET['eshopuse'])){
		$edit=$_GET['eshopuse'];
		$wpdb->query("UPDATE $table set emailUse=(CASE WHEN emailUse=1 THEN 0 ELSE 1 END) where id='$edit'");
   		echo '<div id="message" class="updated fade"><p><strong>'.__('The Template Has Been Changed','eshop').'</strong></p></div>'."\n";
	}
?>
<div class="wrap">
<div id="eshopicon" class="icon32"></div><h2><?php _e('eShop Email Templates','eshop'); ?></h2>
<?php eshop_admin_mode(); ?>
 <p><?php _e('Use this page to modify your default email templates','eshop'); ?>.</p> 
<table class="eshop widefat" summary="available email templates">
<thead><tr><th id="num">#</th><th id="type"><?php _e('Type','eshop'); ?></th><th id="blank"><?php _e('Template?', 'eshop'); ?></th><th id="act"><?php _e('Active','eshop'); ?></th><th id="chg"><?php _e('Change','eshop'); ?></th></tr></thead>
<tbody>
<?php
$eshoptemplate='1';
if(isset($_GET['eshoptemplate']) && is_numeric($_GET['eshoptemplate']))
	$eshoptemplate=$_GET['eshoptemplate'];


$thisemail=$wpdb->get_results("Select * From $table");
$phpself=get_admin_url().'admin.php?page=eshop-templates.php'; 
$x=1;
foreach($thisemail as $this_email){
	$active='';
	$state=__('Active','eshop');
	if($this_email->id>2){
		if($this_email->emailUse==1) $active=__('Deactivate','eshop').' '.$this_email->id;
		else{
			$active=__('Activate','eshop').' '.$this_email->id;
			$state='';
		}
	}
	$alt = ($x % 2) ? '' : ' class="alternate"';
	if($this_email->emailContent=='') $ewarn=' <span class="ewarn">'.__('Template is blank','eshop').'</span>';
	else $ewarn=' <span class="emailok">'.__('Template exists','eshop').'</span>';
	?>
	<tr<?php echo $alt; ?>><td id="row<?php echo $x; ?>" headers="num"><?php echo $this_email->id; ?></td><td headers="row<?php echo $x; ?> num"><a href="<?php echo $phpself.'&amp;eshoptemplate='.$this_email->id; ?>#edit_section" title="<?php _e('edit','eshop'); ?>"><?php echo $this_email->emailType; ?></a></td>
	<td headers="row<?php echo $x; ?> blank"><?php echo $ewarn; ?></td>
	<td headers="row<?php echo $x; ?> act"><?php echo $state; ?></td><td headers="row<?php echo $x; ?> chg"><a href="<?php echo $phpself.'&amp;eshopuse='.$this_email->id; ?>"><?php echo $active; ?></a></td></tr>
	<?php
	$x++;
}
?>
</tbody>
</table>

</div>
<div class="wrap">
<?php
$thisemail=$wpdb->get_row("Select emailType, emailSubject,emailContent From $table where id=$eshoptemplate");
?>
<h2 id="edit_section"><?php _e('Email Template Editor','eshop'); ?></h2>
 <p><?php _e('Use this simple file editor to modify the default email template file.','eshop'); ?></p>
 <form method="post" action="" id="edit_box">
  <fieldset>
   <legend><?php _e('Template:','eshop'); ?> <?php echo $thisemail->emailType; ?> </legend>
   	<label for="subject"><?php _e('Subject','eshop'); ?><br /><input type="text" id="subject" name="subject" size="60" value="<?php echo htmlspecialchars(stripslashes($thisemail->emailSubject)); ?>" /></label><br />

   <label for="stylebox"><?php _e('Email Content','eshop'); ?></label><br />
<textarea rows="20" cols="80" id="stylebox" name="templateContent">
<?php 
echo htmlspecialchars(stripslashes($thisemail->emailContent));
?>
</textarea>
	<input type="hidden" name="edit" value="<?php echo $eshoptemplate;?>" />
	<input type="hidden" name="eshoptemplate" value="<?php echo $eshoptemplate;?>" />
   <p class="submit eshop"><input type="submit" class="button-primary" value="<?php _e('Update Template','eshop'); ?>" name="submit" /></p>
  </fieldset>
</form>
</div>
<div class="wrap">
<h2><?php _e('Template tags for Email Content','eshop'); ?></h2>
<ul>
<li><strong>{STATUS}</strong> - <?php _e('the status of the order.','eshop'); ?></li>
<li><strong>{FIRSTNAME}</strong> - <?php _e('Customers First Name.','eshop'); ?></li>
<li><strong>{NAME}</strong> - <?php _e('Customers Full Name','eshop'); ?></li>
<li><strong>{EMAIL}</strong> - <?php _e('Customers Email address','eshop'); ?></li>
<li><strong>{CART}</strong> - <?php _e('The contents of the customers order (i.e. their shopping cart)','eshop'); ?></li>
<li><strong>{DOWNLOADS}</strong> - <?php _e('A Download link along with the customers email address and password. <em>Only used when an order contains downloads</em>. (not used for cash or bank sales).','eshop'); ?></li>
<li><strong>{ADDRESS}</strong> - <?php _e('Customers Address','eshop'); ?></li>
<li><strong>{REFCOMM}</strong> - <?php _e('The reference and other messages provided by the customer.','eshop'); ?></li>
<li><strong>{CONTACT}</strong> - <?php _e('Customers shipping address phone number.','eshop'); ?></li>
<li><strong>{ORDERDATE}</strong> - <?php _e('Date and time of order.','eshop'); ?></li>
<?php do_action('eshopemailtags'); ?>
</ul>
<p><?php _e('To change the default email that notifies the customer when signing up to you site(if enabled) you may need to install another plugin such as <a href="http://wordpress.org/extend/plugins/new-user-email-set-up/">New user Email Set Up</a>.','eshop'); ?></p>
</div>
	<?php 
}
?>