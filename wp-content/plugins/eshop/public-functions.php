<?php
if ('public-functions.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');

if (!function_exists('eshop_pre_wp_head')) {
    function eshop_pre_wp_head() {
    	global $wp_query,$blog_id;
		if(isset($wp_query->query_vars['eshopaction'])) {
   	 		$eshopaction = urldecode($wp_query->query_vars['eshopaction']);
		   	if($eshopaction=='success'){
		   		//destroy cart
				$_SESSION = array();
				//session_destroy();
			}
			//we need to buffer output on a few pages
			if($eshopaction=='redirect'){
				global $eshopoptions;
				ob_start();
				if(isset($eshopoptions['zero']) && $eshopoptions['zero']=='1'){
					if($_POST['amount']=='0' && $_SESSION['final_price'.$blog_id]== '0')
						$_POST['eshop_payment']=$_SESSION['eshop_payment'.$blog_id]='cash';
				}
			}
			if($eshopaction=='webtopayipn'){
				include_once 'webtopay.php';
				exit;
			}
			if($eshopaction=='paypalipn'){
				include_once 'paypal.php';
				exit;
			}
			if($eshopaction=='paysonipn'){
				include_once 'payson.php';
				//exit;
			}
			if($eshopaction=='authorizenetipn'){
				include_once 'authorizenet.php';
				//exit;
			}
			if($eshopaction=='idealliteipn'){
				include_once 'ideallite.php';
				//exit;
			}
			if($eshopaction=='ogoneipn'){
				include_once 'ogone.php';
				//exit;
			}
			do_action('eshop_include_mg_ipn',$eshopaction);
		}
		if(isset($_POST['eshopident_1'])){
			ob_start();
		}
		
    }
}
if (!function_exists('eshop_wp_head_add')) {
    /**
     * javascript functions
     */
    function eshop_wp_head_add() {
    	global $wp_query,$eshopoptions,$wpdb;
    	$eshopurl=eshop_files_directory();
		if(isset($wp_query->query_vars['eshopaction'])) {
   	 		$eshopaction = urldecode($wp_query->query_vars['eshopaction']);
		   	if($eshopaction=='redirect'){
				//this automatically submits the redirect form
				if($eshopoptions['status']=='live'){
					wp_register_script('eShopSubmit', $eshopurl['1'].'eshop-onload.js', array('jquery'));
					wp_enqueue_script('eShopSubmit');
				}
			}
		}
    }
}


if (!function_exists('add_eshop_query_vars')) {
	function add_eshop_query_vars($aVars) {
		$aVars[] = "eshopaction";    // represents the name of the product category as shown in the URL
		$aVars[] = "eshopaz";
		$aVars[] = "eshopall";
		$aVars[] = "_p";
		return $aVars;
	}
}

if (!function_exists('eshop_stylesheet')) {
	function eshop_stylesheet() {
		global $eshopoptions;
		$eshopurl=eshop_files_directory();
		if(@file_exists(STYLESHEETPATH.'/eshop.css')) {
			$myStyleUrl = get_stylesheet_directory_uri().'/eshop.css';
			$myStyleFile=STYLESHEETPATH.'/eshop.css';
		}elseif($eshopoptions['style']=='yes'){
			$myStyleUrl = $eshopurl['1'] . 'eshop.css';
			$myStyleFile=$eshopurl['0'] . 'eshop.css';
		}
		if ( file_exists($myStyleFile) ) {
			wp_register_style('myStyleSheets', $myStyleUrl);
			wp_enqueue_style( 'myStyleSheets');
		}
	}
}

if (!function_exists('eshop_unversion')) {
	function eshop_unversion($src) {
		if( strpos($src,'eshop.css'))
			$src=remove_query_arg('ver', $src);
		return $src;
	}
}
function eshop_bits_and_bobs(){
	global $eshopoptions;
	/**
	* eshop download products - need to process afore page is rendered
	* so this has to be called like this - unless anyone can come up with a better idea!
	*/
	if (isset($_POST['eshoplongdownloadname'])){
	//long silly name to ensure it isn't used elsewhere!
		eshop_download_the_product($_POST); 
	}
	
	//add images to the search page if set
	if('no' != $eshopoptions['search_img']){
		add_filter('the_excerpt','eshop_excerpt_img');
		add_filter('the_content','eshop_excerpt_img');
	}
	if($eshopoptions['fold_menu'] == 'yes'){
		add_filter('wp_list_pages_excludes', 'eshop_fold_menus');
	}
}

/* ajax */
if (!function_exists('eshop_ajax_inc')) {
	function eshop_ajax_inc(){
		wp_enqueue_script('jquery');
	}
}

