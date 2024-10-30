=== Migration Drupal to Wordpress ===
Contributors: hereticbear
Donate link: 
Tags: migration, drupal, wordpress, bd
Requires at least: 4.0.1
Tested up to: 4.4.2
Stable tag: 4.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Migration Drupal to Wordpress is a tool to move the basic data from databases drupal to wordpress.

This plugin has been tested in WordPress v4 with drupal v6

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Access the menu "migration", fill in the form and select the data to pass


== Frequently Asked Questions ==

= What data passes this plugin? =

All except the table options and links

= Is it only works drupal v6 to wordpress v4? =

Yes, because when you change the cms version changes the position of the data.

= Can I change the names of the tables or the migration class? =

Yes, I encourage you to make your own versions of the class for versions and share your needed

= Why it is giving error when extracting data or entering? =

Does not match the version of the cms
No connection is established with the database
Not enough runtime on the server
Failure prefix
Does not match the name of one or more tables, or columns of these
Lack of user permissions given

= Why not spend all the data that is being asked? =

Because the cache or server runtime are lower than what you need or the amount of data to move.
But it may also be due to a syntax error, which is the class normalize(string)

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0 =
* Upload the plugin

== Upgrade Notice ==

= 1.0 =
* Upload the plugin

== Arbitrary section ==