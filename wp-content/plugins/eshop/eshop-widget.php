<?php
function eshopwidgets_init(){
	register_widget('eshop_widget');
	register_widget('eshop_cart_widget');
	register_widget('eshop_pay_widget');
	register_widget('eshop_products_widget');
	register_widget('eshop_search_widget');
}
add_action("widgets_init", "eshopwidgets_init");

/* *************************
** Main eShop cart widget **
************************** */
class eshop_widget extends WP_Widget {

	function eshop_widget() {
		$widget_ops = array('classname' => 'eshop-widget eshopcart_widget', 'description' => __('Displays a simplified or full cart','eshop'));
		$this->WP_Widget('eshopw_cart', __('eShop Cart','eshop'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		global $blog_id,$eshopoptions;
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$show = apply_filters( 'widget_text', $instance['show'], $instance );
		$showwhat = apply_filters( 'widget_text', $instance['showwhat'], $instance );
		$text = apply_filters( 'widget_text', $instance['text'], $instance );

		if(isset($_SESSION['eshopcart'.$blog_id])){
			$eshopsize=0;
			$eshopqty=0;
			if(isset($_SESSION['eshopcart'.$blog_id])){
				$eshopsize=sizeof($_SESSION['eshopcart'.$blog_id]);
				
				foreach($_SESSION['eshopcart'.$blog_id] as $eshopdo=>$eshopwop){
					$eshopqty+=$eshopwop['qty'];
				}
				if($showwhat=='full'){
					$eecho='<div class="eshopcartwidget"><div class="ajaxcart">'.display_cart($_SESSION['eshopcart'.$blog_id],false, $eshopoptions['checkout'],'widget').'</div>';
					$eecho .='</div>';
				}else{
					$eecho='<p class="eshopwidget">';
					if($showwhat=='items' || $showwhat=='both'){
						$eecho .=sprintf(_n('<span>%d</span> product in cart.','<span>%d</span> products in cart.',$eshopsize,'eshop'),$eshopsize);
					}
					if($showwhat=='qty' || $showwhat=='both'){
						if($showwhat=='both') $eecho.= '<br />';
						$eecho .=sprintf(_n('<span>%d</span> item in cart.','<span>%d</span> items in cart.',$eshopqty,'eshop'),$eshopqty);
					}
					$eecho.= '<br /><a class="cartlink" href="'.get_permalink($eshopoptions['cart']).'">'.__('View Cart','eshop').'</a>';
					$eecho .='<br /><a class="checkoutlink" href="'.get_permalink($eshopoptions['checkout']).'">'.__('Checkout','eshop').'</a>';
					$eecho .='</p>';
				}			

				echo $before_widget;
				echo $before_title.$title.$after_title;
				echo $eecho;
				echo $after_widget;
			}			
		}elseif($show!='no'){
			$eecho= '<div class="ajaxcart">'.$text.'</div><p><a class="cartlink" href="'.get_permalink($eshopoptions['cart']).'">'.__('View Cart','eshop').'</a>';
			$eecho .='<br /><a class="checkoutlink" href="'.get_permalink($eshopoptions['checkout']).'">'.__('Checkout','eshop').'</a></p>';
			echo $before_widget;
			echo $before_title.$title.$after_title;
			echo $eecho;
			echo $after_widget;
		}else{
			if($showwhat=='full'){
				echo $before_widget;
				//echo $before_title.$title.$after_title;
				echo '<div class="eshopcartwidget"><div class="ajaxcart"></div></div>';
				echo $after_widget;
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show'] = strip_tags( $new_instance['show'] );
		$instance['showwhat'] = strip_tags( $new_instance['showwhat'] );
		$instance['text'] = strip_tags( $new_instance['text'] );
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'show'=>'no', 'showwhat'=>'items', 'text'=>'') );
		$title = strip_tags($instance['title']);
		$show = $instance['show'];
		$showwhat = $instance['showwhat'];
		$text = $instance['text'];
		?>
		 <p>
		    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title);?>" />
		 </p>
		 <p>
		  	<label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Show when empty','eshop'); ?></label>
		  	<select id="<?php echo $this->get_field_id('show'); ?>" name="<?php echo $this->get_field_name('show'); ?>">
		  	<option value="yes"<?php selected( $show, 'yes' ); ?>><?php _e('Yes','eshop'); ?></option>
		  	<option value="no"<?php selected( $show, 'no' ); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
			<label for="<?php echo $this->get_field_id('showwhat'); ?>"><?php _e('What to show','eshop'); ?></label>
			<select id="<?php echo $this->get_field_id('showwhat'); ?>" name="<?php echo $this->get_field_name('showwhat'); ?>">
			<option value="items"<?php selected( $showwhat, 'items' ); ?>><?php _e('Total number of different products','eshop'); ?></option>
			<option value="qty"<?php selected( $showwhat, 'qty' ); ?>><?php _e('Total number of different items','eshop'); ?></option>
			<option value="both"<?php selected( $showwhat, 'both' ); ?>><?php _e('Both','eshop'); ?></option>
			<option value="full"<?php selected( $showwhat, 'full' ); ?>><?php _e('Full Cart','eshop'); ?></option>
			</select>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text to show when Cart is empty:','eshop'); ?></label>
		    <input type="text" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" value="<?php echo esc_attr($text);?>" />
		</p>
	<?php
	}
}

/* *********************************************
** Main eShop cart widget - new and improved **
*********************************************** */
class eshop_cart_widget extends WP_Widget {

