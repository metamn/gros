<?php
if ('eshop-discount-codes.php' == basename($_SERVER['SCRIPT_FILENAME']))
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

function eshop_discounts_manager() {
	global $wpdb;
	include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
	$legtext=__('Add a discount code','eshop'); 
	$subtext=__('Submit','eshop');
	$edit=false;
	$phpself='admin.php?page=eshop-discount-codes.php';
	$disctable=$wpdb->prefix.'eshop_discount_codes';
	//blank - will change as we check things
	$eshop_code=$eshop_percent=$eshop_remain=$eshop_used=$eshop_live=$eshop_free_ship=$eshop_code_date=$eshop_code_type='';
	$editid='0';
	//delete
	if(isset($_GET['delcode']) && is_numeric($_GET['delcode'])){
		$id=$_GET['delcode'];
		$wpdb->query("DELETE FROM $disctable WHERE id='$id' limit 1");
		echo '<div class="updated fade"><p>'.__('Discount code deleted.','eshop').'</p></div>';
	}
	
	//edit
	if(isset($_GET['editcode']) && is_numeric($_GET['editcode'])){
		$editid=$_GET['editcode'];
		$row=$wpdb->get_row("SELECT * FROM $disctable WHERE id='$editid'");
		$eshop_code=$row->disccode;
		$eshop_code_type=$row->dtype;
		$eshop_percent=$row->percent;
		$eshop_remain=$row->remain;
		$eshop_used=$row->used;
		$eshop_live=$row->live;
		$eshop_code_date=$row->enddate;
		if($eshop_code_date=='0000-00-00')
			$eshop_code_date='';
		$edit=true;
		$legtext=__('Edit a discount code','eshop'); 
		$subtext=__('Update','eshop');
	}
	//new or edit
	
	if(isset($_POST['editid'])){
		$eshop_id=$wpdb->escape(trim($_POST['editid']));
		if(isset($_POST['eshop_live']))
			$eshop_live='yes';
		else
			$eshop_live='no';
		if(isset($_POST['eshop_code_type']))
			$eshop_code_type=$_POST['eshop_code_type'];
		else
			$eshop_code_type='';
		if(isset($_POST['eshop_percent']))
			$eshop_percent=$_POST['eshop_percent'];
		else
			$eshop_percent='';
		$eshop_code_month=$_POST['eshop_code_month'];
		$eshop_code_day=$_POST['eshop_code_day'];
		$eshop_code_year=$_POST['eshop_code_year'];
		//error check - first check if discount
		switch($eshop_code_type){
			case '':
				$error[]=__('You must choose a discount code type','eshop');
				break;
			case '1':
			case '2':
			case '3':
				if(!is_numeric($eshop_percent) || $eshop_percent>100)
					$error[]=__('Percentage must be a number no greater than 100.00','eshop');
				elseif($eshop_percent<=0)
					$error[]=__('Percentage must be a number above 0','eshop');
				break;
		}
		//error check if date is required it must be valid
		switch($eshop_code_type){
			case '2':
			case '3':
			case '5':
			case '6':
				if(!checkdate($eshop_code_month, $eshop_code_day, $eshop_code_year))
					$error[]=__('The date you have chosen is not valid','eshop');
				break;
		}
		//standard errors
		if(isset($_POST['eshop_code']))
			$eshop_code=$_POST['eshop_code'];
		else
			$eshop_code=='';
			
		if($eshop_code=='')
			$error[]=__('You must specify a code','eshop');
		
		if($eshop_code!=''){
			$ecode=$wpdb->escape(trim(strtolower($eshop_code)));
			$ecount=$wpdb->get_var("SELECT COUNT(id) FROM $disctable WHERE LOWER(disccode)='$ecode' && id!='$eshop_id'");
			if($ecount!=0)
				$error[]=__('That code already exists','eshop');
		}
		
		if(isset($_POST['eshop_remain']))
			$eshop_remain=$_POST['eshop_remain'];
		else
			$eshop_remain='';
			
		if((!is_numeric($eshop_remain) || $eshop_remain<0) && $eshop_remain!='')
			$error[]=__('How many times can this be used - must be numeric, or blank','eshop');
		
		if(isset($error)){
			echo '<div class="error fade"><p>'.__('There were some errors:','eshop').'</p>';
			echo '<ul>';
			foreach($error as $err)
				echo '<li>'.$err."</li>\n";
			echo "</ul></div>\n";
		}else{
			//no errors!
			//create date
			$eshop_code_date=$eshop_code_year.'-'.$eshop_code_month.'-'.$eshop_code_day;
			$eshop_id=$wpdb->escape(trim($_POST['editid']));
			$eshop_code=$wpdb->escape(trim($_POST['eshop_code']));
			$eshop_percent=$wpdb->escape($_POST['eshop_percent']);
			$eshop_remain=$wpdb->escape($_POST['eshop_remain']);
			$eshop_code_type=$wpdb->escape($_POST['eshop_code_type']);
			$eshop_code_month=$wpdb->escape(trim($_POST['eshop_code_month']));
			$eshop_code_day=$wpdb->escape(trim($_POST['eshop_code_day']));
			$eshop_code_year=$wpdb->escape(trim($_POST['eshop_code_year']));
			if($eshop_id!='0'){
				//edit
				//$wpdb->query($wpdb->prepare("UPDATE $stocktable set available=$meta_value where post_id=$id"));

				$query="UPDATE $disctable SET 
				dtype='$eshop_code_type', 
				disccode='$eshop_code',
				percent='$eshop_percent',
				remain='$eshop_remain',
				enddate='$eshop_code_date',
				live='$eshop_live'
				WHERE id='$eshop_id' limit 1";
				$wpdb->query($query);
				echo '<div class="updated fade"><p>'.__('Discount code details updated','eshop').'</p></div>';
			}else{
				//new
				$query="INSERT INTO $disctable 
				(dtype,disccode,percent,remain,enddate,live)
				VALUES
				('$eshop_code_type','$eshop_code','$eshop_percent','$eshop_remain','$eshop_code_date','$eshop_live')";
				$wpdb->query($wpdb->prepare($query));
				echo '<div class="updated fade"><p>'.__('Discount code details entered','eshop').'</p></div>';
				//resetvalues
				$eshop_code=$eshop_percent=$eshop_remain=$eshop_used=$eshop_live=$eshop_free_ship=$eshop_code_date=$eshop_code_type='';
				$editid='0';
			}
		}


		
	}

	if($edit==false){
		$eshop_suggest='<p>'.__('Suggested random codes:','eshop').'</p>';
		$eshop_suggest.='<ul>'."\n";
		for($i=1;$i<=3;$i++){
			$eshop_suggest.='<li>'.eshop_random_code('12').'</li>'."\n";
		}
		$eshop_suggest.="</ul>\n";
			
	}else{
		$eshop_suggest='<p><a href="'.$phpself.'">'.__('Enter new code','eshop').'</a></p>';
	}
	?>
	<div class="wrap">
	<div id="eshopicon" class="icon32"></div><h2><?php _e('eShop Discount Codes','eshop'); ?></h2>
	<?php eshop_admin_mode(); ?>
	<div id="eshopdisccodesform">
	<?php echo $eshop_suggest; ?>
	<form id="eshopdisccodes" action="<?php echo esc_url($_SERVER['REQUEST_URI']);?>" method="post">
		<fieldset><legend><?php echo $legtext; ?></legend>
			<p><label for="eshop_code_type"><?php _e('Discount type','eshop'); ?></label>
			<select name="eshop_code_type" id="eshop_code_type">
		   	<option value=""><?php _e('Please select','eshop'); ?></option>
		   	<optgroup label="<?php _e('Percentage','eshop'); ?>">
			<option value="1"<?php echo $eshop_code_type=='1' ? ' selected="selected"' : ''; ?>><?php _e('%  - Limited by Uses','eshop'); ?></option>
			<option value="2"<?php echo $eshop_code_type=='2' ? ' selected="selected"' : ''; ?>><?php _e('%  - Limited by Date','eshop'); ?></option>
			<option value="3"<?php echo $eshop_code_type=='3' ? ' selected="selected"' : ''; ?>><?php _e('%  - Limited by Uses and Date','eshop'); ?></option>
			</optgroup>
			<optgroup label="<?php _e('Free Shipping','eshop'); ?>">
			<option value="4"<?php echo $eshop_code_type=='4' ? ' selected="selected"' : ''; ?>><?php _e('Free Ship - Limited by Uses','eshop'); ?></option>
			<option value="5"<?php echo $eshop_code_type=='5' ? ' selected="selected"' : ''; ?>><?php _e('Free Ship - Limited by Date','eshop'); ?></option>
			<option value="6"<?php echo $eshop_code_type=='6' ? ' selected="selected"' : ''; ?>><?php _e('Free Ship - Limited by Uses and Date','eshop'); ?></option>
			</optgroup>
			</select></p>
			<p><label for="eshop_code"><?php _e('Code','eshop'); ?></label><br />
			<input type="text" id="eshop_code" name="eshop_code" size="30" value="<?php echo $eshop_code; ?>" /></p>
			<p><label for="eshop_percent"><?php _e('Discount percentage:','eshop'); ?></label><br />
			<input type="text" id="eshop_percent" name="eshop_percent" size="4" value="<?php echo $eshop_percent; ?>" /></p>
			<p><label for="eshop_remain"><?php _e('How many times can this be used (leave blank for unlimited)','eshop'); ?></label><br />
			<input type="text" id="eshop_remain" name="eshop_remain" size="4" value="<?php echo $eshop_remain; ?>" /></p>
			<fieldset><legend><?php _e('End Date','eshop'); ?></legend>
			<p><label for="eshop_code_year"><?php _e('Year','eshop'); ?></label>
				<select name="eshop_code_year" id="eshop_code_year">
				<?php
				// work this out!!!
				$eshopdate=date('Y-m-d',mktime(0, 0, 0, date("m") , date("d"), date("Y")));
				if($eshop_code_date!='')
					$eshopdate=$eshop_code_date;

				list($eshop_code_year, $eshop_code_month, $eshop_code_day) = explode('-', $eshopdate);


				for($i=date('Y');$i<=date('Y')+5;$i++){
					if($i==$eshop_code_year){
						$sel=' selected="selected"';
					}else{
						$sel='';
					}
					echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>'."\n";
				}
				?>
			  </select>
				<label for="eshop_code_month"><?php _e('Month','eshop'); ?></label>

				  <select name="eshop_code_month" id="eshop_code_month">
				<?php

				for($i=1;$i<=12;$i++){
					if($i==$eshop_code_month){
						$sel=' selected="selected"';
					}else{
						$sel='';
					}
					echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>'."\n";
				}
				?>
			  </select>
				<label for="eshop_code_day"><?php _e('Day','eshop'); ?></label>

				  <select name="eshop_code_day" id="eshop_code_day">
				<?php

				for($i=1;$i<=31;$i++){
					if($i==$eshop_code_day){
						$sel=' selected="selected"';
					}else{
						$sel='';
					}
					echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>'."\n";
				}
				?>
	 			</select></p>
	 		</fieldset>
			<p><input id="eshop_live" name="eshop_live" value="yes"<?php echo $eshop_live=='yes' ? ' checked="checked"' : ''; ?> type="checkbox" /> <label for="eshop_live" class="selectit"><?php _e('Active?','eshop'); ?></label></p>
			<input type="hidden" name="editid" value="<?php echo $editid; ?>" />
			</fieldset>
			<p class="submit eshop"><input type="submit" id="submit" class="button-primary" value="<?php echo $subtext; ?>" /></p>
		</form>
	</div>
	<?php
	$max = $wpdb->get_var("SELECT COUNT(id) FROM $disctable WHERE id > 0");
	if($max>0){
		?>
		<div id="eshopdisccodesexisting">
		<h3><?php _e('Current Discount Codes','eshop'); ?></h3>
		<table class="widefat" summary="<?php _e('Discount Codes','eshop'); ?>">
		<caption><?php _e('Discount codes reference table','eshop'); ?></caption>
		<thead>
		<tr>
		<th id="code"><?php _e('Code','eshop'); ?></th>
		<th id="disc"><?php _e('Discount','eshop'); ?></th>
		<th id="type"><?php _e('Type','eshop'); ?></th>
		<th id="remain"><?php _e('Remaining','eshop'); ?></th>
		<th id="enddate"><?php _e('End Date','eshop'); ?></th>
		<th id="used"><?php _e('Used','eshop'); ?></th>
		<th id="active"><?php _e('Active?','eshop'); ?></th>
		<th id="delete"><?php _e('Delete','eshop'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$row=$wpdb->get_results("SELECT * FROM $disctable");
		$calt=0;
		foreach($row as $myrow){
			$calt++;
			$alt = ($calt % 2) ? '' : ' class="alternate"';
			$delete='';
			$remain=$myrow->remain;
			if($myrow->remain=='')
				$remain=__('Unlimited','eshop');
			if($myrow->live!='yes')
				$delete='<a href="'.$phpself.'&amp;delcode='.$myrow->id.'">'.__('Delete','eshop').' '.$myrow->disccode.'</a>';
			$eshopdate=$myrow->enddate;
			//add in check to see if used.
			switch($myrow->dtype){
				case '1':
					$type=__('%  - Limited by Uses','eshop');
					$eshopdate=__('Not applicable','eshop');
					break;
				case '2':
					$type=__('%  - Limited by Date','eshop');
					break;
				case '3':
					$type=__('%  - Limited by Uses and Date','eshop');
					break;
				case '4':
					$type=__('Free Ship - Limited by Uses','eshop');
					$eshopdate=__('Not applicable','eshop');
					break;
				case '5':
					$type=__('Free Ship - Limited by Date','eshop');
					break;
				case '6':
					$type=__('Free Ship - Limited by Uses and Date','eshop');
					break;
			}
	
			echo '<tr'.$alt.'>
			<td headers="code" id="numb'.$calt.'"><a href="'.$phpself.'&amp;editcode='.$myrow->id.'" title="'.__('Edit this discount','eshop').'">'.$myrow->disccode.'</a></td>
			<td headers="disc numb'.$calt.'">'.number_format_i18n($myrow->percent,2).'%</td>
			<td headers="type numb'.$calt.'">'.$type.'</td>
			<td headers="remain numb'.$calt.'">'.$remain.'</td>
			<td headers="enddate numb'.$calt.'">'.$eshopdate.'</td>
			<td headers="used numb'.$calt.'">'.$myrow->used.'</td>
			<td headers="active numb'.$calt.'">'.$myrow->live.'</td>
			<td headers="delete numb'.$calt.'">'.$delete.'</td>'

			."</tr>\n";
		}
		echo "</tbody></table></div>\n";
	}
	?>
	</div>
	<?php
}
?>