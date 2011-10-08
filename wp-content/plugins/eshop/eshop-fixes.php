<?php
//These are a collections of functions to allow ease of removing any conflicting plugins
//not currently used. but left for backwards compatability
function eshoplistfix(){
	//this function is for removing conflicting actions from the 2 listing pages
	//add as necessary.
	//<!--eshop_list_featured--> && <!--eshop_list_subpages-->
	remove_action('the_content', 'akst_add_share_link_to_content');//share this plugin
}
function eshopshipfix(){
	//specific for the shipping page.
}
function eshopcancelfix(){
	//specific for the cancel page

}
function eshopsuccessfix(){
	// specific for the success page

}
function eshopcartfix(){
	//specific for the cart page

}
function eshopcheckoutfix(){
	//specific for the checkout page

}
function eshopdownloadsfix(){
	//specific for the downloads pages

}
?>