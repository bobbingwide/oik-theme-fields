
<?php 
/**
Plugin Name: oik theme fields
Depends: oik base plugin, oik fields, oik themes
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-theme-fields
Description: Additional fields for oik-themes
Version: 0.0.1
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-theme-fields
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2016 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

oikthf_plugin_loaded();

/**
 * Register the additional fields and taxonomies for oik-themes
 */ 
function oikthf_plugin_loaded() {

  add_action( 'oik_fields_loaded', 'oikthf_oik_fields_loaded', 11 );
	add_action( "run_oik-theme-fields.php", "oikthf_run_oikthf" );

}



/**
 * Register additional fields and taxonomies for the oik-themes post type
 * 
 * This is to partially satisfy the schema.json requirements of GitHub GaryJones/genesis-child-theme-index
 * 
 * Note: Not all fields will be registered... we'll use custom taxonomies for most of them
 
 * Schema name | Field       | Type  | Notes
 * ----------- | -----       | ----- | --------
 * name        | _oikth_desc | text  | oik-themes
 * author      | n/a         | -     | See GitHub repository owner
 * purchaseUrl | n/a         | -     | Not required for FREE plugins - see _oikth_product
 * demoUrl     | _oikth_demo | URL   | oik-themes
 * 
 * features    | features
 * 
 
 * layouts		 | 
 */ 
function oikthf_oik_fields_loaded() {
	bw_register_custom_tags( "theme_tags", "oik-themes", "Theme tags" );
	bw_register_custom_tags( "theme_layouts", "oik-themes", "Layouts" );
	bw_register_custom_tags( "postFormats", "oik-themes", "Post Formats" );
	bw_register_custom_tags( "browsersSupported", "oik-themes", "Browsers Supported" );
	oikthf_register_pluginsSupported();
	

}

/** 
 * Register plugins for which the theme explicitly includes styles
 *  
 * Can this be a multiple select noderef? 
 * pluginsSupported
 *
 */
function oikthf_register_pluginsSupported() {

	bw_register_field( "_oikth_plugins", "noderef", "Plugins styled", array( "post_type" => "oik-plugins" ) );
	

}

/**
 * Batch run oik-theme-fields to define the taxonomy terms
 */
function oikthf_run_oikthf() {
	oik_require( "admin/oik-theme-fields-run.php", "oik-theme-fields" );
	oikthf_lazy_run_oikthf();
}

