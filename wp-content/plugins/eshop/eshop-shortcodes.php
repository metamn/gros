<?php 
add_shortcode('eshop_list_featured', 'eshop_list_featured');
add_shortcode('eshop_sale', 'eshop_list_sale');
add_shortcode('eshop_best_sellers', 'eshop_best_sellers');
add_shortcode('eshop_list_new', 'eshop_list_new');
add_shortcode('eshop_list_subpages', 'eshop_list_subpages');
add_shortcode('eshop_list_cat_tags', 'eshop_list_cat_tags');
add_shortcode('eshop_random_products', 'eshop_list_random');
add_shortcode('eshop_show_cancel', 'eshop_show_cancel');
add_shortcode('eshop_show_cart', 'eshop_show_cart');
add_shortcode('eshop_show_checkout', 'eshop_show_checkout');
add_shortcode('eshop_show_discounts','eshop_show_discounts');
add_shortcode('eshop_show_downloads', 'eshop_show_downloads');
add_shortcode('eshop_show_payments','eshop_show_payments');
add_shortcode('eshop_show_product','eshop_show_product');
add_shortcode('eshop_show_shipping', 'eshop_show_shipping');
add_shortcode('eshop_show_success', 'eshop_show_success');
add_shortcode('eshop_empty_cart', 'eshop_empty_cart');
add_shortcode('eshop_list_alpha', 'eshop_list_alpha');
add_shortcode('eshop_cart_items','eshop_cart_items');
add_shortcode('eshop_addtocart','eshop_addtocart');
add_shortcode('eshop_welcome','eshop_welcome');
add_shortcode('eshop_details','eshop_details');
add_shortcode('eshop_tax_rates','eshop_tax_rates');

function eshop_cart_items($atts){
	global $blog_id,$eshopoptions;
	
	extract(shortcode_atts(array('before'=>'','after'=>'','hide'=>'no','showwhat'=>'both'), $atts));
	$eecho='';
	if($before!='')
		$eecho.=$before.' ';
	if(isset($_SESSION['eshopcart'.$blog_id]) || $hide=='no'){
		$eshopsize=0;
		$eshopqty=0;
		if(isset($_SESSION['eshopcart'.$blog_id])){
			$eshopsize=sizeof($_SESSION['eshopcart'.$blog_id]);
			foreach($_SESSION['eshopcart'.$blog_id] as $eshopdo=>$eshopwop){
				$eshopqty+=$eshopwop['qty'];
			}
		}
		
		if($showwhat=='items' || $showwhat=='both'){
			$eecho .=sprintf(_n('<span>%d</span> product in cart.','<span>%d</span> products in cart.',$eshopsize,'eshop'),$eshopsize);
		}
		if($showwhat=='qty' || $showwhat=='both'){
			if($showwhat=='both') $eecho.= '<br />';
			$eecho .=sprintf(_n('<span>%d</span> item in cart.','<span>%d</span> items in cart.',$eshopqty,'eshop'),$eshopqty);
		}
		$eecho.= '<br /><a href="'.get_permalink($eshopoptions['cart']).'">'.__('View Cart','eshop').'</a>';
		$eecho .='<br /><a href="'.get_permalink($eshopoptions['checkout']).'">'.__('Checkout','eshop').'</a>';
		
	}
	
	if($after!='')
		$eecho.=' '.$after;
	
	return $eecho;
}