	function eshop_cart_widget() {
		$widget_ops = array('classname' => 'eshop-widget eshopcarti_widget', 'description' => __('Displays eShop cart','eshop'));
		$this->WP_Widget('eshopwi_cart', __('eShop Cart (Improved)','eshop'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		global $blog_id,$eshopoptions;
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$show = apply_filters( 'widget_text', $instance['show'], $instance );
		$showwhat = apply_filters( 'widget_text', $instance['showwhat'], $instance );
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		$items = apply_filters( 'widget_text', $instance['items'], $instance );
		$qty = apply_filters( 'widget_text', $instance['qty'], $instance );
		$total = apply_filters( 'widget_text', $instance['total'], $instance );
		//$fc=apply_filters( 'widget_text', $instance['fc'], $instance );
		//$fcimg=apply_filters( 'widget_text', $instance['fcimg'], $instance );

		$currsymbol=$eshopoptions['currency_symbol'];
		if(isset($_SESSION['eshopcart'.$blog_id])){
			$eshopsize=0;
			$eshopqty=0;
			$thetotal=0;
			if(isset($_SESSION['eshopcart'.$blog_id])){
				$eshopsize=sizeof($_SESSION['eshopcart'.$blog_id]);
				
				foreach($_SESSION['eshopcart'.$blog_id] as $eshopdo=>$eshopwop){
					$eshopqty+=$eshopwop['qty'];
				}
				
				if(isset($_SESSION['final_price'.$blog_id])) $thetotal=$_SESSION['final_price'.$blog_id];
				
				$eshoptotal=sprintf( __('%1$s%2$s','eshop'), $currsymbol, number_format_i18n($_SESSION['final_price'.$blog_id],__('2','eshop')));
				if($showwhat=='full'){
					$eecho='<div class="eshopcartwidget"><div class="ajaxcart">'.display_cart($_SESSION['eshopcart'.$blog_id],false, $eshopoptions['checkout'],'widget').'';
					$eecho .='</div></div>';
				}else{
					$any=0;
					$eecho='<p class="eshopwidget">';
					if(isset($items) & $items=='1'){
						$eecho .=sprintf(_n('<span>%d</span> product in cart.','<span>%d</span> products in cart.',$eshopsize,'eshop'),$eshopsize);
						$any++;
					}
					if(isset($qty) & $qty=='1'){
						if($any>0) $eecho.= '<br />';
						$eecho .=sprintf(_n('<span>%d</span> item in cart.','<span>%d</span> items in cart.',$eshopqty,'eshop'),$eshopqty);
						$any++;
					}
					if(isset($total) & $total=='1'){
						if($any>0) $eecho.= '<br />';
						$eecho .=sprintf(__('<span>%s</span> cart total.','eshop'),$eshoptotal);
					}
					$eecho.= '<br /><a class="cartlink" href="'.get_permalink($eshopoptions['cart']).'">'.__('View Cart','eshop').'</a>';
					$eecho .='<br /><a class="checkoutlink" href="'.get_permalink($eshopoptions['checkout']).'">'.__('Checkout','eshop').'</a>';
					$eecho .='</p>';
				}			

				echo $before_widget;
				echo $before_title.$title.$after_title;
				echo $eecho;
				echo $after_widget;
			}			
		}elseif($show!='no'){
			$eecho='';
			if($showwhat=='full')
				$eecho .= '<div class="ajaxcart">';
			$eecho .= '<div class="eshopcartwidget"><p>'.$text.'</p><p><a class="cartlink" href="'.get_permalink($eshopoptions['cart']).'">'.__('View Cart','eshop').'</a>';
			$eecho .='<br /><a class="checkoutlink" href="'.get_permalink($eshopoptions['checkout']).'">'.__('Checkout','eshop').'</a></p></div>';
			if($showwhat=='full')
				$eecho .= '</div>';
			echo $before_widget;
			echo $before_title.$title.$after_title;
			echo $eecho;
			echo $after_widget;
		}else{
			if($showwhat=='full'){
				echo $before_widget;
				//echo $before_title.$title.$after_title;
				echo '<div class="eshopcartwidget"><div class="ajaxcart"></div></div>';
				echo $after_widget;
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show'] = strip_tags( $new_instance['show'] );
		$instance['showwhat'] = strip_tags( $new_instance['showwhat'] );
		
		$instance['qty']=$instance['items']=$instance['total'] = '';
		
		if(isset($new_instance['total']))
			$instance['total'] = strip_tags( $new_instance['total'] );
		if(isset($new_instance['items']))
			$instance['items'] = strip_tags( $new_instance['items'] );
		if(isset($new_instance['qty']))
			$instance['qty'] = strip_tags( $new_instance['qty'] );
			
		$instance['fc'] = strip_tags( $new_instance['fc'] );
		$instance['fcimg'] = strip_tags( $new_instance['fcimg'] );
		$instance['text'] = strip_tags( $new_instance['text'] );
		
		//need access elsewhere - restricts to one setting sadly.
		$eshopoptions = get_option('eshop_plugin_settings');
		$eshopoptions['widget_cart_type']=$instance['fc'];
		$eshopoptions['widget_cart_img']=$instance['fcimg'];
		update_option('eshop_plugin_settings',$eshopoptions);
		
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'show'=>'no', 'showwhat'=>'', 'text'=>'', 'items'=>'','qty'=>'','total'=>'','fc'=>'2','fcimg'=>'') );
		$title = strip_tags($instance['title']);
		$show = $instance['show'];
		$showwhat = $instance['showwhat'];
		$items = $instance['items'];
		$qty = $instance['qty'];
		$total = $instance['total'];
		$text = $instance['text'];
		$fc=$instance['fc'];
		$fcimg=$instance['fcimg'];
		?>
		 <p>
		    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title);?>" />
		 </p>
		 <p>
		 	<label for="<?php echo $this->get_field_id('showwhat'); ?>"><?php _e('What to show','eshop'); ?></label>
		 	<select id="<?php echo $this->get_field_id('showwhat'); ?>" name="<?php echo $this->get_field_name('showwhat'); ?>">
		 	<option value="both"<?php selected( $showwhat, 'both' ); ?>><?php _e('Just totals','eshop'); ?></option>
		 	<option value="full"<?php selected( $showwhat, 'full' ); ?>><?php _e('Full Cart','eshop'); ?></option>
			</select><br />
		  	<label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Show when empty','eshop'); ?></label>
		  	<select id="<?php echo $this->get_field_id('show'); ?>" name="<?php echo $this->get_field_name('show'); ?>">
		  	<option value="yes"<?php selected( $show, 'yes' ); ?>><?php _e('Yes','eshop'); ?></option>
		  	<option value="no"<?php selected( $show, 'no' ); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
		<p>Just Totals:<br />
			<input type="checkbox" value="1" <?php checked( $items, '1' ); ?> id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" /><label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Total Number of Items','eshop'); ?></label><br />
			<input type="checkbox" value="1" <?php checked( $qty, '1' ); ?> id="<?php echo $this->get_field_id('qty'); ?>" name="<?php echo $this->get_field_name('qty'); ?>" /><label for="<?php echo $this->get_field_id('qty'); ?>"><?php _e('Total Quantity of Items','eshop'); ?></label><br />
			<input type="checkbox" value="1" <?php checked( $total, '1' ); ?> id="<?php echo $this->get_field_id('total'); ?>" name="<?php echo $this->get_field_name('total'); ?>" /><label for="<?php echo $this->get_field_id('total'); ?>"><?php _e('Cart Total','eshop'); ?></label><br />
		</p>
		<p><label for="<?php echo $this->get_field_id('fc'); ?>">Full Cart</label><br />
			<select id="<?php echo $this->get_field_id('fc'); ?>" name="<?php echo $this->get_field_name('fc'); ?>">
				<option value="0" <?php selected( $fc, '0' ); ?>><?php _e('Image + Text','eshop'); ?></option>
				<option value="1" <?php selected( $fc, '1' ); ?>><?php _e('Image','eshop'); ?></option>
				<option value="2" <?php selected( $fc, '2' ); ?>><?php _e('Text','eshop'); ?></option>
			</select><br />
			<label for="<?php echo $this->get_field_id('fcimg'); ?>"><?php _e('% size image to display','eshop'); ?></label>
			   <input type="text" id="<?php echo $this->get_field_id('fcimg'); ?>" name="<?php echo $this->get_field_name('fcimg'); ?>" value="<?php echo esc_attr($fcimg);?>" />

		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text to show when Cart is empty:','eshop'); ?></label>
		    <input type="text" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" value="<?php echo esc_attr($text);?>" />
		</p>
	<?php
	}
}

/* *******************************
** eShop payment options widget **
******************************** */
class eshop_pay_widget extends WP_Widget {

