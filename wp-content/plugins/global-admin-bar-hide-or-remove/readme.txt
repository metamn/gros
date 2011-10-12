=== Global Hide/Remove Admin Bar Plugin ===
Contributors: Don Fischer
Donate link: http://www.fischercreativemedia.com/wordpress-plugins/donate/
Tags: admin, admin bar, settings, options, hacks, plugin, quick
Requires at least: 3.1
Tested up to: 3.1.1
Stable tag: 1.1

Easily add a global option to hide/remove the new Admin bar in WP 3.1+.

== Description ==
Easily add a global option to hide/remove the new Admin bar in WP 3.1+.

Adds an option to the Settings Menu to globally turn off the Admin Bar and/or turn off the user option in the profile to show admin bar.

= Features: = 
Options Page that allows you to:
* Remove WordPress Admin Menu Bar for logged in users
* Remove Profile "Show Admin Bar" message/settings

= TROUBLESHOOTING: =
* Please let me know if you run into any issues with this plugin by sending an email to adminbarplugin@fischercreativemedia.com

== Installation ==

= If you downloaded this plugin: =
* Upload `global-admin-bar-hide-or-remove` folder to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress
* Once Activated, you can add a redirect by entering the correct information in the `Quick Page/Post Redirect` box in the edit section of a page or post
* You can create a redirect with the 'Quick Redirects' option located in the Admin Settings menu.

= If you install this plugin through WordPress 2.8+ plugin search interface: =
* Click Install `Global Hide/Remove Admin Bar Plugin`
* Activate the plugin through the 'Plugins' menu in WordPress
* Once Activated, you can add access the options page from the SETTINGS menu under ADMIN BAR OPTIONS

== Frequently Asked Questions ==
= How does it work? =
It just simply adds an option page for you to turn the admin bar on or off. You can also remove the notice in the profile page to show admin bar.

= I see othe plugins like this - how is yours different? =
It is not that much different. But, most of the other ones just turn it off when you activate them. Wouldn't you want to have an option page to do that yourself if you like?
Aside from that, this plugin also uses a global variable that WordPress uses to see if the admin bar should be on or off, and then also adds another method to ensure that if WordPress changes the admin bar functionality in the near future, the plugin should still work.


== Changelog ==
= 1.0 =
* Plugin Release. (02/23/11)
= 1.1 =
* Fix Action to remove option in user profile page. Worked in profile, but not user-edit.