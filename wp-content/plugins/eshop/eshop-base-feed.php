<?php
$eshopref=apply_filters('eshop_add_ref_feed','');
$data="<?xml version='1.0' encoding='utf-8' ?>\n";
$data.='<rss version="2.0" 
xmlns:g="http://base.google.com/ns/1.0" 
xmlns:c="http://base.google.com/cns/1.0">
<channel>
<copyright>'.get_bloginfo_rss('name').'</copyright>
<pubDate>'.date("r").'</pubDate>
<title>'.get_bloginfo_rss('name').'</title>
<link>'.get_bloginfo_rss('url').$eshopref.'</link>
<description>'.sprintf(__('Product feed for %1$s', 'eshop'),get_bloginfo_rss('name')).'</description>
<generator>'.__('eShop: Accessible e-commerce plugin for Wordpress','eshop').'</generator>'."\n";

global $wpdb,$eshopoptions;
$metatable=$wpdb->prefix.'postmeta';
$poststable=$wpdb->prefix.'posts';

$myrowres=$wpdb->get_results("
		SELECT DISTINCT meta.post_id
		FROM $metatable as meta, $poststable as posts
		WHERE meta.meta_key = '_eshop_product'
		AND posts.ID = meta.post_id
		AND posts.post_status = 'publish'
		ORDER BY meta.post_id");
$x=0;
foreach($myrowres as $row){
	$grabit[$x]=get_post_custom($row->post_id);
	$grabit[$x]['id']=array($row->post_id);
	$x++;
}
/*
* remove the bottom array to try and flatten
* could be rather slow, but easier than trying to create
* a different method, at least for now!
*/
foreach($grabit as $foo=>$k){
	foreach($k as $bar=>$v){
		if($bar=='_eshop_product'){
			$x=unserialize($v[0]);
			foreach($x as $nowt=>$val){
				$array[$foo][$nowt]=$val;
			}
		}
		foreach($v as $nowt=>$val){
			$array[$foo][$bar]=$val;
		}
	}
}

//set up defaults
$basecondition=$eshopoptions['base_condition'];
$basebrand=$eshopoptions['base_brand'];
$baseptype=$eshopoptions['base_ptype'];
$baseexpiry=$eshopoptions['base_expiry'];
if($baseexpiry=='')$baseexpiry=28;
$basedate=$basedatetoo=date('Y-m-d',mktime(0, 0, 0, date("m") , date("d")+$baseexpiry, date("Y")));
$basepayment=$eshopoptions['base_payment'];
$basepayments = explode(",", $basepayment);
foreach($array as $foo=>$grabit){
	//for the title
	$rid=$grabit['id'];
	$pdata=get_post($rid);
	$post=$pdata;
	setup_postdata($post);

	if(strlen($pdata->post_title) > 79){
		$basetitle=substr($pdata->post_title, 0, 76).'...';
	}else{
		$basetitle=$pdata->post_title;
	}
	//automatic data
	$baselink=get_permalink($rid).$eshopref;
	$baseid=$rid;
	$baseprice=$grabit['products']['1']['price'];
	if (has_post_thumbnail( $rid ) ) {
		$grabimg=get_post_thumbnail_id($rid);
		$src=wp_get_attachment_image_src($grabimg,'full');
		$baseimg =$src['0'];
	}	
	
	$bdoutput = get_the_excerpt();
	$basedescription = apply_filters('the_excerpt_rss', $bdoutput);
	
	//$basecondition=$basebrand=$baseptype=$basedate=$baseimg=$baseean=$baseisbn=$basempn=$baseqty='';
	//individual set product data
	$basetable=$wpdb->prefix ."eshop_base_products";
	$basedata=$wpdb->get_row("SELECT * FROM $basetable WHERE post_id = $rid");
	//if this exists overwrite defaults
	if(isset($basedata->post_id)){
		$basecondition=$basedata->thecondition;
		$basebrand=$basedata->brand;
		$baseptype=$basedata->ptype;
		$basedate=$basedata->expiry;
		$baseimg=$basedata->img;
		$baseean=$basedata->ean;
		$baseisbn=$basedata->isbn;
		$basempn=$basedata->mpn;
		$baseqty=$basedata->qty;
	}

	$data.='
 <item>
	<link>'.eshoprssfilter($baselink).'</link>
	<title>'.eshoprssfilter($basetitle).'</title>	
	<description>'.eshoprssfilter($basedescription).'</description>
	<g:id>'.eshoprssfilter($baseid).'</g:id>';
		if(isset($baseqty) && $baseqty!=''){
			$data.='
	<g:quantity>'.eshoprssfilter($baseqty).'</g:quantity>'."\n";
		}
	$data.='
	<g:price>'.eshoprssfilter($baseprice).'</g:price>
	<g:price_type>starting</g:price_type>'."\n";
	foreach($basepayments as $baseapayment){ 
		if($basepayment!=''){
			$data.='
	<g:payment_accepted>'.eshoprssfilter($baseapayment).'</g:payment_accepted>'."\n";
		}
	} 
	if(isset($basecondition) && $basecondition!=''){
		$data.='
	<g:condition>'.eshoprssfilter($basecondition).'</g:condition>'."\n";
	} 
	if(isset($baseean) && $baseean!=''){
		$data.='
	<g:ean>'.eshoprssfilter($baseean).'</g:ean>'."\n";
	} 
	if(isset($basedate) && $basedate!=''){
		$data.='
	<g:expiration_date>'.$basedate.'</g:expiration_date>'."\n";
	} 
	if(isset($basebrand) && $basebrand!=''){
		$data.='
	<g:brand>'.eshoprssfilter($basebrand).'</g:brand>'."\n";
	} 
	if(isset($baseimg) && $baseimg!=''){
		$data.='
	<g:image_link>'.eshoprssfilter($baseimg).'</g:image_link>'."\n";
	} 
	if(isset($baseisbn) && $baseisbn!=''){
		$data.='
	<g:isbn>'.eshoprssfilter($baseisbn).'</g:isbn>'."\n";
	} 
	if(isset($basempn) && $basempn!=''){
		$data.='
	<g:mpn>'.eshoprssfilter($basempn).'</g:mpn>'."\n";
	} 
	if(isset($baseptype) && $baseptype!=''){
		$data.='
	<g:product_type>'.eshoprssfilter($baseptype).'</g:product_type>'."\n";
	} 
	$data.='
 </item>'."\n";
}
$data.='</channel>
</rss>';

$downloadFilename='gbase2.xml';
if(isset($_GET['os']) && $_GET['os']=='mac'){
	$data=utf8_encode($data);
	$data=iconv('UTF-8', 'macintosh', $data);
}	
if(isset($_GET['d'])){
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$downloadFilename");
header("Pragma: no-cache");
header("Expires: 0");		
}else{
header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
}		
echo $data;
exit;
if (!function_exists('eshopcleanit')) {
	function eshopcleanit($data){
		$toreps='"';
		$repswith='""';
		$order   = array("\r\n", "\n", "\r");
		$replace = "\n";
		$data = str_replace($toreps, $repswith, $data);
		$data = wordwrap($data, 75, "\n", 1);
		$data = str_replace($order, $replace, $data);
		return $data;
	}
}
function eshoprssfilter($z){
	$zz=strip_tags($z);
	$zz=convert_chars($zz);
	return trim($zz);
}
?>