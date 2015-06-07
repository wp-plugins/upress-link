=== uPress Link ===
Contributors: ilanraid,ilanf
Tags: upress,hosting,companion,link,manager,cdn,optimization,performance
Requires at least: 4.0
Tested up to: 4.1.1
Stable tag: 1.1.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

uPress Link is a companion plugin for the WordPress hosting manager at https://www.upress.co.il

== Description ==
uPress Link is a companion plugin for the WordPress hosting manager [uPress](https://www.upress.co.il).

= Features =
* Simple interface to manage the most frequently used features from uPress
* Manage auto updating, auto redirection and firewall settings
* Manage CDN (Content Delivery Network) settings
* Manage uPress optimization settings
* More features coming soon...

**NOTE**
* This plugin does not work as a standalone. It requires an account with [uPress](https://www.upress.co.il).
* Some features require that specific settings will be enabled in uPress

== Installation ==
1. Upload `upress-link` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create API key at https://my.upress.co.il/account/websites
1. Paste the generated API key in the uPress Link settings page (located under 'Settings' menu)

== Frequently Asked Questions ==
= Where are all the options? =
You must generate and save an API key, uPress link does not work without it.

= How to generate API key? =
1. You must have an account with [uPress](https://www.upress.co.il)
1. Login to your account at [my.uPress.co.il](https://my.upress.co.il)
1. Click on your website
1. Select 'Settings' tab
1. Click on 'Generate API Key' button
1. The API key will be shown bellow the button

= Why are the CDN options not available? =
You must enable the CDN from uPress interface:
1. Log in at https://my.upress.co.il
1. Click on your website
1. Select 'Performance' tab
1. Click on 'Manage CDN Settings' and follow on screen instructions

== Screenshots ==
1. uPress Link main view

== Changelog ==
= 1.1 =
1. Added option to fix media upload path
1. Added options to search and replace in database
= 1.0 =
Initial release