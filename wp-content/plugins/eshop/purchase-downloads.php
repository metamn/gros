<?php
if (!function_exists('eshop_downloads')) {
	function eshop_downloads($_POST, $images, $content){
		global $wpdb,$eshopoptions;
		//cache
		eshop_cache();
		$table = $wpdb->prefix ."eshop_downloads";
		$ordertable = $wpdb->prefix ."eshop_download_orders";
		$dir_upload = eshop_download_directory();
		$echo='';
	//download is handled via cart functions as it needs to
	//be accessible before anything is printed on the page

		if (isset($_POST['code']) && isset($_POST['email'])){
		/*
		Need to add in check about number of downloads here, including unlimited!
		*/
			$code=$wpdb->escape($_POST['code']);
			$email=$wpdb->escape($_POST['email']);
			$dlcount = $wpdb->get_var("SELECT COUNT(id) FROM $ordertable where email='$email' && code='$code' && downloads!='0'");
			if($dlcount>0){
				$echo .= $content;
				$tsize=0;
				$x=0;
				if($dlcount>1 && $eshopoptions['downloads_hideall'] != 'yes'){
					$echo .= '<p class="jdl"><a href="#dlall">'.__('Download all files','eshop').'</a></p>';
				}
				$dlresult = $wpdb->get_results("Select * from $ordertable where email='$email' && code='$code' && downloads!='0'");
				foreach($dlresult as $dlrow){
					//download single items.
					$filepath=$dir_upload.$dlrow->files;
			   		$dlfilesize = eshop_filesize($dlrow->files);
			   		$tsize=$tsize+$dlfilesize;
			   		if($dlrow->downloads==1){
			   			$dlword=__('download','eshop');
			   		}else{
			   			$dlword=__('downloads','eshop');
			   		}
			   		$imagetoadd='';
			   		if($images=='add'){
						$checkit=wp_check_filetype($filepath);
						$eshopext=wp_ext2type($checkit['ext']);
						$eshopfiletypeimgurl=wp_mime_type_icon($eshopext);
						$eshophead = wp_remote_head( $eshopfiletypeimgurl );
						$eshophresult = wp_remote_retrieve_response_code( $eshophead );
						if($eshophresult=='200' || $eshophresult=='302')
							$dims=getimagesize( $eshopfiletypeimgurl );
						if(is_array($dims))
							$dimensions=$dims[3];
						else
							$dimensions='';
						$imagetoadd=apply_filters('eshop_download_imgs','<img class="eshop-download-icon" src="'.$eshopfiletypeimgurl.'" '.$dimensions.' alt="" />',$checkit['ext']);
			   		}
			   		$dltitle = (strlen($dlrow->title) >= 20) ? substr($dlrow->title,0,20) . "&#8230;" : $dlrow->title;
					$echo.='
					<form method="post" action="" class="eshop dlproduct"><fieldset>
					<legend>'.$dltitle.' ('.check_filesize($dlfilesize).')</legend>
					'.$imagetoadd.'
					<input name="email" type="hidden" value="'.$_POST['email'].'" />
					<input name="code" type="hidden" value="'.$_POST['code'].'" />
					<input name="id" type="hidden" value="'.$dlrow->id.'" />
					<input name="eshoplongdownloadname" type="hidden" value="yes" />
					<label for="ro'.$x.'">'.__('Number of downloads remaining','eshop').'</label>
					<input type="text" readonly="readonly" name="ro" class="ro" id="ro'.$x.'" value="'.$dlrow->downloads.'" />
					<span class="buttonwrap"><input type="submit" class="button" id="submit'.$x.'" name="Submit" value="'.__('Download','eshop').' '.$dltitle.'" /></span>
					</fieldset></form>';
					$x++;
					$size=0;
				}
				if($dlcount>1 && $eshopoptions['downloads_hideall'] != 'yes'){
					//download all form.
					$echo.='
					<form method="post" action="" id="dlall" class="eshop"><fieldset>
					<legend>'.__('Download all files','eshop').' ('.check_filesize($tsize).') '.__('in one zip file.','eshop').'</legend>
					<input name="email" type="hidden" value="'.$_POST['email'].'" />
					<input name="code" type="hidden" value="'.$_POST['code'].'" />
					<input name="id" type="hidden" value="all" />
					<input name="eshoplongdownloadname" type="hidden" value="yes" />
					<p><span class="buttonwrap"><input class="button" type="submit" id="submit" name="Submit" value="'.__('Download All Files','eshop').'" /></span></p>
					</fieldset></form>
					';
				}
				//allow plugin to change output, validated email/passcode already
				$echo=apply_filters('eshop_download_page',$echo,$code,$email);
			}else{
				$prevdlcount = $wpdb->get_var("SELECT COUNT(id) FROM $ordertable where email='$email' && code='$code'");
				if($dlcount==$prevdlcount){
					$error='<p class="eshoperror error">'.__('Either your email address or code is incorrect, please try again.','eshop').'</p>';
				}else{
					$error='<p class="eshoperror error">'.__('Your email address and code are correct, however you have no downloads remaining.','eshop').'</p>';
				}
				$echo .= eshop_dloadform($email,$code,$error);
			}
		}else{
			$echo .= eshop_dloadform('','');
		}
		return $echo;
	}
}
//the standard log in form
function eshop_dloadform($email,$code,$error=''){
	$echo='';
	if($error!=''){
		$echo .= $error;
	}
	$echo .='
	<form method="post" action="" id="eshopdlform" class="eshop">
	<fieldset><legend>'.__('Enter Details','eshop').'</legend>
	<label for="email">'.__('Email:','eshop').'</label> 
	<input name="email" id="email" type="text" value="'.$email.'" /><br />
	<label for="code">'.__('Code:','eshop').'</label> 
	<input name="code" id="code" type="text" value="'.$code.'" /><br />
	<span class="buttonwrap"><input type="submit" id="submit" class="button" name="Submit" value="'.__('Submit','eshop').'" /></span>
	</fieldset>
	</form>
	';
	return $echo;

}
function check_filesize($size){
  if ($size == NULL){
     return "error";
  }
  $i=0;
  $iec = array("Bytes", "KB", "MB", "GB");
  while (($size/1024)>1) {
     $size=$size/1024;
     $i++;
  }
  $size=ceil($size);
  if($iec[$i]=='Bytes'){
  	return '&lt; 1Kb';
  }else{
  	return substr($size,0,strpos($size,'.')+3).$iec[$i];
  }
}
?>