function eshop_empty_cart($atts, $content = '') {
	global $blog_id;
	
	if(isset($_SESSION['eshopcart'.$blog_id])){
		$content='';
	}
	return $content;
}
function eshop_list_alpha($atts){
	global $wpdb, $post,$wp_rewrite,$wp_query;
	eshop_cache();
	extract(shortcode_atts(array('class'=>'eshopalpha','panels'=>'no','form'=>'no','records'=>'25','imgsize'=>'','links'=>'yes','price'=>'no','outofstock'=>'no'), $atts));
	//a-z listing
	$letter_array = range('A','Z');
	$fullarray=$letter_array;
	$fullarray[]='num';
	$used=array();
	$addwhere="$wpdb->postmeta.meta_key='_eshop_stock' AND";
	if($outofstock!='no')
		$addwhere="$wpdb->postmeta.meta_key='_eshop_product' AND";
	
	$usedaz=$wpdb->get_results("SELECT DISTINCT UPPER(LEFT(post_title,1)) as letters FROM $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' ORDER BY letters");
	$usednum=$wpdb->get_var("SELECT COUNT(DISTINCT UPPER(LEFT(post_title,1)) BETWEEN '0' AND '9') FROM $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish'") - 1;
		
	foreach($usedaz as $usethis){
		$used[]=$usethis->letters;
	}
	if(isset($wp_query->query_vars['eshopaz'])) {
   	 	$eshopaz = urldecode($wp_query->query_vars['eshopaz']);
   	}
	if(!isset($eshopaz) || !in_array($eshopaz,$fullarray)){
		$eshopaz='a';
		$dbletter='A';
	}
	$econtain='<ul class="eshop eshopaz">';
	$thisispage=get_permalink($post->ID);
	foreach ($letter_array as $letter) {
		if (in_array($letter, $used)){
			if(isset($eshopaz) && strtoupper($eshopaz)==$letter){
				$addclass=' class="current"';
				$dbletter=$letter;
			}else{
				$addclass='';
			}
			$thispage=add_query_arg('eshopaz',$letter,$thisispage);
			$econtain.= '<li'.$addclass.'><a href="'.$thispage.'">'.$letter . "</a></li>\n";
		}else{
			$econtain.= '<li><span>'.$letter."</span></li>\n";
		}
	}
	if(isset($eshopaz) && $eshopaz=='num' && $usednum>0 ){
		$thispage=add_query_arg('eshopaz','num',$thisispage);
		$econtain.= '<li class="current"><a href="'.$thispage.'">0-9</a></li>'."\n";
	}elseif ($usednum>0){
		$thispage=add_query_arg('eshopaz','num',$thisispage);
		$econtain.= '<li><a href="'.$thispage.'">0-9</a></li>'."\n";
	}else{
	 	$econtain.= '<li><span>0-9</span></li>'."\n";
	}
	$econtain.="</ul>\n";
	if(isset($dbletter) && in_array($dbletter,$letter_array))
		$qbuild=" AND UPPER(LEFT(post_title,1))='$dbletter'";
	elseif(isset($eshopaz) && $eshopaz=='num')
		$qbuild=" AND UPPER(LEFT(post_title,1)) BETWEEN '0' AND '9'";
	else
		$qbuild='';
	
	if($qbuild!=''){
		$max=$wpdb->get_var("SELECT count($wpdb->posts.ID) from $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' $qbuild");
		if($max>0){
			if(isset($wp_query->query_vars['_p']))$epage=$wp_query->query_vars['_p'];
			else $epage='1';
			if(!isset($wp_query->query_vars['eshopall'])){
				$page_links = paginate_links( array(
					'base' => add_query_arg( '_p', '%#%' ),
					'format' => '',
					'total' => ceil($max / $records),
					'current' => $epage,
					'type'=>'array'
					));
				$offset=($epage*$records)-$records;
			}else{
				$page_links = paginate_links( array(
					'base' => add_query_arg( '_p', '%#%' ),
					'format' => '',
					'total' => ceil($max / $records),
					'current' => $epage,
					'type'=>'array',
					'show_all' => true,
				));
				$offset='0';
				$records=$max;
			}
		}


		if(!isset($offset)) $offset='0';
		$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' $qbuild order by post_title ASC limit $offset,$records");
		if($pages) {
			//paginate
			$echo = '<div class="paginate eshop-paginate">';
			if($records!=$max){
				$eecho = $page_links;
			}
			$echo .= sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
				number_format_i18n( ( $epage - 1 ) * $records + 1 ),
				number_format_i18n( min( $epage * $records, $max ) ),
				number_format_i18n( $max)
			);

			$echo .= '</div>';
			//end
			if($panels=='no'){
				$echo .= eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
			}else{
				if($class=='eshopalpha') $class='eshoppanels';
				$echo .= eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
			}

			if(isset($eecho)){
				$thispage=add_query_arg('eshopall','yes',$thisispage);
				$eeecho="<ul class='page-numbers'>\n\t<li>".join("</li>\n\t<li>", $eecho)."</li>\n<li>".'<a href="'.$thispage.'">'.__('View All','eshop').'</a>'."</li>\n</ul>\n";
				$echo .= '<div class="paginate pagfoot eshop-paginate">'.$eeecho.'</div>';
			}else{
				$echo .= '<br class="pagfoot" />';
			}
			return $econtain.$echo;
		} 
	}

	return $econtain .'<p>'. __('No products found for that letter or number.','eshop').'</p>';
} 
function eshop_list_subpages($atts){
	global $wpdb, $post,$wp_query;
	eshop_cache();
	extract(shortcode_atts(array('class'=>'eshopsubpages','panels'=>'no','form'=>'no','show'=>'100','records'=>'10','sortby'=>'post_title','order'=>'ASC','imgsize'=>'','id'=>'','links'=>'yes','price'=>'no','outofstock'=>'yes','depts'=>'yes'), $atts));
	$echo='';
	if($id!='')
		$eshopid=$id;
	else
		$eshopid=$post->ID;
		
	$allowedsort=array('post_date','post_title','menu_order');
	$allowedorder=array('ASC','DESC');
	if(!in_array($sortby,$allowedsort)) 
		$sortby='post_title';
	switch($sortby){
		case ('post_date'):
			$orderby='date';
			break;
		case ('menu_order'):
			$orderby='menu_order';
			break;
		case ('post_title'):
		default:
			$orderby='title';
			break;
	}
	if(!in_array($order,$allowedorder)) 
		$order='ASC';
	
	$addwhere="_eshop_stock";
	if($outofstock=='yes')
		$addwhere="_eshop_product";
	
	$addingthis='';
	if($depts!='yes')
		$addingthis = "$wpdb->postmeta.meta_key='$addwhere' AND";

	$thisispage=get_permalink($post->ID);
	$pagetype=apply_filters('eshop_sub_page_type','page');
	$max = $wpdb->get_var("SELECT count(DISTINCT($wpdb->posts.ID)) from $wpdb->posts,$wpdb->postmeta WHERE $addingthis $wpdb->posts.ID=$wpdb->postmeta.post_id AND post_type='$pagetype' AND post_parent='$eshopid' AND post_status='publish'");

	if($records>$show) $records=$show;
	if($max>$show)
		$max=$show;
	if($max>0){
		if(isset($wp_query->query_vars['_p']))$epage=$wp_query->query_vars['_p'];
		else $epage='1';
		if(!isset($wp_query->query_vars['eshopall'])){
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array'
				));
			$offset=($epage*$records)-$records;
		}else{
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array',
				'show_all' => true,
			));
			$offset='0';
			$records=$max;
		}
	}
	if(!isset($offset)) $offset='0';
	$args = array(
	'post_type' => $pagetype,
	'post_status' => null,
	'post_parent' => $eshopid, // any parent
	'orderby'=> $orderby,
	'order'=> $order,
	'numberposts' => $records, 
	'offset' => $offset,
	); 
	if($addingthis!='')
		$args['meta_key']= $addwhere;

	$pages = get_posts($args);
	wp_reset_query();

	if($pages) {
		//paginate
		$echo .= '<div class="paginate eshop-paginate">';
		if($records!=$max){
			$eecho = $page_links;
		}
		$echo .= sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
			number_format_i18n( ( $epage - 1 ) * $records + 1 ),
			number_format_i18n( min( $epage * $records, $max ) ),
			number_format_i18n( $max)
		);
		$echo .= '</div>';
		//end
		if($panels=='no'){
			$echo .= eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
		}else{
			if($class=='eshopsubpages') $class='eshoppanels';
			$echo .= eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
		}
		
		if(isset($eecho)){
			$thispage=add_query_arg('eshopall','yes',$thisispage);
			$eeecho="<ul class='page-numbers'>\n\t<li>".join("</li>\n\t<li>", $eecho)."</li>\n<li>".'<a href="'.$thispage.'">'.__('View All','eshop').'</a>'."</li>\n</ul>\n";
			$echo .= '<div class="paginate pagfoot eshop-paginate">'.$eeecho.'</div>';
		}else{
			$echo .= '<br class="pagfoot" />';
		}
		return $echo;
	} 
	return;
}
function eshop_list_cat_tags($atts){
	global $wpdb, $post,$wp_query;
	eshop_cache();
	extract(shortcode_atts(array('class'=>'eshopcats','panels'=>'no','form'=>'no','show'=>'100','records'=>'10','sortby'=>'post_title','order'=>'ASC','imgsize'=>'','find'=>'','type'=>'tag','links'=>'yes','price'=>'no','outofstock'=>'yes'), $atts));
	$echo='';
	$allowedtype=array('cat','category_name','tag','tag_id');
	$allowedtype=apply_filters('eshop_list_cat_tags_types',$allowedtype);
	if(!in_array($type,$allowedtype))  
		$type='tag';
	
	$allowedsort=array('post_date','post_title','menu_order');
	$allowedsort=apply_filters('eshop_list_cat_tags_orderby',$allowedsort);
	if(!in_array($sortby,$allowedsort)) 
		$sortby='post_title';
	$orderby=$sortby;

	$allowedorder=array('ASC','DESC');
	$allowedorder=apply_filters('eshop_list_cat_tags_order',$allowedorder);
	if(!in_array($order,$allowedorder)) 
		$order='ASC';
	
	
	//defaults only.
	switch($sortby){
		case ('post_date'):
			$orderby='date';
			break;
		case ('menu_order'):
			$orderby='menu_order';
			break;
		case ('post_title'):
			$orderby='title';
			break;
	}
	$addwhere="_eshop_stock";
	if($outofstock=='yes')
		$addwhere="_eshop_product";
		
		
	$thisispage=get_permalink($post->ID);
	$array=array('post','page');
	$array=apply_filters('eshop_post_types',$array);
	$args = array(
	'post_type' => $array,
	'post_status' => 'publish',
	$type => $find, 
	'meta_key'=>$addwhere,
	'posts_per_page'=>-1
	); 
	if($records>$show) $records=$show;
	$max = sizeof(query_posts($args));
	if($max>$show)
		$max=$show;
	if($max>0){
		if(isset($wp_query->query_vars['_p']))$epage=$wp_query->query_vars['_p'];
		else $epage='1';
		if(!isset($wp_query->query_vars['eshopall'])){
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array'
				));
			$offset=($epage*$records)-$records;
		}else{
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array',
				'show_all' => true,
			));
			$offset='0';
			$records=$max;
		}
	}
	if(!isset($offset)) $offset='0';
	$args = array(
	'post_type' => $array,
	'post_status' => 'publish',
	$type => $find, 
	'meta_key'=> $addwhere,
	'orderby'=> $orderby,
	'order'=> $order,
	'numberposts' => $records, 
	'offset' => $offset,
	); 
	$pages = get_posts($args);
	wp_reset_query();
	if($pages) {
		//paginate
		$echo .= '<div class="paginate eshop-paginate">';
		if($records!=$max){
			$eecho = $page_links;
		}
		$echo .= sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
			number_format_i18n( ( $epage - 1 ) * $records + 1 ),
			number_format_i18n( min( $epage * $records, $max ) ),
			number_format_i18n( $max)
		);
		$echo .= '</div>';
		//end
		if($panels=='no'){
			$echo .= eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
		}else{
			if($class=='eshopcats') $class='eshoppanels';
			$echo .= eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
		}
		
		if(isset($eecho)){
			$thispage=add_query_arg('eshopall','yes',$thisispage);
			$eeecho="<ul class='page-numbers'>\n\t<li>".join("</li>\n\t<li>", $eecho)."</li>\n<li>".'<a href="'.$thispage.'">'.__('View All','eshop').'</a>'."</li>\n</ul>\n";
			$echo .= '<div class="paginate pagfoot eshop-paginate">'.$eeecho.'</div>';
		}else{
			$echo .= '<br class="pagfoot" />';
		}
		return $echo;
	} 
	return;
}
function eshop_list_new($atts){
	global $wpdb, $post,$wp_query;
	eshop_cache();
	extract(shortcode_atts(array('class'=>'eshopsubpages','panels'=>'no','form'=>'no','show'=>'100','records'=>'10','imgsize'=>'','links'=>'yes','price'=>'no','outofstock'=>'no'), $atts));
	$echo='';
	
	$addwhere="$wpdb->postmeta.meta_key='_eshop_stock' AND";
	if($outofstock!='no')
		$addwhere="$wpdb->postmeta.meta_key='_eshop_product' AND";
	
	$max=$wpdb->get_var("SELECT count($wpdb->posts.ID) from $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish'");
	if($records>$show) $records=$show;

	if($max>$show)
		$max=$show;

	if($max>0){
		if(isset($wp_query->query_vars['_p']))$epage=$wp_query->query_vars['_p'];
		else $epage='1';
		if(!isset($wp_query->query_vars['eshopall'])){
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array'
				));
			$offset=($epage*$records)-$records;
			
		}else{
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array',
				'show_all' => true,
			));
			$offset='0';
			$records=$max;
		}
	}
	if(!isset($offset)) $offset='0';
	$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' order by post_date DESC limit $offset,$records");

	$thisispage=get_permalink($post->ID);
	if($pages) {
		//paginate
		$echo = '<div class="paginate eshop-paginate">';
		if($records!=$max){
			$eecho = $page_links;
		}
		$echo .= sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
			number_format_i18n( ( $epage - 1 ) * $records + 1 ),
			number_format_i18n( min( $epage * $records, $max ) ),
			number_format_i18n( $max)
		);
		$echo .= '</div>';
		//end

		if($panels=='no'){
			$echo .= eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
		}else{
			if($class=='eshopsubpages') $class='eshoppanels';
			$echo .= eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
		}
		if(isset($eecho)){
			$thispage=add_query_arg('eshopall','yes',$thisispage);
			$eeecho="<ul class='page-numbers'>\n\t<li>".join("</li>\n\t<li>", $eecho)."</li>\n<li>".'<a href="'.$thispage.'">'.__('View All','eshop').'</a>'."</li>\n</ul>\n";
			$echo .= '<div class="paginate pagfoot eshop-paginate">'.$eeecho.'</div>';
		}else{
			$echo .= '<br class="pagfoot" />';
		}

		return $echo;
	} 
	return;
} 
function eshop_best_sellers($atts){
	global $wpdb, $post,$wp_query;
	eshop_cache();
	extract(shortcode_atts(array('class'=>'eshopbestsellers','panels'=>'no','form'=>'no','show'=>'100','records'=>'10','imgsize'=>'','links'=>'yes','price'=>'no','outofstock'=>'yes'), $atts));
	$echo='';
	$stktable=$wpdb->prefix.'eshop_stock';
	
	$addwhere="$wpdb->postmeta.meta_key='_eshop_stock' AND";
	if($outofstock=='yes')
		$addwhere="$wpdb->postmeta.meta_key='_eshop_product' AND";
		
	$max=$wpdb->get_var("SELECT COUNT($wpdb->postmeta.post_id)
		from $wpdb->postmeta,$wpdb->posts, $stktable as stk
		WHERE $addwhere
		$wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' AND stk.post_id=$wpdb->posts.ID");
	if($records>$show) $records=$show;

	if($max>$show)
		$max=$show;
	if($max>0){
		if(isset($wp_query->query_vars['_p']))$epage=$wp_query->query_vars['_p'];
		else $epage='1';
		if(!isset($wp_query->query_vars['eshopall'])){
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array'
				));
			$offset=($epage*$records)-$records;
		}else{
			$page_links = paginate_links( array(
				'base' => add_query_arg( '_p', '%#%' ),
				'format' => '',
				'total' => ceil($max / $records),
				'current' => $epage,
				'type'=>'array',
				'show_all' => true,
			));
			$offset='0';
			$records=$max;
		}
	}
	if(!isset($offset)) $offset='0';
	$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.*
	from $wpdb->postmeta,$wpdb->posts, $stktable as stk
	WHERE $addwhere
	$wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' AND stk.post_id=$wpdb->posts.ID
	order by stk.purchases DESC limit $offset,$records");
	$thisispage=get_permalink($post->ID);
	if($pages) {
		//paginate
		$echo = '<div class="paginate eshop-paginate">';
		if($records!=$max){
			$eecho = $page_links;
		}
		$echo .= sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>',
			number_format_i18n( ( $epage - 1 ) * $records + 1 ),
			number_format_i18n( min( $epage * $records, $max ) ),
			number_format_i18n( $max)
		);
		$echo .= '</div>';
		//end
		if($panels=='no'){
			$echo .= eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
		}else{
			if($class=='eshopbestsellers') $class='eshoppanels';
			$echo .= eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
		}

		if(isset($eecho)){
			$thispage=add_query_arg('eshopall','yes',$thisispage);
			$eeecho="<ul class='page-numbers'>\n\t<li>".join("</li>\n\t<li>", $eecho)."</li>\n<li>".'<a href="'.$thispage.'">'.__('View All','eshop').'</a>'."</li>\n</ul>\n";
			$echo .= '<div class="paginate pagfoot eshop-paginate">'.$eeecho.'</div>';
		}else{
			$echo .= '<br class="pagfoot" />';
		}
		return $echo;
	} 
	return;
} 
function eshop_list_featured($atts){
	return eshop_list_featured_sale($atts);
}
function eshop_list_sale($atts){
	return eshop_list_featured_sale($atts, 'sale');
}

