=== eShop ===
Contributors: elfin
Donate link: http://quirm.net/download/
Tags: eshop, ecommerce, shop, store, estore, stock control, cart, e-commerce, wpmu, multisite, authorize.net, paypal, payson, eProcessingNetwork, Webtopay, ideal, cash, bank, tax
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 6.2.10

An accessible Shopping Cart plugin.

== Description ==

eShop is an accessible shopping cart plugin for WordPress, packed with various features. Including:

* Utilises WordPress pages or posts, and compatible with custom post types, to create products
* Customers can sign up to your site (settable option)
* Various methods available for listing products
* Products can have multiple options
* Upload downloadable products
* Basic Statistics
* Download sales data
* Various shipping options, including by weight.
* Admin has access to an Order handling section
* Automatic emails on successful purchase
* User configurable email templates.
* Configurable Out of Stock message.
* Basic Stock Control
* Google Base Data creation
* Uninstall available within the plugin
* Various discount options
* WPMU, Multisite compatible.
* Merchant gateways:Authorize.net, Paypal, Payson, eProcessingNetwork, Webtopay, iDEAL and Cash/Cheque!
* Sales tax!
* Now compatible with WP Affiliate for affiliates - see [wiki](http://quirm.net/wiki/eshop/).
* able to be used as a product catalogue with no sales.
* and much much more


Documentation is available via [Quirm.net](http://quirm.net/wiki/eshop/)

== Screenshots ==

Videos and screenshots available on [Quirm.net](http://quirm.net/)

== Changelog == 

Version 6.2.10

* *fixed* 	small issue with Paypal.

Version 6.2.9

* *NEW* 	CSS file updated and tweaked for new users.
* *fixed* 	Several minor XSS - as advised by High-Tech Bridge SA Security Research Lab
* *fixed*  	sort by sku issue on products and base page.
* *fixed*  	small error with authorize.net 
* *fixed* 	admin bar showing eShop status for non admins.
* *fixed* 	minor translation issues.
* *fixed* 	issue on admin Shipping page that caused the countries to go missing
* *fixed* 	Option sets prices not being used correctly for discount purposes
* *added* 	CSS classes
* *added*	Filter added for Ogone location: ogone-location
* *changed* 	paypal.class renamed to avoid conflicts
* *changed* 	Turkish Lira changed from TL to TRY


Version 6.2.8

* *fixed*  	Activation issue, rogue space :(

Version 6.2.7

* *fixed* 	tax not being applied when shipping fields were hidden
* *added* 	signup field can now be a required field - add to the required fields using signup as the value in the filter eshopCheckoutReqd

Version 6.2.6

* *fixed* 	cart issue with deleting an item in error
* *fixed* 	small fix for authorize.net when using discount codes.
* *fixed* 	bug on checkout when not using tax.
* *amended* 	When using Shipping method 4, the first option is now automatically the default. the filer eshop_default_shipping can amend which one is the default.
* *added* 	a filter eshop_use_cookie which can be set to false if sites are having an issue with the checkout after first purchase

Version 6.2.5

* *fixed* 	now possible set an overall tax rate if using the download only form (may be extended in the future)
* *fixed* 	tax notification in emails
* *fixed* 	missing translation string for single price

Version 6.2.4

* *fixed* 	Minor bug on merchant gateways page.
* *fixed* 	bug on dashboard for non admins
* *fixed* 	bug with tax rates - possibly fixed, feedback appreciated
* *fixed* 	small change to authorize.net to try and fix a bug, unable to test, feedback appreciated.
* *added*	CSS hook to shipping row in cart

Version 6.2.3

* *fixed* 	issues with shipping method 4 on checkout page.
* *fixed/added* shipping method 4, extra details for shortcode and checkout page.
* *fixed* 	date of order in customer email
* *fixed/added* eshop_rtn_order_details now has an array of post_ids for sold products	
* *fixed/added* grand total to email if tax is added.
* *fixed* 	minor issue with alpha shortcode

Version 6.2.2

* *fixed* 	Tax not calculating correctly for shipping methods 1-3 - __if you are using Sales Tax, please upgrade asap__
* *fixed* 	Shipping method 4, not saving correct shipping on error in checkout
* *fixed* 	fixed eshop details, class name appeared twice + missing information for shipping by weight
* *fixed* 	missing information for shipping by weight
* *fixed* 	apply_filters for the_title 
* *fixed* 	error in add cart
* *fixed* 	scalar value as an array error
* *fixed* 	product widget issue not showing correct amount.
* *fixed* 	checkout nor picking up saved State/County/Province
* *fixed* 	minor error with new dashboard stats
* *fixed* 	dates now hopefully picking up correct timezone.
* *updated* 	CSS when using the image button for the add to cart.
* *updated* 	Dashboard widgets.
* *updated* 	Link to order in admin email
* *added*  	Link to user on the orders page (if they signed up/signed in at time of order)
* *added* 	extras to the downloads shortcode - ability to set content and image type icons(via images='yes') 
* *added* 	filter to discounts.
* *added* 	Hide Appearance > eShop page if theme has an eshop.css

Version 6.2.1

* *fixed* 	some orders were not saved to the database correctly, this fixes it, but is not backwards compatible. (only affect those whose table prefix was not the default)
* *fixed* 	issue with discount codes on the checkout
* *fixed* 	issue with checkout and fields losing their value on error
* *fixed* 	checkout required fields for shipping method 4
* *added* 	missing spans to checkout fields for styling

Version 6.2.0

* *WARNING* 	__Back up your database before upgrading__
* *NEW* 	__Sales Tax__ can now be added. (settable per product option after 'tax bands' has been entered).
* *NEW* 	Extra Stats.
* *NEW* 	Downloads can now have 'collections'.
* *NEW* 	Sale Prices for main options, shortcodes adapted to take price = yes, sale or both.
* *FIXED/Added* Secondary address box for Paypal, finally allowing non main account email addresses to be used!
* *added* 	generic eshop-widget class to eShop widgets
* *added* 	filter for the eshop_files_directory
* *added* 	check on checkout page, if memebersonly is set as an attribute, then you can use this format to display a message: [eshop_show_checkout membersonly='yes']please show me[/eshop_show_checkout]
* *added* 	number of decimals is now translatable.
* *added* 	extra CSS hooks to checkout confirmation page.
* *added* 	action eshop_sold_product which is sent the post id of the product that was sold.
* *added* 	Shipping by weight now allows you to choose between states/counties/province and countries per mode, and add in a max weight for each.
* *added* 	missing strings for translations
* *added* 	classes for styling in various places
* *fixed* 	small bug with listing on the downloads page.
* *fixed* 	slashes issue with option sets
* *fixed* 	eshop_welcome shortcode now picks up loged in users display name if set.
* *fixed* 	slashes appearing in emails.(hopefully)
* *fixed* 	eshop_details shortcode now functions correctly.
* *fixed* 	checkout losing chosen State/County/Province, props VK.
* *fixed* 	many small bugs.
* *changed*	renaming of eshop.css to eshop-admin.css to avoid confusion, and many styles tweaked.
* *changed*	About page changed.
* *changed* 	Discount Codes are no longer case sensitive
* *changed* 	removed _ from filenames and replaced with -, easier for developers.
* *changed* 	eShop stats are now on the main WP Dashboard, only available for eShop admins.
* *Note* 	after upgrading you may need to refresh the page due to the change above.

__Note__ This version has been tested with Paypal, Cash, Bank, Webtopay and Authorize.net merchant gateways, and all seemed to work OK in test mode. The other gateways have not had a full test, but should work without issue.

Version 6.1.0

* *Development Version only*

Version 6.0.2

* *fixed* 	issue with Shipping Method's clearing all values.
* *fixed* 	errors when allowing user to sign up to your site from the checkout.
* *added* 	version 6 broke the renaming of shipping zones, a new method has therefore been created, see the [wiki](http://quirm.net/wiki/eshop/changing-displayed-text/#Renaming-Shipping-Zones)

Version 6.0.1

* *added/fixed* 	eship_list_subpages shortcode, new attribute depts, set to yes by default to show department pages - if a featured image is chosen for that page it will show that as well. To hide them set it to no.

Version 6.0.0

* *NEW* 	shipping zones, number utilised can now be set between 1 & 9.
* *NEW* 	added outofstock attribute to some listing shortcodes - you can now decide whether to show out of stock items in the listings or not
* *added*  	action, eshop_copy_admin_order_email, for cc'ing that email.
* *added*	filter, eshop_add_ref_feed, for adding a reference to links in feed for affiliates. 
* *added* 	extra classes for styling
* *fixed* 	for users of WP Replicator who were unable to access eShop menu's - hopefully.
* *fixed* 	issue with some shortcodes and widgets showing incorrect number of products.
* *fixed* 	small fix for cancelled orders and discount codes.
* *fixed* 	small fix in admin for product tables.

== Installation ==

Download the plugin, upload to your Wordpress plugins directory and activate. Further instructions and help are included. the plugin automatically creates certain pages for you:
The plugin automatically creates 6 pages for you, all of which are editable.

* Shopping Cart
* Checkout
* Thank you for your order
* Cancelled Order
* Downloads
* Shipping rates

You then need to create a top level shop page, and start creating departments and entering products!

== Frequently Asked Questions ==

= Yet Another Related Posts Plugin =
If you are using this plugin I recommend you disable it before upgrading or deactivating/reactivating eShop, as it may be the cause of some incompatibility issues.


= Does this work with Wordpress MultiSite =

Yes - but do not activate the plugin via the network activate link. eShop needs to run the activation process for each and every site within your network. This is currently not done when you activate a plugin for the entire network. Enable the plugin for the sites to activate themselves instead.

= I updated and now things don't look right = 

There is always a possibility that necessary CSS changes have been made, always remember to check the _Appearance > eShop_ page where you will be notified if they do not match, and be given an opportunity to view the differences.

= Is eShop translatable =

Yes! the po file is available from Quirm.net [eshop.po download](http://quirm.net/download/26/)

= Support =

Available via the WordPress forums (please tag the post eshop) or via [Quirm.net forums](http://quirm.net/forum/forum.php?id=14)

Due to increasing demands we no longer offer free CSS support.

== Upgrade Notice ==

= 6.2.9 =

Please remember to backup your database before upgrading. Please update asap.