	function eshop_pay_widget() {
		$widget_ops = array('classname' => 'eshop-widget eshoppay_widget', 'description' => __('Displays accepted payment logos','eshop'));
		$this->WP_Widget('eshopw_pay', __('eShop Payments Accepted','eshop'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		global $blog_id,$eshopoptions;
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		echo $before_title.$title.$after_title;
		if(is_array($eshopoptions['method'])){
			$i=1;
			$replace = array(".");
			$eshopfiles=eshop_files_directory();
			echo "\n".'<ul class="eshoppaywidget">'."\n";
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
				$dims='';
				if(file_exists($eshopmerchantimgpath))
					$dims=getimagesize($eshopmerchantimgpath);
				echo '<li><img src="'.$eshopmerchantimgurl.'" '.$dims[3].' alt="'.__('Pay via','eshop').' '.$eshoppayment_text.'" title="'.__('Pay via','eshop').' '.$eshoppayment_text.'" /></li>'."\n";
				$i++;
			}
			echo "</ul>\n";
		}
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		?>
		 <p>
		    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title);?>" />
		 </p>
		 
	<?php
	}
}

/* **************************
**  eShop products widget  **
*************************** */
class eshop_products_widget extends WP_Widget {

	function eshop_products_widget() {
		$widget_ops = array('classname' => 'eshop-widget eshopproducts_widget', 'description' => __('Displays products','eshop'));
		$this->WP_Widget('eshopw_prod', __('eShop Products','eshop'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		global $blog_id;
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$show_size = $instance['show_size'];
		$show_id = $instance['show_id'];
		$show_type = $instance['show_type'];
		$show_what = $instance['show_what'];
		$order_by = $instance['order_by'];
		$show_amts = $instance['show_amts'];
		if($show_type==1) $stype='yes';
		else $stype='no';
		echo $before_widget;
		echo $before_title.$title.$after_title;
		switch($show_what){
			case '1'://featured
				echo eshopw_w_featured(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size,'sortby'=>$order_by));
				break;
			case '2'://new
				echo eshopw_list_new(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size));
				break;
			case '3'://random
				echo eshopw_list_random(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size));
				break;
			case '4'://show specific products
				echo eshopw_show_product(array('id'=>$show_id,'images'=>$stype,'size'=>$show_size));
				break;
			case '5'://show best sellers
				echo eshopw_best_sellers(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size));
				break;
			case '6'://show catname
				echo eshopw_list_cat_tags(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size,'type'=>'category_name','id'=>$show_id,'sortby'=>$order_by));
				break;
			case '7'://show cat id
				echo eshopw_list_cat_tags(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size,'type'=>'cat','id'=>$show_id,'sortby'=>$order_by));
				break;
			case '8'://show tags
				echo eshopw_list_cat_tags(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size,'type'=>'tag','id'=>$show_id,'sortby'=>$order_by));
				break;
			case '9'://show tag id
				echo eshopw_list_cat_tags(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size,'type'=>'tag_id','id'=>$show_id,'sortby'=>$order_by));
				break;
			case '10'://sale
				echo eshopw_w_sale(array('images'=>$stype,'show'=>$show_amts,'size'=>$show_size,'sortby'=>$order_by));
				break;
		}
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show_size'] = strip_tags($new_instance['show_size']);
		$instance['show_id'] = $new_instance['show_id'];
		$instance['show_type'] = strip_tags($new_instance['show_type']);
		$instance['show_what'] = strip_tags($new_instance['show_what']);
		$instance['order_by'] = strip_tags($new_instance['order_by']);
		$instance['show_amts'] = strip_tags($new_instance['show_amts']);
		if(!is_numeric($instance['show_size'])) $instance['show_size']='';
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '','show_size'=>'','show_id'=>'','show_type'=>'','show_what'=>'','order_by'=>'','show_amts'=>'',) );
		$title = strip_tags($instance['title']);
		$show_size=$instance['show_size'];
		$show_id=$instance['show_id'];
		$show_type=$instance['show_type'];
		$show_what=$instance['show_what'];
		$order_by=$instance['order_by'];
		$show_amts=$instance['show_amts'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title','eshop'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p><label for="<?php echo $this->get_field_id('show_type'); ?>"><?php _e('Images or text','eshop'); ?></label>
			<select id="<?php echo $this->get_field_id('show_type'); ?>" name="<?php echo $this->get_field_name('show_type'); ?>">
			<option value="1"<?php selected( $show_type, '1' ); ?>><?php _e('Images','eshop'); ?></option>
			<option value="2"<?php selected( $show_type, '2' ); ?>><?php _e('Text','eshop'); ?></option>
		</select></p>
		<p><label for="<?php echo $this->get_field_id('show_size'); ?>"><?php _e('&#37; size image to display','eshop'); ?></label>
			<input size="3" maxlength="3" id="<?php echo $this->get_field_id('show_size'); ?>" name="<?php echo $this->get_field_name('show_size'); ?>" type="text" value="<?php echo $show_size; ?>" />
		</p>
		<p><label for="<?php echo $this->get_field_id('show_what'); ?>"><?php _e('What to show','eshop'); ?></label>
			<select id="<?php echo $this->get_field_id('show_what'); ?>" name="<?php echo $this->get_field_name('show_what'); ?>">
			<option value="1"<?php selected( $show_what, '1' ); ?>><?php _e('Featured','eshop'); ?></option>
			<option value="2"<?php selected( $show_what, '2' ); ?>><?php _e('New','eshop'); ?></option>
			<option value="3"<?php selected( $show_what, '3' ); ?>><?php _e('Random','eshop'); ?></option>
			<option value="4"<?php selected( $show_what, '4' ); ?>><?php _e('Specific products','eshop'); ?></option>
			<option value="5"<?php selected( $show_what, '5' ); ?>><?php _e('Best Sellers','eshop'); ?></option>
			<option value="6"<?php selected( $show_what, '6' ); ?>><?php _e('Category names','eshop'); ?></option>
			<option value="7"<?php selected( $show_what, '7' ); ?>><?php _e('Category ID','eshop'); ?></option>
			<option value="8"<?php selected( $show_what, '8' ); ?>><?php _e('Tags','eshop'); ?></option>
			<option value="9"<?php selected( $show_what, '9' ); ?>><?php _e('Tag ID','eshop'); ?></option>
			<option value="10"<?php selected( $show_what, '10' ); ?>><?php _e('Sale','eshop'); ?></option>
		</select></p>
		<p><label for="<?php echo $this->get_field_id('order_by'); ?>"><?php _e('Featured/Sale Order by','eshop'); ?></label>
				<select id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>">
				<option value="1"<?php selected( $order_by, '1' ); ?>><?php _e('Title','eshop'); ?></option>
				<option value="2"<?php selected( $order_by, '2' ); ?>><?php _e('Menu Order','eshop'); ?></option>
				<option value="3"<?php selected( $order_by, '3' ); ?>><?php _e('Date Ascending','eshop'); ?></option>
				<option value="4"<?php selected( $order_by, '4' ); ?>><?php _e('Date Descending','eshop'); ?></option>
		</select></p>
		<p><label for="<?php echo $this->get_field_id('show_amts'); ?>"><?php _e('How many to show','eshop'); ?></label>
			<select id="<?php echo $this->get_field_id('show_amts'); ?>" name="<?php echo $this->get_field_name('show_amts'); ?>">
			<?php
			for($i=1;$i<=10;$i++){
			?>
				<option value="<?php echo $i; ?>"<?php selected( $show_amts, $i ); ?>><?php echo $i; ?></option>
			<?php
			}
			?>
		</select></p>
		<p><label for="<?php echo $this->get_field_id('show_id'); ?>"><?php _e('Page, Post, Tag &amp; Category IDs or Tag/Category names - comma separated','eshop'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('show_id'); ?>" name="<?php echo $this->get_field_name('show_id'); ?>" type="text" value="<?php echo $show_id; ?>" />
		</p>
	<?php
	}
}

