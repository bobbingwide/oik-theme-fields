<?php 
/**
Plugin Name: oik theme fields
Depends: oik base plugin, oik fields, oik themes
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-theme-fields
Description: Additional fields for oik-themes
Version: 0.0.0
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
	add_action( "init", "oikthf_init" );

}

function oikthf_init() {
	remove_filter( 'post_class', 'genesis_featured_image_post_class' );
	add_filter( "genesis_get_image_default_args", "oikthf_genesis_get_image_default_args", 10, 2 );
	add_filter( "genesis_get_image", "oikthf_genesis_get_image", 10, 6 );
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
	oikthf_register_pluginsSupported();
	bw_register_custom_category( "theme_tags", "oik-themes", "Theme tags" );
	bw_register_custom_category( "theme_layouts", "oik-themes", "Layouts" );
	bw_register_custom_category( "postFormats", "oik-themes", "Post Formats" );
	bw_register_custom_category( "browsersSupported", "oik-themes", "Browsers Supported" );
	
	bw_register_field_for_object_type( "theme_tags", "oik-themes" );
	bw_register_field_for_object_type( "theme_layouts", "oik-themes" );
	bw_register_field_for_object_type( "postFormats", "oik-themes" );
	bw_register_field_for_object_type( "browsersSupported", "oik-themes" );
	
	bw_register_field_for_object_type( "oik_tags", "oik-plugins" );
	

}

/** 
 * Register plugins for which the theme explicitly includes styles
 *  
 * Can this be a multiple select noderef? 
 * pluginsSupported
 *
 */
function oikthf_register_pluginsSupported() {

	bw_register_field( "_oikth_plugins", "noderef", "Plugins styled", array( "#type" => "oik-plugins", '#optional' => true, "#multiple" => 5 ) );
	bw_register_field_for_object_type( "_oikth_plugins", "oik-themes" );
	

}

/**
 * Batch run oik-theme-fields to define the taxonomy terms
 */
function oikthf_run_oikthf() {
	oik_require( "admin/oik-theme-fields-run.php", "oik-theme-fields" );
	oikthf_lazy_run_oikthf();
}

/**
 * Set fallback values for the featured image
 * 
 * @param array $defaults
 * @param array $args
 * @return array with fallback parameters set 
 */
function oikthf_genesis_get_image_default_args( $defaults, $args ) {

	bw_trace2();
	unset( $defaults['fallback'] );
	
	$defaults['fallback']['html'] = "[github owner repository screenshot.png]";
	$defaults['fallback']['url'] = null;
	return( $defaults );
}

/**
 * Implement 'genesis_get_image' for deferred finding of the attached image
 * 
 * @param string $output 
 * @param array $args - fairly useless
 * @param integer $id - may be 0
 * @param string $html - could be the dummy github shortcode 
 * @param string $url may be null
 * @param string $src may be null
 */
function oikthf_genesis_get_image( $output, $args, $id, $html, $url, $src ) {
	//bw_trace2();
	if ( !$output ) {
		$post = get_post( null );
		//bw_trace2( $post, "post", null );
		if ( $post->post_type == "oik-themes" ) {
			$gitrepo = get_post_meta( $post->ID, "_oikp_git", true );
			//bw_trace2( $gitrepo, "gitrepo", null );
			if ( $gitrepo ) {
				$image_file = oikthf_github_image_file( $gitrepo, "screenshot.png" ); 
				$image = retimage( null, $image_file, $gitrepo ); 
				$output = $image;
			}
		}
	}	else {
		// It's already set
	}
	return( $output );
}

/**
 * Return a GitHub image file URL
 * 
 * @param string $gitrepo consisting of owner/repository e.g. bobbingwide/genesis-oik
 * @param string $file the image file we want to display - we assume it exists
 * @return string the full file URL
 */ 
function oikthf_github_image_file( $gitrepo, $file='screenshot.png' ) {
	$github[] = "https://raw.githubusercontent.com";
	$github[] = $gitrepo;
	$github[] = "master";
	$github[] = $file;
	$target = implode( "/", $github );
	return( $target );
}
 

