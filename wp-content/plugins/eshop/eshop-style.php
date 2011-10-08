<?php
function eshop_process_style($styleFile) {
	global $wpdb;
	//processes style page forms
	if(!empty($_POST['cssFile'])){
		//update css file
    	$newfile = stripslashes($_POST['cssFile']);      	
		if(is_writeable($styleFile)) {
   			$f = fopen($styleFile, 'w+');
         	fwrite($f, $newfile);
        	fclose($f);
    		echo ' <div id="message" class="updated fade"><p><strong>'.__('The Stylesheet Has Been Updated','eshop').'</strong></p></div>'."\n";
		} 
	}
	if(!empty($_POST['usestyle'])){
		$eshopoptions = get_option('eshop_plugin_settings');
		$eshopoptions['style']=$wpdb->escape($_POST['usestyle']);
		update_option('eshop_plugin_settings',$eshopoptions);
		if($_POST['usestyle']=='yes'){
			$use=__('Default style has been applied.','eshop');
		}else{
			$use=__('Default style has been turned off.','eshop');
		}
		echo ' <div id="message" class="updated fade"><p><strong>'.$use.'</strong></p></div>'."\n";

	}
	return;
}
function eshop_form_admin_style(){
	//make sure options exist for the style page
	//config options
	$eshopurl=eshop_files_directory();
    $styleFile = $eshopurl['0'].'eshop.css';
    $style=eshop_process_style($styleFile);
    $eshopoptions = get_option('eshop_plugin_settings');

    if(!is_writeable($styleFile)) {
  		echo ' <div id="message" class="error fade"><p>'.__('<strong>Warning!</strong> The css file is not currently editable/writable! File permissions must first be changed.','eshop').'</p>
	   	</div>'."\n";
 	}
?>
<div class="wrap">
<div id="eshopicon" class="icon32"></div><h2><?php _e('eShop Styles','eshop'); ?></h2>
<?php eshop_admin_mode(); ?>
</div>
<div class="wrap">
<h2><?php _e('Default Style','eshop'); ?></h2>
<?php
if(@file_exists(get_stylesheet_directory().'/eshop.css')) {
echo '<p>';
_e('Your active theme has an eshop style sheet, eshop.css, and will be used in preference to the default style below. Therefore changes made via the style editor below will not show on your site.','eshop');
echo '</p>';
}else{
?>
<p><?php _e('Default style is used by default. You can edit this via the editor below, or choose not to use it.','eshop'); ?></p>
<form action="themes.php?page=eshop-style.php" method="post" id="style_form" name="style">
 <fieldset>
  <legend><?php _e('Use Default Style','eshop'); ?></legend>
  <?php
  if($eshopoptions['style']=='yes'){
  	$yes=' checked="checked"';
  	$no='';
  }else{
  	$no=' checked="checked"';
  	$yes='';
  }
  ?>
  <input type="radio" id="usestyle" name="usestyle" value="yes"<?php echo $yes; ?> /><label for="usestyle"><?php _e('Yes','eshop'); ?></label> 
  <input type="radio" id="nostyle" name="usestyle" value="no"<?php echo $no; ?> /><label for="nostyle"><?php _e('No','eshop'); ?></label>
  <p class="submit eshop"><input type="submit" value="<?php _e('Amend','eshop'); ?>" name="submit" /></p>

</fieldset>
</form>
<?php
}
//check for new css
$plugin_dir=WP_PLUGIN_DIR;
$dirs=wp_upload_dir();
$upload_dir=$dirs['basedir'];
$eshop_goto=$upload_dir.'/eshop_files/eshop.css';
$eshop_from=$plugin_dir.'/eshop/files/eshop.css';
$eshopver=split('\.',ESHOP_VERSION);
$left_string = file_get_contents($eshop_from, true);
$right_string = file_get_contents($eshop_goto, true);

?>
</div>
<div class="wrap">
<h2><?php _e('Style Editor','eshop'); ?></h2>
 <p><?php _e('Use this simple <abbr><span class="abbr" title="Cascading Style Sheet">CSS</span></abbr> file editor to modify the default style sheet file.','eshop'); ?></p>
 <form method="post" action="themes.php?page=eshop-style.php" id="edit_box">
  <fieldset>
   <legend><?php _e('Style File Editor.','eshop'); ?></legend>
   <label for="stylebox"><?php _e('Edit Style','eshop'); ?></label><br />
	<textarea rows="20" cols="80" id="stylebox" name="cssFile"><?php 
	if(!is_file($styleFile))
		$error = 1;

	if(!isset($error) && filesize($styleFile) > 0) {
		$f="";
		$f = fopen($styleFile, 'r');
		$file = fread($f, filesize($styleFile));
		echo $file;
		fclose($f);
	} else {
		_e('Sorry. The file you are looking for could not be found','eshop');
	}
?></textarea>
   <p class="submit eshop"><input type="submit" class="button-primary" value="<?php _e('Update Style','eshop'); ?>" name="submit" /></p>
  </fieldset>
</form>
</div>
	<?php 
	$left_string  = normalize_whitespace($left_string);
	$right_string = normalize_whitespace($right_string);
	if(isset($_GET['diff'])){
		echo '<div class="wrap" id="diff">';
		echo wp_text_diff( $right_string,$left_string, array( 'title' => __('Comparing Current Style with latest installed version of eShop','eshop'), 'title_right' => __('Latest(from plugin)','eshop'), 'title_left' => __('Current (in use)','eshop') ) );
		echo '</div>';
	}elseif(trim($left_string)!=trim($right_string)){
		echo '<div class="wrap">';
		echo '<p>'.__('There may have been updates to the style.','eshop').' <a href="themes.php?page=eshop-style.php&amp;diff#diff">'.__('Compare Current Style with latest installed version of eShop.','eshop').'</a></p>';
		echo '</div>';
	}else{
		echo '<div class="wrap">';
		echo '<p>'.__('Your CSS matches that included with eShop.','eshop').'</p>';
		echo '</div>';
	}
}
?>