function eshop_list_featured_sale($atts, $type='featured'){
	global $wpdb, $post;
	eshop_cache();
	$paged=$post;
	extract(shortcode_atts(array('class'=>'eshop'.$type,'panels'=>'no','form'=>'no','sortby'=>'post_title','order'=>'ASC','imgsize'=>'','links'=>'yes','price'=>'no'), $atts));
	$allowedsort=array('post_date','post_title','menu_order','random');
	$allowedorder=array('ASC','DESC');
	if(!in_array($sortby,$allowedsort)) 
		$sortby='post_title';
	if(!in_array($order,$allowedorder)) 
		$order='ASC';
	if($sortby=='random')
		$sortby='rand()';
	
	$pages=$wpdb->get_results("SELECT p.* from $wpdb->postmeta as pm,$wpdb->posts as p WHERE pm.meta_key='_eshop_".$type."' AND p.post_status='publish' AND p.ID=pm.post_id ORDER BY $sortby $order");

	if($pages) {
		if($panels=='no'){
			$echo = eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
		}else{
			if($class=='eshop'.$type) $class='eshoppanels';
			$echo = eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
		}
		$echo .= '<br class="pagfoot" />';
		$post=$paged;
		return $echo;
	} 
	$post=$paged;
	return;
}
function eshop_list_random($atts){
	global $wpdb, $post;
	//cache
	eshop_cache();
	$paged=$post;
	extract(shortcode_atts(array('list' => 'yes','class'=>'eshoprandomlist','panels'=>'no','form'=>'no','show'=>'6','records'=>'6','imgsize'=>'','excludes'=>'0','links'=>'yes','price'=>'no','outofstock'=>'yes'), $atts));
	if($list!='yes' && $class='eshoprandomlist'){
		$class='eshoprandomproduct';
	}
	if($list=='yes'){
		$elimit=$show;
	}else{
		$elimit=1;
	}
	$subquery='';
	if($excludes!=0){
		$exclude= explode(",", $excludes);
		foreach($exclude as $exid){
			if(is_numeric($exid) && $exid>0){
				$subq[]= "$wpdb->posts.ID!=$exid";
			}
		}
		$subquery= ' AND '.implode(' AND ',$subq);
	}
	
	$addwhere="$wpdb->postmeta.meta_key='_eshop_stock' AND";
	if($outofstock=='yes')
		$addwhere="$wpdb->postmeta.meta_key='_eshop_product' AND";
	
	$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $addwhere $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish'$subquery order by rand() limit $elimit");

	if($pages) {
		if($panels=='no'){
			$echo = eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
		}else{
			if($class=='eshoprandomlist') $class='eshoppanels';
				$echo = eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
		}
		$post=$paged;
		return $echo;
	}
	$post=$paged;
	return;
}
function eshop_show_product($atts){
	global $wpdb, $post;
	eshop_cache();
	$paged=$post;

	extract(shortcode_atts(array('id'=>'0','class'=>'eshopshowproduct','panels'=>'no','form'=>'no','imgsize'=>'','links'=>'yes','price'=>'no'), $atts));
	if($id!=0){
		$pages=array();
		$theids = explode(",", $id);
		foreach($theids as $thisid){
			$thispage=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $wpdb->postmeta.meta_key='_eshop_product' AND $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' AND $wpdb->posts.ID='$thisid'");
			if(sizeof($thispage)>0)//only add if it exists
				array_push($pages,$thispage['0']);
		}
		if(sizeof($pages)>0){//if nothing found - don't do this
			if($panels=='no'){
				$echo = eshop_listpages($pages,$class,$form,$imgsize,$links,$price);
			}else{
				if($class=='eshopshowproduct') $class='eshoppanels';
				$echo = eshop_listpanels($pages,$class,$form,$imgsize,$links,$price);
			}
			$post=$paged;
			return $echo;
		}
		$post=$paged;
	}

	return;
}
function eshop_listpages($subpages,$eshopclass,$form,$imgsize,$links,$price){
	global $wpdb,$eshopoptions,$post;
	$eshopprodimg='_eshop_prod_img';
	$echo ='<ul class="eshop '.$eshopclass.'">';
	$tempost=$post;
	foreach ($subpages as $post){	
		setup_postdata($post);
		$xclass='<li class="eshop-product-'.$post->ID.'">';
		if(isset($eshopoptions['sale']) && $eshopoptions['sale']=='yes'){
			$esale=get_post_meta( $post->ID, '_eshop_sale',true );
			if($esale=='yes')
				$xclass='<li class="sale eshop-product-'.$post->ID.'">';
		}
		$echo .= $xclass;
		if($links=='yes')
			$echo .= '<a class="itemref" href="'.get_permalink($post->ID).'">'.apply_filters("the_title",$post->post_title, $post->ID).'</a>';
		else
			$echo .= apply_filters("the_title",$post->post_title, $post->ID);
			
		if(isset($esale) && $esale=='yes')
			$echo .= '<strong class="onsale"><span>'.__('On Sale','eshop').'</span></strong>';
			
		$w=apply_filters('eshop_thumbnail_size_w',get_option('thumbnail_size_w'));
		$h=apply_filters('eshop_thumbnail_size_h',get_option('thumbnail_size_h'));
		if($imgsize!=''){
			$w=round(($w*$imgsize)/100);
			$h=round(($h*$imgsize)/100);
		}

		if (has_post_thumbnail( $post->ID ) ) {
			if($links=='yes')
				$echo .='<a class="itemref" href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail( $post->ID, array($w, $h)).'</a>'."\n";
			else
				$echo .=get_the_post_thumbnail( $post->ID, array($w, $h))."\n";

		}else{
			$eimage=eshop_files_directory();
			$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');

			if($links=='yes')
				$echo .='<a class="itemref" href="'.get_permalink($post->ID).'"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a>'."\n";
			else
				$echo .='<img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" />'."\n";

		}
		//this line stops the addtocart form appearing.
		remove_filter('the_content', 'eshop_boing');
		$echo .= apply_filters('eshop_list_excerpt',apply_filters('the_excerpt', get_the_excerpt()), $post->ID);

		if($price!='no'){
			$currsymbol=$eshopoptions['currency_symbol'];
			$eshop_product=maybe_unserialize(get_post_meta( $post->ID, '_eshop_product',true ));
		}
		if($price=='yes'){
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['price'])){
				$echo.='<span class="ep_price">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['price'],__('2','eshop'))).'</span>';
			}
		}
		if($price=='sale'){
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['saleprice']) && $eshop_product['products']['1']['saleprice']!='' && isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
				$echo.='<span class="ep_price saleprice">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['saleprice'],__('2','eshop'))).'</span>';
			}
		}
		if($price=='both'){
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['price'])){
				$echo.='<span class="ep_price onsale">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['price'],__('2','eshop'))).'</span>';
			}
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['saleprice']) && $eshop_product['products']['1']['saleprice']!='' && isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
				$echo.='<span class="ep_price saleprice">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['saleprice'],__('2','eshop'))).'</span>';
			}
		}
		if($form=='yes' || $form=='yesqty'){
			if($form=='yesqty') $short='no';
			else $short=$form;
			$echo =eshop_boing($echo,$short,$post->ID,'y');
		}
		$echo .= '</li>'."\n";
		//and then we re-add it
		add_filter('the_content', 'eshop_boing');
	}
	$echo .= '</ul>';
	$post=$tempost;
	return $echo;
}

