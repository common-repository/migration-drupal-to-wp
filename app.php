<?php
/*
 * Plugin Name: Migration Drupal to WP
 * Plugin URI: https://wordpress.org/plugins/migration-drupal-to-wp/
 * Description: Migration of content databases drupal v6 to wordpress v4.
 * Version: 0.0
 * Author: hereticbear
 * Author URI: http://bears-house.tumblr.com
 * License: GPLv2
 */

/* 
	Copyright 2016- hereticbear  (email : marcos.rioja@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function migration_drupal_to_wp_page() {
	add_menu_page('Migration_drupal_to_wp', 
		'Migration', 
		'manage_options', 
		'migration', 
		'migration_drupal_to_wp_union', 
		plugin_dir_url( __FILE__ ) . 'bear.ico', 
		0
	);
}

function migration_drupal_to_wp_union() {
	require 'app/index.php';
}

add_action( 'admin_menu', 'migration_drupal_to_wp_page');


function migration_drupal_to_wp_src($hook) {
    if('toplevel_page_migration' != $hook) {
        return;
    }

    	wp_enqueue_script( 'migration_drupal_to_wp_js_script', plugin_dir_url( __FILE__ ) . 'app/script.js' );
	wp_enqueue_script( 'migration_drupal_to_wp_js_bootstrap', plugin_dir_url( __FILE__ ) . 'app/bootstrap/js/bootstrap.min.js' );
	wp_enqueue_style( 'migration_drupal_to_wp_css_bootstrap', plugin_dir_url( __FILE__ ) . 'app/bootstrap/css/bootstrap.min.css' );
}

add_action( 'admin_enqueue_scripts', 'migration_drupal_to_wp_src' );

function migration_drupal_to_wp_Start_Session($hook) {
	if('toplevel_page_migration' != $hook) {
		return;
	}

	if(!session_id()) {
		session_start();
	}
}

add_action('admin_enqueue_scripts', 'migration_drupal_to_wp_Start_Session');

?>
