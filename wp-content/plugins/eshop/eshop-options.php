<?php
if ('eshop-options.php' == basename($_SERVER['SCRIPT_FILENAME']))
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
global $wpdb, $eshopoptions;
$opttable=$wpdb->prefix.'eshop_option_names';
$optsettable=$wpdb->prefix.'eshop_option_sets';

?>
<div class="wrap">
<div id="eshopicon" class="icon32"></div><h2><?php _e('Option Sets','eshop'); ?></h2>
<?php eshop_admin_mode(); ?>
<?php
if(isset($_GET['optid']) && !isset($_POST['delete']) && !isset($_POST['eaddopt'])){
?>
<p><a href="admin.php?page=eshop-options.php"><?php _e('Return','eshop'); ?></a></p>
<?php
}
// updating options
if (isset($_POST['delete'])) {
	$optid=$_POST['optid'];
	$wpdb->query($wpdb->prepare("DELETE FROM $opttable where optid='%d'",$optid));
	$wpdb->query($wpdb->prepare("DELETE FROM $optsettable where optid='%d'",$optid));
	echo '<p class="success">'.__('Option Set Deleted','eshop').'</p>';
	unset($_GET['optid']);
}elseif (isset($_POST['update'])) {
	$optid=$_POST['optid'];
	$wpdb->query($wpdb->prepare("DELETE FROM $optsettable where optid='%d'",$optid));
	$x=1;
	$type=$_POST['type'];
	if($type=='2' || $type=='3'){
		echo "<p>".__('Only the first option field is used for this type.','eshop')."</p>\n";
	}
	foreach($_POST['eshop_option'] as $notused=>$named){
		if($named!=''){
			$name=$_POST['eshop_option'][$x];
			$price=$_POST['eshop_price'][$x];
			if(isset($_POST['eshop_weight'][$x]))
				$weight=$_POST['eshop_weight'][$x];
			else
				$weight='';
			if($price=='' || !is_numeric($price))$price='0.00';
			if($weight=='' || !is_numeric($weight))$weight='0.00';
			$wpdb->query($wpdb->prepare("INSERT INTO $optsettable SET optid='%d', name='%s',price='%s',weight='%s'",$optid,$name,$price,$weight));
		}
		if($type=='2' || $type=='3')
			break;
		$x++;
	}
	$name=$_POST['name'];
	$adminname=$_POST['admin_name'];
	$description=$_POST['description'];
	$wpdb->query($wpdb->prepare("UPDATE $opttable SET  name='%s',admin_name='%s',type='%d',description='%s' where optid='%d'",$name,$adminname,$type,$description,$optid));
	echo '<p class="success">'.__('Option Set Updated','eshop').'</p>';
}