function eshop_listpanels($subpages,$eshopclass,$form,$imgsize,$links,$price){
	global $wpdb, $post, $eshopoptions;
	$paged=$post;
	$eshopprodimg='_eshop_prod_img';
	$echo ='<ul class="eshop '.$eshopclass.'">';
	include_once( 'eshop-add-cart.php' );

	foreach ($subpages as $post) {
		setup_postdata($post);
		$xclass='<li class="eshop-product-'.$post->ID.'">';
		if(isset($eshopoptions['sale']) && $eshopoptions['sale']=='yes'){
			$esale=get_post_meta( $post->ID, '_eshop_sale',true );
			if($esale=='yes')
				$xclass='<li class="sale eshop-product-'.$post->ID.'"><strong class="onsale"><span>'.__('On Sale','eshop').'</span></strong>';
		}
		$echo .= $xclass;
		$w=apply_filters('eshop_thumbnail_size_w',get_option('thumbnail_size_w'));
		$h=apply_filters('eshop_thumbnail_size_h',get_option('thumbnail_size_h'));
		if($imgsize!=''){
			$w=round(($w*$imgsize)/100);
			$h=round(($h*$imgsize)/100);
		}
		if (has_post_thumbnail( $post->ID ) ) {
			if($links=='yes')
				$echo .='<a class="itemref" href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail( $post->ID, array($w, $h)).'</a>'."\n";
			else
				$echo .=get_the_post_thumbnail( $post->ID, array($w, $h))."\n";
		}else{
			$eimage=eshop_files_directory();
			$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
			if($links=='yes')
				$echo .='<a class="itemref" href="'.get_permalink($post->ID).'"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a>'."\n";
			else
				$echo .='<img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" />'."\n";
		}
		if($links=='yes')
			$echo .= '<a href="'.get_permalink($post->ID).'"><span>'.apply_filters("the_title",$post->post_title, $post->ID).'</span></a>'."\n";
		else
			$echo .= '<span>'.apply_filters("the_title",$post->post_title, $post->ID).'</span>'."\n";
		
		if($price!='no'){
			$currsymbol=$eshopoptions['currency_symbol'];
			$eshop_product=maybe_unserialize(get_post_meta( $post->ID, '_eshop_product',true ));
		}
		if($price=='yes'){
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['price'])){
				$echo.='<span class="ep_price">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['price'],__('2','eshop'))).'</span>';
			}
		}
		if($price=='sale'){
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['saleprice']) && $eshop_product['products']['1']['saleprice']!='' && isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
				$echo.='<span class="ep_price saleprice">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['saleprice'],__('2','eshop'))).'</span>';
			}
		}
		if($price=='both'){
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['price'])){
				$echo.='<span class="ep_price onsale">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['price'],__('2','eshop'))).'</span>';
			}
			if(is_array($eshop_product) && isset($eshop_product['products']['1']['saleprice']) && $eshop_product['products']['1']['saleprice']!='' && isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
				$echo.='<span class="ep_price saleprice">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshop_product['products']['1']['saleprice'],__('2','eshop'))).'</span>';
			}
		}
		if($form=='yes' || $form=='yesqty'){
			if($form=='yesqty') $short='no';
			else $short=$form;
			$echo =eshop_boing($echo,$short,$post->ID,'y');
		}
		$echo .= '</li>'."\n";
	}
	$echo .= '</ul>';
	$post=$paged;
	return $echo;
}
function eshop_show_discounts(){
	global $eshopoptions;
	$edisc=array();
	eshop_cache();
	$currsymbol=$eshopoptions['currency_symbol'];
	$shipdisc=$eshopoptions['discount_shipping'];
	for ($x=1;$x<=3;$x++){
		if($eshopoptions['discount_spend'.$x]!='')
			$edisc[$eshopoptions['discount_spend'.$x]]=$eshopoptions['discount_value'.$x];
	}
	$echo ='';
	$discarray=sizeof($edisc);
	if($discarray>0){
		ksort($edisc);
		$echo='
		<table class="eshop eshopdiscounts" summary="'.__('Discount for amount sold','eshop').'">
		<caption>'.__('Discount for amount sold','eshop').'</caption>
		<thead>
		<tr>
		<th id="elevel">'.__('Discounts','eshop').'</th>
		<th id="espend">'.__('Spend','eshop').'</th>
		<th id="ediscount">'.__('% Discount','eshop').'</th>
		</tr>
		</thead>
		<tbody>';
		$x=0;
		foreach ($edisc as $amt => $percent) {
			$x++;
			$echo .='
			<tr>
			<th headers="elevel"  id="row'.$x.'">'.$x.'</th>
			<td headers="elevel espend row'.$x.'" class="amts">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($amt,__('2','eshop'))).'</td>
			<td headers="elevel ediscount row'.$x.'" class="disc">'.$percent.'</td>
			</tr>';
		}
		$echo .='</table>';
	}
	if($shipdisc>0){
		$echo .='
		<p class="shipdiscount">'.__('Free Shipping if you spend over','eshop').' <span>'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($eshopoptions['discount_shipping'],__('2','eshop'))).'</span></p>';
	}
	return $echo;
}
function eshop_show_payments(){
	global $eshopoptions;
	$echo='';
	eshop_cache();
	if(is_array($eshopoptions['method'])){
		$i=1;
		$replace = array(".");
		$eshopfiles=eshop_files_directory();
		$echo.= "\n".'<ul class="eshop eshoppayoptions">'."\n";
		foreach($eshopoptions['method'] as $k=>$eshoppayment){
			$eshoppayment_text=$eshoppayment;
			$eshoppayment = str_replace($replace, "", $eshoppayment);
			if($eshoppayment_text=='cash'){
				$eshopcash = $eshopoptions['cash'];
				if($eshopcash['rename']!='')
					$eshoppayment_text=$eshopcash['rename'];
			}
			if($eshoppayment_text=='bank'){
				$eshopbank = $eshopoptions['bank'];
				if($eshopbank['rename']!='')
					$eshoppayment_text=$eshopbank['rename'];
			}
			$eshopmi=apply_filters('eshop_merchant_img_'.$eshoppayment,array('path'=>$eshopfiles['0'].$eshoppayment.'.png','url'=>$eshopfiles['1'].$eshoppayment.'.png'));
			$eshopmerchantimgpath=$eshopmi['path'];
			$eshopmerchantimgurl=$eshopmi['url'];
			$dims=array('3'=>'');
			if(file_exists($eshopmerchantimgpath))
				$dims=getimagesize($eshopmerchantimgpath);
			$echo.= '<li><img src="'.$eshopmerchantimgurl.'" '.$dims[3].' alt="'.__('Pay via','eshop').' '.$eshoppayment_text.'" title="'.__('Pay via','eshop').' '.$eshoppayment_text.'" /></li>'."\n";
			$i++;
		}
		$echo.= "</ul>\n";
	}
	return $echo;
}

function eshop_show_shipping($atts) { 
	global $wpdb, $eshopoptions;
	eshop_cache();
	if($eshopoptions['shipping']!='4'){
		extract(shortcode_atts(array('shipclass'=>'A,B,C,D,E,F'), $atts));
		$shipclasses = explode(",", $shipclass);
		$dtable=$wpdb->prefix.'eshop_rates';
		$query=$wpdb->get_results("SELECT * from $dtable where rate_type='shipping' OR rate_type='ship_weight'");
		$currsymbol=$eshopoptions['currency_symbol'];

		$eshopshiptable='<table id="eshopshiprates" summary="'.__('This is a table of our online order shipping rates','eshop').'" class="eshopshiprates eshop">';
		$eshopshiptable.='<caption><span>'.__('Shipping rates by class and zone <small>(subject to change)</small>','eshop').'</span></caption>'."\n";
		$eshopshiptable.='<thead><tr><th id="class">'.__('Ship Class','eshop').'</th>';
		for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
			$y='zone'.$z;
			$echozone=sprintf(__('Zone %1$d','eshop'),$z);
			$dispzone=apply_filters('eshop_rename_ship_zone',array());
			if(isset($dispzone[$z]))
				$echozone=$dispzone[$z];
			$eshopshiptable.='<th id="'.$y.'" class="'.$y.'">'.$echozone.'</th>';
		}
		$eshopshiptable.='</tr></thead>'."\n";
		$eshopshiptable.='<tbody>'."\n";
		$x=1;
		$calt=0;
		switch ($eshopoptions['shipping']){
			case '1':// ( per quantity of 1, prices reduced for additional items )

				$query=$wpdb->get_results("SELECT * from $dtable where rate_type='shipping' ORDER BY class ASC, items ASC");

				foreach ($query as $row){
					if(in_array($row->class,$shipclasses)){
						$calt++;
						$alt = ($calt % 2) ? ' class="eshoprow'.$x.'"' : ' class="alt eshoprow'.$x.'"';
						$row->class=apply_filters('eshop_shipping_rate_class',$row->class);
						$eshopshiptable.= '<tr'.$alt.'>';
						if($row->items==1){
							$eshopshiptable.= '<th id="cname'.$x.'" headers="class">'.$row->class.' <small>'.__('(First Item)','eshop').'</small></th>'."\n";
						}else{
							$eshopshiptable.= '<th id="cname'.$x.'" headers="class">'.$row->class.' <small>'.__('(Additional Items)','eshop').'</small></th>'."\n";
						}
						for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
							$y='zone'.$z;
							$eshopshiptable.= '<td headers="zone'.$z.' cname'.$x.'" class="zone'.$z.'">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, $row->$y).'</td>'."\n";
						}
						$eshopshiptable.= '</tr>';
						$x++;
					}
				}
				break;
			case '2'://( once per shipping class no matter what quantity is ordered )
				$query=$wpdb->get_results("SELECT * from $dtable where items='1' && rate_type='shipping' ORDER BY 'class'  ASC");
				foreach ($query as $row){
					if(in_array($row->class,$shipclasses)){
						$calt++;
						$alt = ($calt % 2) ? ' class="eshoprow'.$x.'"' : ' class="alt eshoprow'.$x.'"';
						$row->class=apply_filters('eshop_shipping_rate_class',$row->class);
						$eshopshiptable.= '<tr'.$alt.'>';
						$eshopshiptable.= '<th id="cname'.$x.'" headers="class">'.$row->class.'</th>'."\n";
						for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
							$y='zone'.$z;
							$eshopshiptable.= '<td headers="'.$y.' cname'.$x.'" class="'.$y.'">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, $row->$y).'</td>'."\n";
						}
						$eshopshiptable.= '</tr>';
						$x++;
					}
				}
				break;
			case '3'://( one overall charge no matter how many are ordered )

				$query=$wpdb->get_results("SELECT * from $dtable where items='1' and class='A' and rate_type='shipping' ORDER BY 'class'  ASC");

				foreach ($query as $row){
					if(in_array($row->class,$shipclasses)){
						$calt++;
						$alt = ($calt % 2) ? ' class="eshoprow'.$x.'"' : ' class="alt eshoprow'.$x.'"';
						$row->class=apply_filters('eshop_shipping_rate_class',$row->class);
						$eshopshiptable.= '<tr'.$alt.'>';
						$eshopshiptable.= '<th id="cname'.$x.'" headers="class">'.$row->class.' <small>'.__('(Overall charge)','eshop').'</small></th>'."\n";
						for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
							$y='zone'.$z;
							$eshopshiptable.= '<td headers="'.$y.' cname'.$x.'" class="'.$y.'">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, $row->$y).'</td>'."\n";
						}
						$eshopshiptable.= '</tr>';
						$x++;
					}
				}
				break;
		}
		if(in_array('F',$shipclasses)){
			$calt++;
			$alt = ($calt % 2) ? ' class="eshoprowf"' : ' class="alt eshoprowf"';
			$row->class=apply_filters('eshop_shipping_rate_class','F');
			$eshopshiptable.= '<tr'.$alt.'>';
			$eshopshiptable.= '<th id="cname'.$x.'" headers="class">'.$row->class.' <small>'.__('(Free)','eshop').'</small></th>'."\n";
			$yzones='';
			for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++)
				$yzones.='zone'.$z.' ';
				
			$eshopshiptable.= '<td headers="'.$yzones.' cname'.$x.'" colspan="'.$eshopoptions['numb_shipzones'].'" class="center">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n('0',__('2','eshop'))).'</td>'."\n";
			$eshopshiptable.= '</tr>';
		}
		$eshopshiptable.='</tbody>'."\n";
		$eshopshiptable.='</table>'."\n";
	}else{
		if(isset($eshopoptions['ship_types'])){
			$dtable=$wpdb->prefix.'eshop_rates';
			$eshopshiptable='';
			$typearr=explode("\n", $eshopoptions['ship_types']);
			$eshopletter = "A";
			$weightsymbol=$eshopoptions['weight_unit'];
			$currsymbol=$eshopoptions['currency_symbol'];
			foreach ($typearr as $k=>$type){
				$k++;
				$eshopshiptableinner ='
				<table class="eshopshiprates eshop" summary="'.__('Shipping rates per mode','eshop').'">
				<caption>'.stripslashes(esc_attr($type)).'</caption>
				<thead>
				<tr>
				<th id="'.$eshopletter.'weight">'. __('Starting weight','eshop').'</th>';
				for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
					$y='zone'.$z;
					$echozone=sprintf(__('Zone %1$d','eshop'),$z);
					$dispzone=apply_filters('eshop_rename_ship_zone',array());
					if(isset($dispzone[$z]))
						$echozone=$dispzone[$z];
					$eshopshiptableinner .='<th id="'.$eshopletter.$y.'" class="'.$y.'">'. $echozone.'</th>';
				}
				$eshopshiptableinner .='</tr>
				</thead>
				<tbody>';
				$x=1;
				$query=$wpdb->get_results("SELECT * from $dtable where class='$k' && rate_type='ship_weight' ORDER BY weight ASC");
				foreach ($query as $row){
					$alt = ($x % 2) ? '' : ' class="alt"';
					/* '1 - weight 2-weight symbol' */
					$eshopshiptableinner .='
					<tr'.$alt.'>
					<th id="'.$eshopletter.'cname'.$x.'" headers="'.$eshopletter.'weight">'.sprintf( __('%1$s %2$s','eshop'), number_format_i18n($row->weight,__('2','eshop')),$weightsymbol).'</th>';
					for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
						$y='zone'.$z;
						$eshopshiptableinner .='<td headers="'.$eshopletter.$y.' ' .$eshopletter.'cname'.$x.'" class="'.$y.'">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, $row->$y).'</td>';
					}
					$eshopshiptableinner .='</tr>';
					$x++;
				}
				$eshopletter++;
				$eshopshiptableinner .='</tbody></table>'."\n";
				
				
				if($row->area=='country')
					$eshopshiptableheadtext = sprintf( __('%1$s <small>%2$s</small>','eshop'),stripslashes(esc_attr($type)), __('(Shipping Zones by Country)','eshop'));
				else
					$eshopshiptableheadtext = sprintf( __('%1$s <small>%2$s</small>','eshop'),stripslashes(esc_attr($type)), __('(Shipping Zones by State/County/Province)','eshop'));
				
				if(isset($row->maxweight) && $row->maxweight!='')
					$eshopshiptableheadtext .= ' '.sprintf( __('Max. Weight %1$s %2$s','eshop'),$row->maxweight,$eshopoptions['weight_unit']);
				$eshopshiptablehead='<h3 class="eshop-ship4-head">'.$eshopshiptableheadtext.'</h3>';
				$eshopshiptable .= $eshopshiptablehead.$eshopshiptableinner;
				
			
			}
		}else{
			$eshopshiptable='';
		}
	}

	if('yes' == $eshopoptions['show_zones']){
		if('country' == $eshopoptions['shipping_zone'])
			$array=array('country');
		if('state' == $eshopoptions['shipping_zone'])
			$array=array('state');
		if( '4' == $eshopoptions['shipping'] ){
			$array=array();
			$dtable=$wpdb->prefix.'eshop_rates';
			$query=$wpdb->get_results("SELECT area from $dtable where rate_type='ship_weight'");
			foreach ($query as $row) 
				$array[]=$row->area;
		}
		$eshopshiptable.=eshop_show_zones($array);
	}
	return $eshopshiptable;

}