/********************************************************************** */
/* functions for widgets above - similar but not the same as shortcodes */
/* ******************************************************************** */
function eshopw_list_new($atts){
	global $wpdb, $post;
	extract(shortcode_atts(array('class'=>'eshopw_new','images'=>'no','show'=>'6','size'=>''), $atts));
	$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $wpdb->postmeta.meta_key='_eshop_stock' AND $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' order by post_date DESC limit $show");
	if($pages) {
		if($images=='no'){
			$echo = eshopw_listpages($pages,$class);
		}else{
			$class='eshopw_panels';
			$echo = eshopw_listpanels($pages,$class,$size);
		}
		return $echo;
	} 
	return;
} 
function eshopw_best_sellers($atts){
	global $wpdb, $post;
	$stktable=$wpdb->prefix.'eshop_stock';
	extract(shortcode_atts(array('class'=>'eshopw_best','images'=>'no','show'=>'6','size'=>''), $atts));
	$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* 
	from $wpdb->postmeta,$wpdb->posts, $stktable as stk
	WHERE $wpdb->postmeta.meta_key='_eshop_stock' 
	AND $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' AND stk.post_id=$wpdb->posts.ID
	order by stk.purchases DESC limit $show");
	if($pages) {
		if($images=='no'){
			$echo = eshopw_listpages($pages,$class);
		}else{
			$class='eshopw_panels';
			$echo = eshopw_listpanels($pages,$class,$size);
		}
		return $echo;
	} 
	return;
} 