//add/edit an option here
if (isset($_POST['eaddopt'])) {
	$optid=$_POST['optid'];
	$x=1;
	$type=$_POST['type'];
	if($type=='2' || $type=='3'){
		echo "<p>".__('Only the first option field is used for this type.','eshop')."</p>\n";
	}
	foreach($_POST['eshop_option'] as $notused=>$named){
		if($named!=''){
			$name=$_POST['eshop_option'][$x];
			$price=$_POST['eshop_price'][$x];
			if(isset($_POST['eshop_weight'][$x]))
				$weight=$_POST['eshop_weight'][$x];
			else
				$weight='';
			if($weight=='' || !is_numeric($weight))$weight='0.00';
			if($price=='' || !is_numeric($price))$price='0.00';
			$wpdb->query($wpdb->prepare("INSERT INTO $optsettable SET optid='%d', name='%s',price='%s',weight='%s'",$optid,$name,$price,$weight));
		}
		if($type=='2' || $type=='3')
			break;
		$x++;
	}
	$name=$_POST['name'];
	$adminname=$_POST['admin_name'];
	$description=$_POST['description'];
	$wpdb->query($wpdb->prepare("UPDATE $opttable SET  name='%s',admin_name='%s',type='%d',description='%s' where optid='%d'",$name,$adminname,$type,$description,$optid));
	echo '<p class="success">'.__('Option Set Created','eshop').'</p>';
	createform($opttable);
}elseif (isset($_POST['create'])) {
	if($_POST['eoption-name']==''){
		echo "<p>".__('Sorry that name isn\'t allowed. Try another name.','eshop')."</p>\n";
	}else{
		$eoption=$_POST['eoption-name'];
		$wpdb->query($wpdb->prepare("INSERT INTO $opttable SET name='%s'",$eoption));
		$optid=$wpdb->get_var( $wpdb->prepare("SELECT optid FROM $opttable where name='%s' order by optid DESC limit 1",$eoption));
		createoptions($optid,$eoption);
	}
}elseif(isset($_GET['optid']) && is_numeric($_GET['optid'])) {
	$optid=$_GET['optid'];
	$myrowres=$wpdb->get_results($wpdb->prepare("select name as optname, price,weight from $optsettable where optid='%d' ORDER by id ASC",$optid));
	$egrab=$wpdb->get_row($wpdb->prepare("select * from $opttable where optid='%d' LIMIT 1",$optid));
	$ename=$egrab->name;
	$adminename=$egrab->admin_name;
	$etype=$egrab->type;
	$edesc=$egrab->description;
	$checkrows=sizeof($myrowres);
	if($checkrows!=0){
		echo "<p>".__('<strong>Warning:</strong> Changing these will affect <strong>all</strong> products using these options','eshop')."</p>";
	}
	$i=1;
	$tbody='';
	foreach($myrowres as $myrow){
		$tbody.="<tr>\n".
		'<th id="eshopnumrow'.$i.'" headers="eshopnum">'.$i.'</th>
		<td headers="eshopoption eshopnumrow'.$i.'"><label for="eshop_option_'.$i.'">'. __('Option','eshop').' '.$i.'</label>
		<input id="eshop_option_'.$i.'" name="eshop_option['.$i.']" value="'.stripslashes(esc_attr($myrow->optname)).'" type="text" size="25" /></td>
		<td headers="eshopprice eshopnumrow'.$i.'"><label for="eshop_price_'.$i.'">'.__('Price','eshop').' '.$i.'</label>
		<input id="eshop_price_'.$i.'" name="eshop_price['.$i.']" value="'.stripslashes(esc_attr($myrow->price)).'" type="text" size="6" /></td>';
		if($eshopoptions['shipping']=='4'){
		$tbody.='<td headers="eshopweight eshopnumrow'.$i.'"><label for="eshop_weight_'.$i.'">'.__('Weight','eshop').' '.$i.'</label>
		<input id="eshop_weight_'.$i.'" name="eshop_weight['.$i.']" value="'.stripslashes(esc_attr($myrow->weight)).'" type="text" size="6" /></td>';
		}
		$tbody.="</tr>\n";
		$i++;
	}
	?>
		<form id="eshopoptionsets" action="" method="post">
			<fieldset>
			<input type = "hidden" name="optid" id="optid" value = "<?php echo $optid; ?>" />
			<label for="name"><?php _e('Display Name','eshop'); ?></label><input type = "text" name="name" id="name" value = "<?php echo stripslashes(esc_attr($ename)); ?>" size="35"/>
			<label for="type"><?php _e('Set display type','eshop'); ?></label>
			<select id="type" name="type">
			<option value="0"<?php if($etype==0) echo ' selected="selected"';?>><?php _e('Dropdown','eshop'); ?></option>
			<option value="1"<?php if($etype==1) echo ' selected="selected"';?>><?php _e('Checkboxes','eshop'); ?></option>
			<option value="2"<?php if($etype==2) echo ' selected="selected"';?>><?php _e('Text','eshop'); ?></option>
			<option value="3"<?php if($etype==3) echo ' selected="selected"';?>><?php _e('Textarea','eshop'); ?></option>
			</select><br />
			<label for="adminname"><?php _e('Short Admin Name','eshop'); ?></label><input type = "text" name="admin_name" id="adminname" value = "<?php echo stripslashes(esc_attr($adminename)); ?>" size="35"/><br />
			<label for="edesc"><?php _e('Description','eshop'); ?></label>
			<textarea id="edesc" name="description" rows="3" cols="80"><?php echo stripslashes(esc_attr($edesc)); ?></textarea>
			<table class="hidealllabels widefat eshoppopt" summary="<?php _e('Product Options by option and price','eshop'); ?>">
			<caption><?php _e('Options for','eshop'); ?> <?php echo stripslashes(esc_attr($ename)); ?></caption>
			<thead><tr><th id="eshopnum">#</th><th id="eshopoption"><?php _e('Option','eshop'); ?></th><th id="eshopprice"><?php _e('Price','eshop'); ?></th><?php if($eshopoptions['shipping']=='4'){?><th id="eshopweight"><?php _e('Weight','eshop'); ?></th><?php } ?></tr></thead>
		<tbody>
	<?php
		echo $tbody;
		extraoptions($i);
	?>
		</tbody></table>
	<p>
	<input type="submit" name="update" id="submit" value="<?php _e('Update','eshop'); ?>" />
	<input type="submit" name="delete" id="submit2" value="<?php _e('Delete','eshop'); ?>" />
	</p>
	</fieldset></form>
	<?php
}else{
	createform($opttable);
}
?>
</div>
<?php
function createform($opttable){
	global $wpdb;
	$myrowres=$wpdb->get_results("select *	from $opttable ORDER BY admin_name,name ASC");
	createnew();
	if(sizeof($myrowres)>0){
		?>
		<h3><?php _e('Existing Option Sets','eshop'); ?></h3>
		<table class="widefat" summary="<?php _e('Option Sets','eshop'); ?>">
		<caption><?php _e('Option sets reference table','eshop'); ?></caption>
		<thead>
		<tr>
		<th id="aname"><?php _e('Admin Name','eshop'); ?></th>
		<th id="pname"><?php _e('Public Name','eshop'); ?></th>
		<th id="type"><?php _e('Type','eshop'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$otypes=array('0'=>__('Dropdown','eshop'), '1'=>__('Checkboxes','eshop'), '2'=>__('Text','eshop'), '3'=>__('Textarea','eshop'));
		$calt=0;
		foreach($myrowres as $row){
			$calt++;
			$alt = ($calt % 2) ? '' : ' class="alternate"';
			echo '<tr'.$alt.'>';
			echo '<td id="row'.$calt.'" headers="aname"><a href="admin.php?page=eshop-options.php&amp;optid='.$row->optid.'">'.stripslashes(esc_attr($row->admin_name))."</a></td>\n";
			echo '<td headers="pname row'.$calt.'"><a href="admin.php?page=eshop-options.php&amp;optid='.$row->optid.'">'.stripslashes(esc_attr($row->name))."</a></td>\n";
			echo '<td headers="pname row'.$calt.'">'.$otypes[$row->type]."</td>\n";
			echo "</tr>\n";
		}
		?>
		</tbody>
		</table>
		<?php
	}
}
function createnew(){
	?>
	<form id="newoption" action="" method="post">
	<fieldset><legend><?php _e('Create New Option Set','eshop'); ?></legend>
	<label for="eoption-name"><?php _e('Name','eshop'); ?></label><input type = "text" name="eoption-name" id="eoption-name" value = "" />
	<p><input type="submit" name="create" id="submit" value="<?php _e('Create','eshop'); ?>" /></p>
	</fieldset>
	</form>
	<?php
}
function createoptions($optid,$name){
global $eshopoptions;
	?>
	<form id="eshopoptionsets" action="" method="post">
	<fieldset>
	<input type = "hidden" name="optid" id="optid" value = "<?php echo $optid; ?>" />
	<label for="name"><?php _e('Name','eshop'); ?></label><input type = "text" name="name" id="name" value = "<?php echo stripslashes(esc_attr($name)); ?>" size="35"/>
	<label for="type"><?php _e('Set display type','eshop'); ?></label>
	<select id="type" name="type">
	<option value="0"><?php _e('Dropdown','eshop'); ?></option>
	<option value="1"><?php _e('Checkboxes','eshop'); ?></option>
	<option value="2"><?php _e('Text','eshop'); ?></option>
	<option value="3"><?php _e('Textarea','eshop'); ?></option>
	</select><br />
	<label for="adminname"><?php _e('Short Admin Name','eshop'); ?></label><input type = "text" name="admin_name" id="adminname" value = "<?php echo stripslashes(esc_attr($name)); ?>" size="35"/><br />

	<label for="edesc"><?php _e('Description','eshop'); ?></label>
	<textarea id="edesc" name="description" rows="3" cols="80"></textarea>
	<table class="hidealllabels widefat eshoppopt" summary="<?php _e('Product Options by option and price','eshop'); ?>">
	<caption><?php _e('Options for','eshop'); ?> <?php echo stripslashes(esc_attr($name)); ?></caption>
	<thead><tr><th id="eshopnum">#</th><th id="eshopoption"><?php _e('Option','eshop'); ?></th><th id="eshopprice"><?php _e('Price','eshop'); ?></th><?php if($eshopoptions['shipping']=='4'){?><th id="eshopweight"><?php _e('Weight','eshop'); ?></th><?php } ?></tr></thead>
	<tbody>
	<?php extraoptions(1); ?>
	</tbody></table>
	<p><input type="submit" name="eaddopt" id="submit" value="<?php _e('Create','eshop'); ?>" /></p>
	</fieldset>
	</form>
	<?php
}
function extraoptions($start){
	global $eshopoptions;
	$i = $start;
	$finish=$start+4;
	while ($i <= $finish) {
		?>
		<tr>
			<th id="eshopnumrow<?php echo $i; ?>" headers="eshopnum"><?php echo $i; ?></th>
			<td headers="eshopoption eshopnumrow<?php echo $i; ?>"><label for="eshop_option_<?php echo $i; ?>"><?php _e('Option','eshop'); ?> <?php echo $i; ?></label><input id="eshop_option_<?php echo $i; ?>" name="eshop_option[<?php echo $i; ?>]" value="" type="text" size="25" /></td>
			<td headers="eshopprice eshopnumrow<?php echo $i; ?>"><label for="eshop_price_<?php echo $i; ?>"><?php _e('Price','eshop'); ?> <?php echo $i; ?></label><input id="eshop_price_<?php echo $i; ?>" name="eshop_price[<?php echo $i; ?>]" value="" type="text" size="6" /></td>
			<?php if($eshopoptions['shipping']=='4'){?>
			<td headers="eshopweight eshopnumrow<?php echo $i; ?>"><label for="eshop_weight_<?php echo $i; ?>"><?php _e('Weight','eshop'); ?> <?php echo $i; ?></label><input id="eshop_weight_<?php echo $i; ?>" name="eshop_weight[<?php echo $i; ?>]" value="" type="text" size="6" /></td>
			<?php }?>

		</tr>	
		<?php
		$i++; 
	}
	?>
	<?php
}
?>