if (!function_exists('eshop_show_zones')) {
    /**
     * returns a table of the ones, state or country depending on what is chosen.
     */
    function eshop_show_zones($array, $legend = '' ) { 
		global $wpdb,$eshopoptions;
		if($legend == '')
			$legend = __('Check your shipping zone','eshop');
		eshop_cache();
		$echo='';
		if(in_array('country', $array)){
			//countries
			$tablec=$wpdb->prefix.'eshop_countries';
			$List=$wpdb->get_results("SELECT code,country FROM $tablec GROUP BY list,country",ARRAY_A);
			foreach($List as $key=>$value){
				$k=$value['code'];
				$v=$value['country'];
				$countryList[$k]=$v;
			}
			if(isset($_POST['country']) && $_POST['country']!=''){
				$country=$_POST['country'];
			}
			$echo .='<form action="#customzone" method="post" class="eshop eshopzones eshopcountryz"><fieldset>
			<legend>'.$legend.'</legend>
			 <label for="country">'.__('Country','eshop').' <select class="med" name="country" id="country">';
			$echo .='<option value="">'.__('Please Select','eshop').'</option>';
			foreach($countryList as $code => $label)	{
				if (isset($country) && $country == $code){
					$echo.= "<option value=\"$code\" selected=\"selected\">$label</option>\n";
				}else{
					$echo.="<option value=\"$code\">$label</option>\n";
				}
			}
			$echo.= '</select></label> 
			<span class="buttonwrap"><input type="submit" class="button" id="submititc" name="submit" value="'.__('Submit','eshop').'" /></span>
			</fieldset></form>';
			if(isset($_POST['country']) && $_POST['country']!=''){
				$qccode=$wpdb->escape($_POST['country']);
				$qcountry = $wpdb->get_row("SELECT country,zone FROM $tablec WHERE code='$qccode' limit 1",ARRAY_A);
				$echo .='<p id="customzone">'.sprintf(__('%1$s is in Zone %2$s','eshop'),$qcountry['country'],$qcountry['zone']).'.</p>';
			}

		}
		if(in_array('state', $array)){
			//each time re-request from the database
			// state list from db
			$table=$wpdb->prefix.'eshop_states';
			$getstate=$eshopoptions['shipping_state'];
			if($eshopoptions['show_allstates'] != '1'){
				$stateList=$wpdb->get_results("SELECT code,stateName FROM $table WHERE list='$getstate' ORDER BY stateName",ARRAY_A);
			}else{
				$stateList=$wpdb->get_results("SELECT code,stateName,list FROM $table ORDER BY list,stateName",ARRAY_A);
			}
			
			if(isset($_POST['state']) && $_POST['state']!=''){
				$state=$_POST['state'];
			}
			$echo .='<form action="#customzone" method="post" class="eshop eshopzones eshopstatez"><fieldset>
			<legend>'.$legend.'</legend>
			<label for="state">'.__('State, County or Province','eshop').'<select class="med" name="state" id="state">';
			$echo .='<option value="">'.__('Please select','eshop').'</option>';

			foreach($stateList as $code => $value){
				if(isset($value['list'])) $li=$value['list'];
				else $li='1';
				$eshopstatelist[$li][$value['code']]=$value['stateName'];
			}
			$tablec=$wpdb->prefix.'eshop_countries';
			foreach($eshopstatelist as $egroup =>$value){
				$eshopcname=$wpdb->get_var("SELECT country FROM $tablec where code='$egroup' limit 1");

				$echo .='<optgroup label="'.$eshopcname.'">'."\n";
				foreach($value as $code =>$stateName){
					$stateName=htmlspecialchars($stateName);
					if (isset($state) && $state == $code){
						$echo.= '<option value="'.$code.'" selected="selected">'.$stateName."</option>\n";
					}else{
						$echo.='<option value="'.$code.'">'.$stateName."</option>\n";
					}
				}
				$echo .="</optgroup>\n";
			}
			$echo.= "</select></label>\n".'
			<span class="buttonwrap"><input type="submit" class="button" id="submitist" name="submit" value="'.__('Submit','eshop').'" /></span>
			</fieldset></form>';
			if(isset($_POST['state']) && $_POST['state']!=''){
				$qccode=$wpdb->escape($_POST['state']);
				$qstate = $wpdb->get_row("SELECT stateName,zone FROM $table WHERE code='$qccode' limit 1",ARRAY_A);
				$echo .='<p id="customzone">'.sprintf(__('%1$s is in Zone %2$s','eshop'),$qstate['stateName'],$qstate['zone']).'.</p>';
			}
		}
		if(get_bloginfo('version')<'2.5.1')
			remove_filter('the_content', 'wpautop');

		return $echo;
	}
}
function eshop_addtocart($atts, $content = ''){
	global $wpdb, $post;
	eshop_cache();
	extract(shortcode_atts(array('hide'=>'','id'=>''), $atts));
	if($hide!='' && !is_user_logged_in())
		return $content;
	include_once( 'eshop-add-cart.php' );
	return eshop_boing('','short',$id,'y');
}
function eshop_welcome($atts, $content = ''){
	global $blog_id;
	
	extract(shortcode_atts(array('before'=>'','returning'=>'','guest'=>'','after'=>''), $atts));
	$echo='';
	if($before!='')
		$echo.=$before.' ';
	if(isset($_COOKIE["eshopcart"]) || is_user_logged_in()){
		if($returning!='')
			$echo.=$returning.' ';
		$eshopsetcookie=apply_filters('eshop_use_cookie',true);
		if(isset($_COOKIE["eshopcart"]) && $eshopsetcookie==true)
			$crumbs=eshop_break_cookie($_COOKIE["eshopcart"]);
		if(is_user_logged_in()){
			global $current_user;
		     get_currentuserinfo();
			$crumbs['first_name']=$current_user->display_name;
			$crumbs['last_name']='';
		}
		$echo .=$crumbs['first_name'].' '.$crumbs['last_name'];
	}else{
		$echo.=$guest.' ';
	}
	if($content!='')
		$echo.=$content.' ';	
	if($after!='')
		$echo.=' '.$after;
	return $echo;
}

function eshop_show_cancel(){
	global $wp_query;
	if(isset($wp_query->query_vars['eshopaction'])) {
		$echo = '';
		$eshopaction = urldecode($wp_query->query_vars['eshopaction']);
		if($eshopaction=='cancel'){
			$echo .= '<h3 class="eshoperror error">'.__('The order was cancelled.','eshop')."</h3>";
			$echo .= '<p>'.__('We have not emptied your shopping cart in case you want to make changes.','eshop').'</p>';
		}
		$postit='';
		if(isset($_POST)) $postit=$_POST;
		$echo = apply_filters('eshop_show_cancel', $echo, $eshopaction, $postit);
		return $echo;
	}
	return;
}