function eshopw_w_featured($atts){
	return eshopw_list_featured_sale($atts);
}
function eshopw_w_sale($atts){
	return eshopw_list_featured_sale($atts, 'sale');
}


function eshopw_list_featured_sale($atts, $type='featured'){
	global $wpdb, $post;
	$paged=$post;
	extract(shortcode_atts(array('class'=>'eshopw_'.$type,'images'=>'no','show'=>'6','size'=>'','sortby'=>'1'), $atts));

	switch ($sortby){
		case '2'://menu order
			$orderby='p.menu_order';
			$order= 'ASC';
			break;
		case '3'://date asc
			$orderby='p.post_date';
			$order= 'ASC';
			break;
		case '4'://date desc
			$orderby='p.post_date';
			$order= 'DESC';
			break;
		case '1'://title
		default:
			$orderby='p.post_title';
			$order= 'ASC';
			break;
	}
	$pages=$wpdb->get_results("SELECT p.* from $wpdb->postmeta as pm,$wpdb->posts as p WHERE pm.meta_key='_eshop_".$type."' AND p.post_status='publish' AND p.ID=pm.post_id ORDER BY $orderby $order LIMIT $show");
	if($pages) {
		if($images=='no'){
			$echo = eshopw_listpages($pages,$class);
		}else{
			$class='eshopw_panels';
			$echo = eshopw_listpanels($pages,$class,$size);
		}
		$post=$paged;
		return $echo;
	} 
	$post=$paged;
	return;
}
function eshopw_list_random($atts){
	global $wpdb, $post;
	$paged=$post;
	extract(shortcode_atts(array('class'=>'eshopw_random','images'=>'no','show'=>'6','size'=>''), $atts));
	$pages=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $wpdb->postmeta.meta_key='_eshop_stock' AND $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' order by rand() limit $show");
	if($pages) {
		if($images=='no'){
			$echo = eshopw_listpages($pages,$class);
		}else{
			$class='eshopw_panels';
			$echo = eshopw_listpanels($pages,$class,$size);
		}
		$post=$paged;
		return $echo;
	}
	$post=$paged;
	return;
}
function eshopw_show_product($atts){
	global $wpdb, $post;
	extract(shortcode_atts(array('id'=>'0','class'=>'eshopw_prod','images'=>'no','size'=>''), $atts));
	if($id!=0){
		$epages=array();
		$theids = explode(",", $id);
		foreach($theids as $thisid){
			$thispage=$wpdb->get_results("SELECT $wpdb->postmeta.post_id, $wpdb->posts.* from $wpdb->postmeta,$wpdb->posts WHERE $wpdb->posts.ID=$wpdb->postmeta.post_id AND $wpdb->posts.post_status='publish' AND $wpdb->posts.ID='$thisid'");
			if(sizeof($thispage)>0)//only add if it exists
				array_push($epages,$thispage['0']);
		}
		if(sizeof($epages)>0){//if nothing found - don't do this
			if($images=='no'){
				$echo = eshopw_listpages($epages,$class);
			}else{
				$echo = eshopw_listpanels($epages,$class,$size);
			}
			return $echo;
		}
	}
	return;
}
function eshopw_list_cat_tags($atts){
	global $wpdb, $post,$wp_query;
	extract(shortcode_atts(array('class'=>'eshopwcats','images'=>'no','sortby'=>'1','show'=>'6','order'=>'ASC','size'=>'','id'=>'','type'=>''), $atts));
	$allowedsort=array('post_date','post_title','menu_order');
	$allowedorder=array('ASC','DESC');
	if(!in_array($sortby,$allowedsort)) 
		$sortby='post_title';
	switch ($sortby){
		case '2'://menu order
			$orderby='menu_order';
			$order= 'ASC';
			break;
		case '3'://date asc
			$orderby='post_date';
			$order= 'ASC';
			break;
		case '4'://date desc
			$orderby='post_date';
			$order= 'DESC';
			break;
		case '1'://title
		default:
			$orderby='post_title';
			$order= 'ASC';
			break;
	}
	if(!in_array($order,$allowedorder)) 
		$order='ASC';
	$array=array('post','page');
	$array=apply_filters('eshop_post_types',$array);
	$args = array(
	'post_type' => $array,
	'post_status' => null,
	$type => $id, 
	'meta_key'=>'_eshop_product',
	'orderby'=> $orderby,
	'posts_per_page' => $show
	); 
	$pages = query_posts($args);
	wp_reset_query();
	if($pages) {
		if($images=='no'){
			$echo = eshopw_listpages($pages,$class);
		}else{
			if($class=='eshopsubpages') $class='eshopwpanels';
			$echo = eshopw_listpanels($pages,$class,$size);
		}
		return $echo;
	} 
	return;
}
function eshopw_listpages($subpages,$eshopclass){
	global $wpdb, $post,$eshopoptions;
	$paged=$post;
	$echo='';
	$echo .='<ul class="'.$eshopclass.'">';
	foreach ($subpages as $post) {
		setup_postdata($post);
		$xclass='<li>';
		if(isset($eshopoptions['sale']) && $eshopoptions['sale']=='yes'){
			$esale=get_post_meta( $post->ID, '_eshop_sale',true );
			if($esale=='yes')
				$xclass='<li class="sale"><strong class="onsale"><span>'.__('On Sale','eshop').'</span></strong>';
		}
		$echo .= $xclass;
		$echo .= '<a class="itemref" href="'.get_permalink($post->ID).'">'.apply_filters("the_title",$post->post_title, $post->ID).'</a></li>';
	}
	$echo .= '</ul>';
	$post=$paged;
	return $echo;
}

