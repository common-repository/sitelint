=== Plugin Name ===
Contributors: ctomczyk, tmehdi
Tags: automated, web, audits, accessibility, seo, performance, security, privacy
Requires at least: 4.7
Tested up to: 6.6.2
Stable tag: 1.5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Client-side & real-time checker for Accessibility, SEO, Performance, Security, Privacy, Technical issues.

== Description ==

Audit your site today to improve engagement for your audiences! Let SiteLint identify Accessibility, SEO, Performance, Security, Privacy, Technical issues in one click!

== Installation ==

1. Upload `sitelint` folder to the `/wp-content/plugins/` directory or install SiteLint plugin in WordPress.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Log in to SiteLint or create a new account.
4. That's all. The audits should run now automatically.

Note that the SiteLint WordPress plugin shows only scores for each Audit. You need to visit https://platform.sitelint.com/ to get all details.

== Screenshots ==

1. SiteLint Dashboard
2. SiteLint Issue details example

== Changelog ==

= 1.5.2 =

* Rewrite enum to be compatible with PHP version 7.x

= 1.5.1 =
* Set "Add SiteLint logo to the page footer" to false by default
* Fixing infinite loop when trying to obtain JWT access token from the SiteLint API

= 1.5.0 =
* Changed the logic for API token selection to site selection.
* Fixed issues and implemented stabilities.
* Added SiteLint audits Web UI Widget display.
* Enabled site audit capability for unregistered users on the SiteLint Platform.

= 1.4.0 =
* Fixing issue: Undefined array key "page" in sitelint-admin.php on line 131

= 1.3.0 =
* Upgrading npm packages and rebuilding frontend admin and public packages;

= 0.1 =
* Initial version of the Plugin