function eshop_show_success(){
	global $wpdb,$eshopoptions;
	//cache
	eshop_cache();
	$echo='';
	global $wp_query;
	if(isset($wp_query->query_vars['eshopaction'])) {
		$eshopaction = urldecode($wp_query->query_vars['eshopaction']);
		if($eshopaction=='success' && isset($_POST['txn_id'])){
			$detailstable=$wpdb->prefix.'eshop_orders';
			$dltable=$wpdb->prefix.'eshop_download_orders';
			if($eshopoptions['status']=='live'){
				$txn_id = $wpdb->escape($_POST['txn_id']);
			}else{
				$txn_id = __('TEST-','eshop').$wpdb->escape($_POST['txn_id']);
			}
			$checkid=$wpdb->get_var("select checkid from $detailstable where transid='$txn_id' && downloads='yes' limit 1");
			$checkstatus=$wpdb->get_var("select status from $detailstable where transid='$txn_id' && downloads='yes' limit 1");

			if(($checkstatus=='Sent' || $checkstatus=='Completed') && $checkid!=''){
				$row=$wpdb->get_row("select email,code from $dltable where checkid='$checkid' and downloads>0 limit 1");
				if($row->email!='' && $row->code!=''){
					//display form only if there are downloads!
						$echo = '<form method="post" class="dform" action="'.get_permalink($eshopoptions['show_downloads']).'">
					<p class="submit"><input name="email" type="hidden" value="'.$row->email.'" /> 
					<input name="code" type="hidden" value="'.$row->code.'" /> 
					<span class="buttonwrap"><input type="submit" id="submit" class="button" name="Submit" value="'.__('View your downloads','eshop').'" /></span></p>
					</form>';
				}
			}
		}elseif($eshopaction=='success' && isset($_GET['epn'])){
			include_once (WP_PLUGIN_DIR.'/eshop/epn/process.php');
			if(isset($_GET['epn']) && $_GET['epn']=='ok' && isset($_POST['transid'])){
				$detailstable=$wpdb->prefix.'eshop_orders';
				$dltable=$wpdb->prefix.'eshop_download_orders';
				if($eshopoptions['status']=='live'){
					$txn_id = $wpdb->escape($_POST['transid']);
				}else{
					$txn_id = __('TEST-','eshop').$wpdb->escape($_POST['transid']);
				}
				$checkid=$wpdb->get_var("select checkid from $detailstable where transid='$txn_id' && downloads='yes' limit 1");
				$checkstatus=$wpdb->get_var("select status from $detailstable where transid='$txn_id' && downloads='yes' limit 1");

				if(($checkstatus=='Sent' || $checkstatus=='Completed') && $checkid!=''){
					$row=$wpdb->get_row("select email,code from $dltable where checkid='$checkid' and downloads>0 limit 1");
					if($row->email!='' && $row->code!=''){
						//display form only if there are downloads!
							$echo = '<form method="post" class="dform" action="'.get_permalink($eshopoptions['show_downloads']).'">
						<p class="submit"><input name="email" type="hidden" value="'.$row->email.'" /> 
						<input name="code" type="hidden" value="'.$row->code.'" /> 
						<span class="buttonwrap"><input type="submit" id="submit" class="button" name="Submit" value="'.__('View your downloads','eshop').'" /></span></p>
						</form>';
					}
				}
			}
		}
		elseif($eshopaction=='authorizenetipn'){
			// because authorize.net handles things differently... have to add this in here
			$detailstable=$wpdb->prefix.'eshop_orders';
			$dltable=$wpdb->prefix.'eshop_download_orders';
			if($eshopoptions['status']=='live'){
				$txn_id = $wpdb->escape($_POST['x_trans_id']);
			}else{
				$txn_id = __('TEST-','eshop').$wpdb->escape($_POST['x_trans_id']);
			}
			$checked=$wpdb->get_var("select checkid from $detailstable where transid='$txn_id' && downloads='yes' order by id DESC limit 1");

			$checkstatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' && downloads='yes' limit 1");
			if(($checkstatus=='Sent' || $checkstatus=='Completed') && $checked!=''){
				$row=$wpdb->get_row("select email,code from $dltable where checkid='$checked' and downloads>0 limit 1");
				if($row->email!='' && $row->code!=''){
					//display form only if there are downloads!
						$echo = '<form method="post" class="dform" action="'.get_permalink($eshopoptions['show_downloads']).'">
					<p class="submit"><input name="email" type="hidden" value="'.$row->email.'" /> 
					<input name="code" type="hidden" value="'.$row->code.'" /> 
					<span class="buttonwrap"><input type="submit" id="submit" class="button" name="Submit" value="'.__('View your downloads','eshop').'" /></span></p>
					</form>';
				}
			}
			// Start of TEMCEDIT-20091117-a
		}elseif($eshopaction=='idealliteipn' && isset($_GET['ideal']['status']) ){
			if($_GET['ideal']['status'] == md5("SUCCESS") ) {
					$echo ='<h3 class="success">'.__('Thank you for your order','eshop')." !</h3>";
					$echo.= '<p>'.__('Your iDEAL payment has been succesfully received.','eshop').'<br />';
					$echo.= __('We will get on it as soon as possible.','eshop').'</p>';
			}elseif($_GET['ideal']['status'] == md5("ERROR") ) {
					$echo ='<h3 class="eshoperror error">'.__('The payment failed at iDEAL.','eshop')."</h3>";
					$echo.= '<p>'.__('Your iDEAL payment has not been received yet, and currently has status "ERROR".','eshop').'<br />';
					$echo.= __('Please try to checkout your order again.','eshop').'</p>';
					$echo.='<p>'.__('We have not emptied your shopping cart in case you want to make changes.','eshop').'</p>';
			}elseif($_GET['ideal']['status'] == md5("CANCEL") ) {
					$echo ='<h3 class="eshoperror error">'.__('The payment was cancelled at iDEAL.','eshop')."</h3>";
					$echo.= '<p>'.__('Your iDEAL payment has not been revieced yet, and currently has status "CANCEL".','eshop').'<br />';
					$echo.= __('Please try to checkout your order again.','eshop').'</p>';
			}else{
					$echo ='<h3 class="eshoperror error">'.__('The payment failed at iDEAL.','eshop')."</h3>";
					$echo.= '<p>'.__('Please try checkout your order again.','eshop').'</p>';
			}
			// End of TEMCEDIT-20091117-a
		}
		$postit='';
		if(isset($_POST)) $postit=$_POST;
		$echo = apply_filters('eshop_show_success', $echo, $eshopaction, $postit);
		
		return $echo;
	}
}
function eshop_details($atts){
	global $wpdb, $post,$wp_query,$eshopoptions;
	eshop_cache();
	extract(shortcode_atts(array('class'=>'eshopdetails','show'=>'','options_hide'=>'','etax_page'=>''), $atts));
	$echo='';
	$allowedtoshow=array('sku','description','options','optionset','shipping');
	$willshow=array();
	if($show!=''){
		$wanttoshow=explode(",", $show);
		foreach($wanttoshow as $showit){
			$showit=trim($showit);
			if(in_array($showit,$allowedtoshow)) $willshow[]=$showit;
		}
	}else{
		$willshow=$allowedtoshow;
	}
	
	$allowedtohide=array('price','saleprice','tax','download','weight','stockqty','filesize');
	$willhide=array();
	if($options_hide!=''){
		$wanttohide=explode(",", $options_hide);
		foreach($wanttohide as $hideit){
			$hideit=trim($hideit);
			if(in_array($hideit,$allowedtohide)) $willhide[]=$hideit;
		}
	}else{
		$willhide=array();
	}
	
	$listed='';
	$producttable = $wpdb->prefix ."eshop_downloads";
	$eshop_product=maybe_unserialize(get_post_meta($post->ID, '_eshop_product','true'));
	$eshopdlavail = $wpdb->get_var("SELECT COUNT(id) FROM $producttable WHERE id > 0");
	$numoptions=$eshopoptions['options_num'];
	$currsymbol=$eshopoptions['currency_symbol'];
	if(!empty($eshopoptions['weight_unit']))
		$weightsymbol=$eshopoptions['weight_unit'];
	else
		$weightsymbol='';
	$eshopletter = "A";
	$stocktable=$wpdb->prefix ."eshop_stock";
	foreach($willshow as $listit){
		switch($listit){
			case 'sku':
				if(isset($eshop_product['sku'])){
					$listed.='<dt>'.__('Sku','eshop')."</dt>\n";
					$listed.='<dd>'.$eshop_product['sku']."</dd>\n";
				}
				break;
			case 'description':
				if(isset($eshop_product['description'])){
					$listed.='<dt>'.__('Description','eshop')."</dt>\n";
					$listed.='<dd>'.$eshop_product['description']."</dd>\n";
				}
				break;
			case 'options':
				if(isset($eshop_product['products'])){
				    $thclass='';
					$listed.='<dt>'.__('Product Options','eshop')."</dt>\n";
					$listed.='<dd>';
					$listed.='<table class="eshop" summary="'.__('Product Options by option price and download','eshop').'">
    				<thead>
    				<tr>';
    				if(!in_array('option',$willhide))
    					$listed.='<th id="'.$eshopletter.'eshopnum">#</th><th id="'.$eshopletter.'eshopoption">'. __('Option','eshop').'</th>';
    				if(!in_array('price',$willhide)){
    					$thprice=__('Price','eshop');
	    				$listed.='<th id="'.$eshopletter.'eshopprice"'.$thclass.'>'.$thprice.'</th>';
	    			}
	    			if(!in_array('saleprice',$willhide) && isset($eshopoptions['sale_prices']) && $eshopoptions['sale_prices'] == 1 
	    			&& isset($eshopoptions['sale']) && 'yes' == $eshopoptions['sale']){
					   	$thprice=__('Sale Price','eshop');
						if(isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
							$thprice=__('Sale Price','eshop');
						}
					   	$listed.='<th id="'.$eshopletter.'eshopsaleprice" class="sale">'.$thprice.'</th>';
	    			}
	    			if(!in_array('tax',$willhide) && isset($eshopoptions['tax']) &&  $eshopoptions['tax']=='1'){
	    				$listed.='<th id="'.$eshopletter.'eshoptax">'.__('Sales Tax','eshop').'</th>';
	    			}
    				if($eshopdlavail>0 && !in_array('download',$willhide)){ 
    					$listed.='<th id="'.$eshopletter.'eshopdownload">'.__('Download','eshop').'</th>';
    				}
    				if($eshopdlavail>0 && !in_array('filesize',$willhide)){ 
					    $listed.='<th id="'.$eshopletter.'eshopdownloadsize">'.__('File Size','eshop').'</th>';
    				} 
    				if($eshopoptions['shipping']=='4' && !in_array('weight',$willhide)){
    					$listed.='<th id="'.$eshopletter.'eshopweight">'.__('Weight','eshop').'</th>';
    				}
    				if(!in_array('stockqty',$willhide) && 'yes' == $eshopoptions['stock_control'])
    					$listed.='<th id="'.$eshopletter.'eshopstk">'.__('Stock','eshop').'</th>';
    				$listed.='</tr></thead>
        			<tbody>'."\n";
        			if('yes' == $eshopoptions['stock_control']){
						$stkq=$wpdb->get_results("SELECT option_id, available from $stocktable where post_id=$post->ID");
						foreach($stkq as $thisstk){
							if($thisstk->available<0) $thisstk->available=0;
							$stkarr[$thisstk->option_id]=$thisstk->available;
						}
					}
					for($i=1;$i<=$numoptions;$i++){
						$thclass='';
						if(isset($eshop_product['products'][$i]) && is_array($eshop_product['products'][$i])){
							$opt=$eshop_product['products'][$i]['option'];
							$price=$eshop_product['products'][$i]['price'];
							$downl='';
							if(isset($eshop_product['products'][$i]['download']))
								$downl=$eshop_product['products'][$i]['download'];
							if(isset($eshop_product['products'][$i]['weight']) && $eshop_product['products'][$i]['weight']!='') 
								$weight=$eshop_product['products'][$i]['weight'];
							else
								$weight='0';

							if(isset($eshop_product['products'][$i]['stkqty']) && $eshop_product['products'][$i]['stkqty']!='' && 'yes' == $eshopoptions['stock_control'])
								$stklvl=$stkarr[$i];
							else
								$stklvl='0';
							if(isset($eshop_product['products'][$i]['tax'])) 
								$eshoptaxband=$eshop_product['products'][$i]['tax'];
							else
								$eshoptaxband='0';
						}else{
							$opt=$price=$downl='';
							$stklvl=$weight=$price='0';
							$eshoptaxband='';
						}
						if($opt=='') break;
						$alt = ($i % 2) ? '' : ' class="alt"';
						$listed.='<tr'.$alt.'>';
						$listed.='<th id="'.$eshopletter.'eshopnumrow'.$i.'" headers="'.$eshopletter.'eshopnum">'.$i.'</th>';
						if(!in_array('option',$willhide))
							$listed.='<td headers="'.$eshopletter.'eshopoption '.$eshopletter.'eshopnumrow'.$i.'">'.stripslashes(esc_attr($opt)).'</td>';
						if(!in_array('price',$willhide)){
							if(isset($eshop_product['products'][$i]['saleprice']) && $eshop_product['products'][$i]['saleprice']!=''
							&& isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
								$thclass=' class="onsale"';
							}
							$listed.='<td headers="'.$eshopletter.'eshopprice '.$eshopletter.'eshopnumrow'.$i.'"'.$thclass.'>'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($price,__('2','eshop'))).'</td>';
						}
						if(!in_array('saleprice',$willhide) && isset($eshopoptions['sale_prices']) && $eshopoptions['sale_prices'] == 1 
						&& isset($eshopoptions['sale']) && 'yes' == $eshopoptions['sale']){
							$thprice=__('Sale Price','eshop');
							$sprice='';
							if(isset($eshop_product['products'][$i]['saleprice']) && $eshop_product['products'][$i]['saleprice']!=''
							&& isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
								$sprice=$eshop_product['products'][$i]['saleprice'];
								$sprice=sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($sprice,__('2','eshop')));
							}
							$listed.='<td headers="'.$eshopletter.'eshopsaleprice '.$eshopletter.'eshopnumrow'.$i.'" class="saleprice">'.$sprice.'</td>';
	    				}
						
						
						//tax
						if(!in_array('tax',$willhide) && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1'){
							if($eshoptaxband!='' && $eshoptaxband!='0'){
								$tzone=sprintf(__('Band %1$d','eshop'),$eshoptaxband);
								$disptzone=apply_filters('eshop_rename_tax_zone',array());
								if(isset($disptzone[$eshoptaxband]))
									$tzone=$disptzone[$eshoptaxband];
							}else{
								$tzone='';
							}
							$listed.='<td headers="'.$eshopletter.'eshoptax '.$eshopletter.'eshopnumrow'.$i.'"'.$thclass.'>'. $tzone.'</td>';
						}
						
						
						
						
						if($eshopdlavail>0 && !in_array('download',$willhide)){
    						$myrowres=$wpdb->get_results("Select * From $producttable");
							$listed.='<td headers="'.$eshopletter.'eshopdownload '.$eshopletter.'eshopnumrow'.$i.'">';
							foreach($myrowres as $prow){
								if( trim( $prow->id ) == trim( $downl ) ){
									$listed.=stripslashes(esc_attr($prow->title))."\n";
								}
							}
							$listed .="</td>";

						}
						if($eshopdlavail>0 && !in_array('filesize',$willhide)){
							$downlsize='';
						    $myrowres=$wpdb->get_results("Select * From $producttable");
							foreach($myrowres as $prow){
								if( trim( $prow->id ) == trim( $downl ) ){
									$filepath=eshop_download_directory().$prow->files;
									$size = eshop_filesize($prow->files);
									$downlsize=eshop_read_filesize($size);
								}
							}
							$listed.='<td headers="'.$eshopletter.'eshopdownloadsize '.$eshopletter.'eshopnumrow'.$i.'">';
							$listed.= $downlsize."\n";
							$listed .="</td>";

						}
						if($eshopoptions['shipping']=='4' && !in_array('weight',$willhide)){//shipping by weight 
							/* ,'1- weight 2-weight symbol' */
							$listed.='<td headers="'.$eshopletter.'eshopweight '.$eshopletter.'eshopnumrow'.$i.'">'.sprintf( __('%1$s %2$s','eshop'), number_format_i18n($weight,__('2','eshop')),$weightsymbol).'</td>';
						}
    					if(!in_array('stockqty',$willhide) && 'yes' == $eshopoptions['stock_control']){//stock
    						if(isset($stkarr[$i])) 
    							$dstk=$stkarr[$i];
    						else 
    							$dstk=0;
    						$listed.='<td headers="'.$eshopletter.'eshopstk '.$eshopletter.'eshopnumrow'.$i.'">'.$dstk.'</td>';
						}
						$listed.="</tr>\n";
					 }
					 $eshopletter++;
					$listed.='</tbody></table>'."\n";
					
					if(!in_array('tax',$willhide) && isset($eshopoptions['tax']) && $eshopoptions['tax']=='1' && $etax_page!='')
						$listed .= '<p><a href="'.get_permalink($etax_page).'#eshoptaxtable">'.__('Sales tax rates','eshop').'</a></p>';
					$listed.="</dd>\n";
				}
				break;
			case 'optionset':
				if(isset($eshop_product['optset'])){
					$osets=$eshop_product['optset'];
					$thclass='';
					$thprice=__('Price','eshop');
					if(isset($eshop_product['sale']) && $eshop_product['sale']=='yes'){
						$thprice=__('Sale Price','eshop');
						$thclass=' class="sale"';
					}
					if(is_array($osets)){
						$listed.='<dt>'.__('Additional Options','eshop')."</dt>\n";
						$listed.='<dd>';
						$opttable=$wpdb->prefix.'eshop_option_names';
						$optsettable=$wpdb->prefix.'eshop_option_sets';
						foreach($osets as $optid){
							$myrowres=$wpdb->get_results($wpdb->prepare("select name as optname, price,weight from $optsettable where optid='%d' ORDER by id ASC",$optid));
							$egrab=$wpdb->get_row($wpdb->prepare("select * from $opttable where optid='%d' LIMIT 1",$optid));
							if(isset($egrab->name)){
								$ename=$egrab->name;
								$etype=$egrab->type;
								$edesc=$egrab->description;
								$checkrows=sizeof($myrowres);
								$i=1;
								$tbody='';
								foreach($myrowres as $myrow){
									if($myrow->weight=='')
										$myrow->weight='0';
									$alt = ($i % 2) ? '' : ' class="alt"';
									$tbody.="<tr".$alt.">\n".
									'<th id="'.$eshopletter.'eshopnumrow'.$i.'" headers="'.$eshopletter.'eshopnum">'.$i.'</th>';
									if(!in_array('option',$willhide))
										$tbody.='<td headers="'.$eshopletter.'eshopoption '.$eshopletter.'eshopnumrow'.$i.'">'.stripslashes(esc_attr($myrow->optname)).'</td>';
									if(!in_array('price',$willhide))
										$tbody.='<td headers="'.$eshopletter.'eshopprice '.$eshopletter.'eshopnumrow'.$i.'"'.$thclass.'>'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($myrow->price,__('2','eshop'))).'</td>';
									if($eshopoptions['shipping']=='4' && !in_array('weight',$willhide)){
										/* ,'1- weight 2-weight symbol' */
										$tbody.='<td headers="'.$eshopletter.'eshopweight '.$eshopletter.'eshopnumrow'.$i.'">'.sprintf( __('%1$s %2$s','eshop'), number_format_i18n($myrow->weight,__('2','eshop')),$weightsymbol).'</td>';
									}
									$tbody.="</tr>\n";
									$i++;
								}
								$listed.='<strong>'.stripslashes(esc_attr($ename)).'</strong><br />';
								$listed.=nl2br(stripslashes(esc_attr($edesc)));
								$listed.='<table class="eshop" summary="'.__('Product Options by option and price','eshop').'">
								<thead><tr>
								<th id="'.$eshopletter.'eshopnum">#</th>';
								if(!in_array('option',$willhide))
									$listed.='<th id="'.$eshopletter.'eshopoption">'.__('Option','eshop').'</th>';
								if(!in_array('price',$willhide)){
									$listed.='<th id="'.$eshopletter.'eshopprice"'.$thclass.'>'.$thprice.'</th>';
								}
								if($eshopoptions['shipping']=='4' && !in_array('weight',$willhide))
									$listed.='<th id="'.$eshopletter.'eshopweight">'. __('Weight','eshop').'</th>';
								$listed.='</tr></thead><tbody>'."\n";
								$listed.=$tbody;
								$listed.='</tbody></table>'."\n";
								$eshopletter++;
							}
						}
						$listed.="</dd>\n";
					}
				}
				break;
			case 'shipping':
				if(isset($eshop_product['shiprate']) && $eshopoptions['shipping']!='4'){
					$listed.='<dt>'.__('Shipping','eshop')."</dt>\n";
					if($eshopoptions['cart_shipping']!=''){
						$replace ='<a href="'.get_permalink($eshopoptions['cart_shipping']).'#eshopshiprates"><span>'.__('Shipping Rate:','eshop').'</span> '.$eshop_product['shiprate'].'</a>';
					}else{
						$replace ='<span>'.__('Shipping Rate:','eshop').'</span> '.$eshop_product['shiprate'];
					}
					$listed.='<dd>'.$replace."</dd>\n";
				}elseif(isset($eshop_product['shiprate']) && $eshopoptions['shipping']=='4'){
					unset($weight);
					$listed.='<dt>'.__('Shipping','eshop')."</dt>\n";
					//only for ship by weight need to grab weights
					for($i=1;$i<=$numoptions;$i++){
						if(isset($eshop_product['products']) && is_array($eshop_product['products'])){
							if(isset($eshop_product['products'][$i]['weight']) && $eshop_product['products'][$i]['weight']!='' ) 
								$weight[]=$eshop_product['products'][$i]['weight'];
							else
								$weight[]=0;
						}else{
							$weight[]=0;
						}
					}
					//expand $weight
					$cartweight="weight<='".implode("' || weight<='",$weight)."'";
					$typearr=explode("\n", $eshopoptions['ship_types']);
					$eshopshiptable='';
					$dtable=$wpdb->prefix.'eshop_rates';
					foreach ($typearr as $k=>$type){
						$k++;
						$eshopshiptabletop = '<table class="eshopshiprates eshop" summary="'.__('Shipping rates per mode','eshop').'">
						<thead>
						<tr>
						<th id="'.$eshopletter.'wt">'. __('Weight','eshop').'</th>';
						for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
							$y='zone'.$z;
							$echozone=sprintf(__('Zone %1$d','eshop'),$z);
							$dispzone=apply_filters('eshop_rename_ship_zone',array());
							if(isset($dispzone[$z]))
								$echozone=$dispzone[$z];
							$eshopshiptabletop .='<th id="'.$eshopletter.$y.'">'. $echozone.'</th>';
						}
						$eshopshiptabletop .='</tr>
						</thead>
						<tbody>';
						$x=1;
						
						$query=$wpdb->get_results("SELECT * from $dtable  where ($cartweight) && class='$k' && rate_type='ship_weight' order by weight ASC");
									
						if(count($query)==0) 
							break;
						$eshopshiptableinner = '';
						foreach ($query as $row){
							$alt = ($x % 2) ? '' : ' class="alt"';
							/* ,'1- weight 2-weight symbol' */
							$cols=$eshopoptions['numb_shipzones']+1;

							$eshopshiptableinner.='
							<tr'.$alt.'>
							<th headers="'.$eshopletter.'wt">'.sprintf( __('%1$s %2$s','eshop'), number_format_i18n($row->weight,__('2','eshop')),$weightsymbol).'</th>';
							for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
								$y='zone'.$z;
								$eshopshiptableinner.='<td headers="'.$eshopletter.$y.'">'.sprintf( __('%1$s%2$s','eshop'), $currsymbol, $row->$y).'</td>';
							}
							$eshopshiptableinner.='</tr>';
							$x++;
						}
						$eshopletter++;
						$eshopshiptablefoot ='</tbody></table>'."\n";
						if($row->area=='country')
							$eshopshiptableheader = '<span class="eshopshiphead">'.sprintf( __('%1$s <small>%2$s</small>','eshop'),stripslashes(esc_attr($type)), __('(Shipping Zones by Country)','eshop'));
						else
							$eshopshiptableheader = '<span class="eshopshiphead">'.sprintf( __('%1$s <small>%2$s</small>','eshop'),stripslashes(esc_attr($type)), __('(Shipping Zones by State/County/Province)','eshop'));

						$eshopshiptable .= $eshopshiptableheader . $eshopshiptabletop . $eshopshiptableinner . $eshopshiptablefoot;
					
					}
					$listed .='<dd>'.$eshopshiptable.'</dd>';
				}
				
				
				break;
		}
	}
	if($listed!='')
		$addclass='';
		if($class!='' && $class!='eshopdetails')
			$addclass=' '.$class;
		$echo ='<div class="eshopdetails'.$addclass.'"><dl>'."\n".$listed.'</dl></div>';
	
	return $echo;
	
}