function eshopw_listpanels($subpages,$eshopclass,$size){
	global $wpdb, $post,$eshopoptions;
	$paged=$post;
	$post='';
	$echo='';
	$echo .='<ul class="'.$eshopclass.'">';
	foreach ($subpages as $post) {
		setup_postdata($post);
		$xclass='<li>';
		if(isset($eshopoptions['sale']) && $eshopoptions['sale']=='yes'){
			$esale=get_post_meta( $post->ID, '_eshop_sale',true );
			if($esale=='yes')
				$xclass='<li class="sale"><strong class="onsale"><span>'.__('On Sale','eshop').'</span></strong>';
		}
		$echo .= $xclass;
		$w=apply_filters('eshop_thumbnail_size_w',get_option('thumbnail_size_w'));
		$h=apply_filters('eshop_thumbnail_size_h',get_option('thumbnail_size_h'));
		if($size!=''){
			$w=round(($w*$size)/100);
			$h=round(($h*$size)/100);
		}
		if (has_post_thumbnail( $post->ID ) ) {
			$echo .='<a class="itemref" href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail( $post->ID, array($w, $h)).'</a></li>'."\n";
		}else{
			$eimage=eshop_files_directory();
			$eshopnoimage=apply_filters('eshop_no_image',$eimage['1'].'noimage.png');
			$echo .='<a class="itemref" href="'.get_permalink($post->ID).'"><img src="'.$eshopnoimage.'" height="'.$h.'" width="'.$w.'" alt="" /></a></li>'."\n";
		}
	}
	$echo .= '</ul>';
	$post=$paged;
	return $echo;
}
/* product serach widget */

