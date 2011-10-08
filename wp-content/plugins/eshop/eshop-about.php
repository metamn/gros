<?php
if ('eshop-about.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
    
$eshopoptions = get_option('eshop_plugin_settings');

/*
See eshop.php for information and license terms
*/
if (file_exists(ABSPATH . 'wp-includes/l10n.php')) {
    require_once(ABSPATH . 'wp-includes/l10n.php');
}
else {
    require_once(ABSPATH . 'wp-includes/wp-l10n.php');
}
//so that user is no longer auto redirected to this page.
if('yes'==$eshopoptions['first_time']){
	echo'<div id="message" class="updated fade"><p>'.__('This page is normally accessible via the <strong>About</strong> link.','eshop').'</p></div>'."\n";
}
$eshopoptions['first_time']='no';
update_option('eshop_plugin_settings',$eshopoptions);
?>
<div id="eshoppage">
<div class="wrap">
 <div id="eshopicon" class="icon32"></div><h2>eShop About &amp; Help</h2>
  <?php eshop_admin_mode(); ?>
 <h3>About</h3>
  <p>eShop is simple accessible shopping cart plugin for WordPress.</p>
  <h4>Sections</h4>
  <ul>
  	<li><a href="#setup">eShop Setup Instructions</a></li>
  	<li><a href="#ehelp">eShop Help</a></li>
  	<li><a href="#donate">eShop Donations</a></li>
	<li><a href="#cred">eShop Credits</a></li>
  </ul>
</div>
<div class="wrap">

 <h3 id="donate">eShop Donations</h3>
 <div class="eshopinfo stuffbox">
 <p style="text-align:center">Feeling generous? then please dig deep and donate as much as you can afford!</p>
 <form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="margin:0 auto; width:120px;">
 <input type="hidden" name="cmd" value="_xclick" />
 <input type="hidden" name="business" value="paypal@blackwidows.co.uk" />
 <input type="hidden" name="item_name" value="eShop donation" />
 <input type="hidden" name="buyer_credit_promo_code" value="" />
 <input type="hidden" name="buyer_credit_product_category" value="" />
 <input type="hidden" name="buyer_credit_shipping_method" value="" />
 <input type="hidden" name="buyer_credit_user_address_change" value="" />
 <input type="hidden" name="no_shipping" value="0" />
 <input type="hidden" name="no_note" value="1" />
 <input type="hidden" name="currency_code" value="GBP" />
 <input type="hidden" name="tax" value="0" />
 <input type="hidden" name="lc" value="GB" />
 <input type="hidden" name="bn" value="PP-DonationsBF" />
 <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" name="submit" alt="Donate via paypal" />
 <img src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" alt="" />
</form>

  <p class="thanks">Thank you very much!</p>
  </div>
</div>
<div class="wrap">
 <h3 id="setup">eShop Setup Instructions</h3>
  <p>Once the plugin is activated, it will automatically install various tables into the database for use.</p>
  <h4>Installation</h4>
	<p>Upload to your Wordpress plugin directory and activate.</p>

	<p>The plugin automatically creates 6 pages for you, all of which are editable.</p>

	<ul>
	<li><strong>Shopping Cart:</strong> 
	this will automatically display the shopping cart when someone adds a product to their cart.
	</li>
	<li><strong>Checkout:</strong> 
	for when the customer wants to pay for their purchases.
	</li>
	<li><strong>Thank you for your order:</strong> 
	after a successful payment at paypal the customer, if they follow the link from paypal, will return to this page.
	</li>
	<li><strong>Cancelled Order:</strong> 
	if the customer cancels the order at payapl, and they follow the link from paypal, they will return to this page.
	</li>
	<li><strong>Downloads:</strong> this is the page a user will be directed to after they purchase a downloadable product.</li>
	<li><strong>Shipping rates:</strong> 
	because of the different possible shipping rates the plugin allows, this page automatically shows the table of shipping rates for you.
	</li>
	</ul>
	<h5>Admin Pages</h5>
	<p>eShop create a full section containing the majority of admin pages, along with 3 additional ones located as mentioned below.</p>
	<ul>
	<li><strong>Appearance > eShop</strong>:  to allow you to alter the included CSS.</li>
	<li><strong>Settings > eShop</strong>: for various settings within the plugin.</li>
	<li><strong>Plugins > eShop Uninstall</strong>: should you decide to delete eShop entirely <em>(which I of course hope you never need!)</em></li>
	</ul>
	<h4>Product Entry</h4>
	<p>Simply edit a page/post and enter details into the Product Entry section on that page.</p>
</div>
<div class="wrap">
<h3 id="ehelp">Help</h3>
	<p>The full help file is now available via: <a href="http://quirm.net/wiki/eshop/">http://quirm.net/wiki/eshop/</a> For easy reference that link is included in the Help tab on eShop pages.</p>
<h4>Customisation</h4>
	<p class="eshopinfo stuffbox"><a href="http://blackwidows.co.uk/">Black Widow Web Design Ltd</a> provide full customisation service for eShop from simple style changes to complete theme development and additional plugins. Please use the <a href="http://blackwidows.co.uk/contact/enquiry2.php">Contact Form</a> to ask for a quote.</p>
</div>
<div class="wrap">
 <h3 id="cred">eShop Credits</h3>
  <p>Created by <a href="http://cms.elfden.co.uk/">Rich Pedley</a> based on ideas and requirements from <a href="http://green-beast.com/">Mike Cherim</a>.</p>
  <h4>Credits</h4>
  <p>During the creation of these scripts I have utilised scripts from elsewhere, although many of them were heavily adpated to suit. So my thanks go to:</p>
  <ul>
  <li><a href="http://alexking.org/">Alex King</a> - for removal of some default Wordpress settings.</li>
  <li><a href="http://rhymedcode.net">Joshua Sigar</a> - for the product entry idea.</li>
  <li><a href="http://www.internetofficer.com/">InternetOfficer SPRL</a> - adding in the pages to the default search.</li>
  <li><a href="http://www.coffee2code.com">Scott Reilly</a> - inspiration for the add to cart form.</li>
  <li><a href="http://justintadlock.com">Justin Tadlock</a> - for the sub page listing.</li>
  <li><a href="http://www.vtardia.com">Vito Tardia</a> - utilised this script to enhance the sub page listing.</li>
  <li><a href="http://www.mealldubh.org">John McCreesh</a> - for the additional page lisitng enhancement.</li>
  <li><a href="http://green-beast.com/">Mike Cherim</a> - design and layout, plus the style and templates pages.</li>
  <li><a href="http://www.blackwidows.co.uk/">Mel Pedley</a> - design and layout, and proof reading at various stages.</li>
  <li><a href="http://4visions.nl/">Allard van Hagen</a> (aka RavanH) - help with Wordpress MU coding, general bug hunting and extra Paypal help.</li>
  <li><a href="mailto:integrate@mokejimai.lt">Markas Krasovskis</a> - for coding the webtopay merchant gateway api.</li>
  <li><a href="http://donelliott.us/">Don Elliott Design</a> - donating to have the eProcessingNetwork merchant gateway added.</li>
  <li><a href="http://combobreaker.com/">Dan Remollino</a> - donating to have option sets added.</li>
  <li><a href="http://www.grizzdesign.nl/">Timo Tijhof</a> - scripting the iDeal Lite payment gateway.</li>
  <li><em>Jos&#233; Mar&#237;a Sancho de &#193;vila</em> - donating for the product reference page updates.</li>
  <li><a href="http://www.Simon-Townshend.com">Simon Townshend</a> - donating to have the ogone payment gateway added.</li>
  <li><a href="http://www.conormaxwell.com/">Connor Maxwell</a> - thank you.</li>
  <li><a href="http://www.code-styling.de/">Heiko Rabe</a> - for the initial class behind the new met boxes on settings pages</li>
  </ul>
</div>
</div>