function eshop_show_cart() {
	global $wpdb, $blog_id,$wp_query,$eshopoptions;
	$echo='';
	include "cart-functions.php";
	//cache
	eshop_cache();
	if(isset($_SESSION['eshopcart'.$blog_id]['error'])){
		$echo .= $_SESSION['eshopcart'.$blog_id]['error'];
		unset($_SESSION['eshopcart'.$blog_id]['error']);
	}
	if(isset($_SESSION['eshopcart'.$blog_id]['enote'])){
		$echo .= $_SESSION['eshopcart'.$blog_id]['enote'];
		unset($_SESSION['eshopcart'.$blog_id]['enote']);
	}
	if(isset($_SESSION['eshopcart'.$blog_id])){
		if((isset($wp_query->query_vars['eshopaction']) && urldecode($wp_query->query_vars['eshopaction'])=='cancel') && !isset($_POST['save'])){
			$echo.= "<h3>".__('The order was cancelled.','eshop')."</h3>"; 
			$echo.= '<p>'.__('We have not deleted the contents of your shopping cart in case you may want to edit its content.','eshop').'</p>';
		}
		if($eshopoptions['shop_page']!=''){
			$return=get_permalink($eshopoptions['shop_page']);
		}elseif(isset($_SESSION['lastproduct'.$blog_id])){
			$return=get_permalink($_SESSION['lastproduct'.$blog_id]);
		}else{
			$return=get_permalink($eshopoptions['cart']);
		}
		$echo.= display_cart($_SESSION['eshopcart'.$blog_id],'true', $eshopoptions['checkout']);
		$echo.='<ul class="continue-proceed"><li class="rtnshopping"><a href="'.$return.'">'.__('&laquo; Continue Shopping','eshop').'</a></li>
		<li class="gotocheckout"><a href="'.get_permalink($eshopoptions['checkout']).'">'.__('Proceed to Checkout &raquo;','eshop').'</a></li></ul>';
	}else{
		//can be altered as desired.
		$echo.= '<p><strong class="eshoperror error">'.__('Your shopping cart is currently empty.','eshop').'</strong></p>';
	}
	return $echo;
}