class eshop_search_widget extends WP_Widget {

	function eshop_search_widget() {
		$widget_ops = array('classname' => 'eshop-widget eshop_search_widget', 'description' => __('Displays a product search, optional link to an index page, and a random wiki page link','eshop'));
		$this->WP_Widget('eshop_search_widget', __('eShop Product Search','eshop'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		$output='';
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$random = $instance['random'];
		$search = $instance['search'];
		$find = $instance['find'];
		if($search=='1'){
			$sq=' ';
			if(get_search_query()!='')
				$sq=get_search_query();
			$output.='
			    <form id="eshopsearchform" method="get" action="'.get_bloginfo('url').'">
				<div>
					<input type="text" name="s" id="eshopws" size="20" value="'.$sq.'" />
					<input type="submit" value="'.__('Find','eshop').'" />
					<input type="hidden" name="eshopsearch" value="'.$find.'" />
				</div>
				</form>';
			
		}
		if($random!=''){
			$output.='<ul>';
			$eswr=add_query_arg('eshoprandom','',get_bloginfo('url'));
			$output.='<li><a href="'.$eswr.'">'.__('Random product','eshop').'</a></li>';
			$output.='</ul>';
		}
		
		echo $before_widget;
		echo $before_title.$title.$after_title;
		echo $output;
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['random'] = strip_tags( $new_instance['random'] );
		$instance['search'] = strip_tags( $new_instance['search'] );
		$instance['find'] = strip_tags( $new_instance['find'] );
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '','random'=>'' , 'search'=>'', 'find' => '') );
		$title = strip_tags($instance['title']);
		$random = $instance['random'];
		$search = $instance['search'];
		$find = $instance['find'];

		?>
		 <p>
		    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title);?>" />
		 </p>
		 <p>
		  	<label for="<?php echo $this->get_field_id('search'); ?>"><?php _e('Show search form','eshop'); ?></label>
		  	<select id="<?php echo $this->get_field_id('search'); ?>" name="<?php echo $this->get_field_name('search'); ?>">
		  	<option value="1"<?php selected( $search, '1' ); ?>><?php _e('Yes','eshop'); ?></option>
		  	<option value=""<?php selected( $search, '' ); ?>><?php _e('No','eshop'); ?></option>
			</select><br />
			
			<label for="<?php echo $this->get_field_id('find'); ?>"><?php _e('Find','eshop'); ?></label>
			<select id="<?php echo $this->get_field_id('find'); ?>" name="<?php echo $this->get_field_name('find'); ?>">
			<option value="all"<?php selected( $find, 'all' ); ?>><?php _e('All Products','eshop'); ?></option>
			<option value="instock"<?php selected( $find, 'instock' ); ?>><?php _e('Only Products in Stock','eshop'); ?></option>
			</select><br />
			
			<label for="<?php echo $this->get_field_id('random'); ?>"><?php _e('Show random product link','eshop'); ?></label>
			<select id="<?php echo $this->get_field_id('random'); ?>" name="<?php echo $this->get_field_name('random'); ?>">
			<option value="1"<?php selected( $random, '1' ); ?>><?php _e('Yes','eshop'); ?></option>
			<option value=""<?php selected( $random, '' ); ?>><?php _e('No','eshop'); ?></option>
			</select>			
		</p>
	<?php
	}
}
?>