if (!function_exists('eshop_action_javascript')) {
	function eshop_action_javascript() {
	$eshopajaxcart['addfadein']=100;
	$eshopajaxcart['addfadeout']=3000;
	$eshopajaxcart['cartcleardelay']=1000;
	$eshopajaxcart['cartdelay']=750;
	$eshopajaxcart['cartupdate']=3000;
	$eshopajaxcart['cartfadeout']=50;
	$eshopajaxcart['cartfadein']=700;
	//expects an array
	$eshopajaxcart=apply_filters('eshop_ajax_cart',$eshopajaxcart);
	?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($){
	$('.addtocart').submit(function(){
		var Id =$(this).attr("id");
		var data = {action: 'eshop_special_action',post:$('#'+Id).serialize() };  
		$.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
			function(response){
			$('#'+Id +" .eshopajax").insertAfter(this).fadeIn(<?php echo $eshopajaxcart['addfadein']; ?>).html(response).fadeOut(<?php echo $eshopajaxcart['addfadeout']; ?>);
			setTimeout (cleareshopCart,<?php echo $eshopajaxcart['cartcleardelay']; ?>); 
			setTimeout (doeshopRequest,<?php echo $eshopajaxcart['cartdelay']; ?>);  
			setTimeout (cleareshopRequest,<?php echo $eshopajaxcart['cartupdate']; ?>);  
		});
		function doeshopRequest(){
			var tdata = {action: 'eshop_cart'};
			$.post("<?php echo admin_url('admin-ajax.php'); ?>", tdata,
			function(response){
				$(".ajaxcart").insertAfter(this).fadeOut(<?php echo $eshopajaxcart['cartfadeout']; ?>).html(response).fadeIn(<?php echo $eshopajaxcart['cartfadein']; ?>);
			});
		}
		function cleareshopRequest(){
			$(".eshopajax").empty();
		}
		function cleareshopCart(){
			$(".ajaxcart").insertAfter();
		}
		return false;
	});

});
//]]>
</script>
	<?php
	}
}

if (!function_exists('eshop_cart_callback')) {
	function eshop_cart_callback($array) {
		global $eshopoptions, $blog_id;
		if(isset($_SESSION['eshopcart'.$blog_id]))
			echo display_cart($_SESSION['eshopcart'.$blog_id],false, $eshopoptions['checkout'],'widget');
		die();

	}
}

if (!function_exists('eshop_special_action_callback')) {
	function eshop_special_action_callback($array) {
		global $_POST, $blog_id; 
		// extract the data
		$jdata=$_POST['post'];
		$q = explode("&",$jdata);
		foreach ($q as $qi){
			if ($qi != ""){
				$qa = explode("=",$qi);
				list ($key, $val) = $qa;
				if(substr(urldecode($key),0,6)=='optset' && $val){
					$arr2[urldecode($key)] = urldecode($val);
				}elseif ($val){
					$data[urldecode($key)] = urldecode($val);
				}
			}
		} 
		if(isset($arr2)){
			foreach ($arr2 as $arr => $v){
				$off=substr($arr,6);
				$off=$off.'[val]['.$v.']';
				$on[]=explode('][',trim($off,'[]'));

			}
			foreach($on as $c){
				//change string array into proper array
				//0 = arraynum
				//1=arraykey
				//3=value
				$data['optset'][$c[0]][$c[1]]=$c[3];
			}
		}
		//quick qunatity check
		if(!isset($data['qty']) || isset($data['qty']) && !ctype_digit($data['qty'])){
			$msg=apply_filters('eshopCartQtyError','<p><strong class="eshoperror error">'.__('Warning: you must supply a valid quantity.','eshop').'</strong></p>');
		}
		if(!isset($msg)){
			eshop_cart_process($data);
			if(isset($_SESSION['eshopcart'.$blog_id]['error'])){
				$msg=apply_filters('eshopCartError',$_SESSION['eshopcart'.$blog_id]['error']);
				unset($_SESSION['eshopcart'.$blog_id]['error']);
			}elseif(isset($_SESSION['eshopcart'.$blog_id]['enote'])){
				$msg=apply_filters('eshopCartNote',$_SESSION['eshopcart'.$blog_id]['enote']);
				unset($_SESSION['eshopcart'.$blog_id]['enote']);
			}else{
				$msg=apply_filters('eshopCartSuccess',__('<p>Added</p>','eshop'));
			}
		}
		echo $msg;
		die();
	}
}
//randomise
function eshop_random() {
 	global $wpdb;
	$random_id=$wpdb->get_var("SELECT $wpdb->postmeta.post_id from $wpdb->postmeta,$wpdb->posts WHERE $wpdb->postmeta.meta_key='_eshop_stock' AND $wpdb->postmeta.meta_value='1' AND $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' order by rand() limit 1");
  	wp_redirect( get_permalink( $random_id ) );
 	exit;
}

class eshop_search {
	function eshop_search() {
		add_action('posts_where_request', array(&$this, 'search'));
	}
	function search($where)	{
		if ( isset( $_GET['eshopsearch'] ) && is_search()) {
			global $wpdb, $wp;
			$meta='_eshop_product';
			if($_GET['eshopsearch'] == 'instock')
				$meta='_eshop_stock';
			$where .= " AND $wpdb->postmeta.meta_key = '{$meta}'";
			add_filter('posts_join_request', array(&$this, 'search_join'));
		}
		return $where;
	}
	function search_join($join)	{
		global $wpdb;
		return $join .= " LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
	}
}

function eshopgbase(){
	if(isset($_GET['eshopbasefeed'])){
		include WP_PLUGIN_DIR.'/eshop/eshop-base-feed.php';
	}
}
function eshop_ie_fix(){
?>
<!--[if lt IE 8]>
<style type="text/css">
.eshoppanels li{
    display:inline;
    margin:10px 5px 0 5px;
}
</style>
<![endif]-->
<?php
}

?>