function eshop_show_checkout($atts, $content = ''){
	global $blog_id;
	extract(shortcode_atts(array('membersonly'=>''), $atts));
	if($membersonly!='' &&  !is_user_logged_in()){
		return $content;
	}
	include_once 'checkout.php';
	return eshop_checkout($_POST);
}

function eshop_show_downloads($atts, $content = ''){
	extract(shortcode_atts(array('images'=>''), $atts));
	if($images!='')
		$images='add';
	include_once 'purchase-downloads.php';
	return eshop_downloads($_POST, $images, $content);
}
function eshop_tax_rates($atts){
	global $wpdb, $eshopoptions;
	extract(shortcode_atts(array('zonechecker'=>''), $atts));
	$string='';
	if(!isset($eshopoptions['etax'])) $eshopoptions['etax']=array();
	$etax = $eshopoptions['etax'];
	if(!isset($etax['bands'])) $etax['bands']='0';
	$dtable=$wpdb->prefix.'eshop_rates';
	if($etax['bands']!='0' && $eshopoptions['tax']=='1'){
		$eshopletter = "A";
		for($i=1;$i<=2;$i++){
			if($i==1){
				$area='state';
				$string.= '<h4>'.__('Local Tax rates.','eshop').'</h4>';
			}else{
				$area='country';
				$string.='<h4>'.__('Tax rates for Other Countries.','eshop').'</h4>';
			}
			$query=$wpdb->get_results("SELECT * from $dtable where rate_type='tax' && area='$area' ORDER BY class ASC");
			$k=0;
			$string.='
			<table class="eshop" id="eshoptaxtable">
				<thead>
				<tr>
					<th id="'.$eshopletter.'tax">'. __('Tax Band','eshop').'</th>';
				if($etax['zonal']=='1'){
					for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
						$echozone=sprintf(__('Zone %1$d','eshop'),$z);
						$dispzone=apply_filters('eshop_rename_ship_zone',array());
						if(isset($dispzone[$z]))
							$echozone=$dispzone[$z];
						$string.='<th id="'.$eshopletter.'zone'. $z.'" class="zone'.$z.'">'.$echozone.'</th>';
					}
				}else{
					$z=1;
					$string.='<th id="'.$eshopletter.'zone'. $z.'" class="zone'.$z.'">'.__('All Zones','eshop').'</th>';

				}
				$string.='
				</tr>
				</thead>
				<tbody>';
				$x=1;
				foreach ($query as $row){
					$alt = ($x % 2) ? '' : ' class="alt"';
					$string.= '<tr'.$alt.'>';
					$tzone=sprintf(__('Band %1$d','eshop'),$x);
					$disptzone=apply_filters('eshop_rename_tax_zone',array());
					if(isset($disptzone[$x]))
						$tzone=$disptzone[$x];
					$string.= '<td id="'.$eshopletter.'cname'.$x.'" headers="'.$eshopletter.'tax">'.$tzone.'</td>'."\n";
					if($etax['zonal']=='1'){
						for($z=1;$z<=$eshopoptions['numb_shipzones'];$z++){
							$y='zone'.$z;
							$echozone=sprintf(__('Zone %1$d','eshop'),$z);
							$dispzone=apply_filters('eshop_rename_ship_zone',array());
							if(isset($dispzone[$z]))
								$echozone=$dispzone[$z];
							$string.= '<td headers="'.$eshopletter.'zone'.$z.' '.$eshopletter.'cname'.$x.'" class="zone'.$z.'">'.$row->$y.'</td>'."\n";
						}
					}else{
						$z=1;
						$y='zone'.$z;
						$string.= '<td headers="'.$eshopletter.'zone'.$z.' '.$eshopletter.'cname'.$x.'" class="zone'.$z.'">'.$row->$y.'</td>'."\n";

					}
					$string.= '</tr>';
					if($x >= $etax['bands']) 
						break;
					$x++;
				}
				$eshopletter++;
				$string.='
				</tbody>
				</table>';
		}
		if($zonechecker!=''){
			$array=array('state', 'country');
			$legend=__('Check your tax zone','eshop');
			$string.=eshop_show_zones($array,$legend);
		}
	}
	return $string;
}
?>