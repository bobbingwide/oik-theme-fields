<?php 
/**
Plugin Name: oik theme fields
Depends: oik base plugin, oik fields, oik themes
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-theme-fields
Description: Additional fields for oik-themes
Version: 0.0.2
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-theme-fields
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2016,2017 Bobbing Wide (email : herb@bobbingwide.com )

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
	
	add_action( "oik_admin_menu", "oikthf_oik_admin_menu" );

}

function oikthf_init() {
	remove_filter( 'post_class', 'genesis_featured_image_post_class' );
	add_filter( "genesis_get_image_default_args", "oikthf_genesis_get_image_default_args", 10, 2 );
	add_filter( "genesis_get_image", "oikthf_genesis_get_image", 10, 6 );
	add_filter( "wp_get_attachment_image_src", "oikthf_wp_get_attachment_image_src", 10, 4 );
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
		$image_file = oikthf_github_repo_screenshot();
		if ( $image_file ) {
			$output = retimage( null, $image_file, "" ); 
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

/**
 * Implement 'wp_get_attachment_image_src' for oik-theme-fields 
 * 
 * @param array|false  $image         Either array with src, width & height, icon src, or false.
 * @param int          $attachment_id Image attachment ID.
 * @param string|array $size          Size of image. Image size or array of width and height values
 *                                    (in that order). Default 'thumbnail'.
 * @param bool         $icon          Whether the image should be treated as an icon. Default false.
 */
function oikthf_wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
	if ( !$image ) {
		$image[0] = oikthf_github_repo_screenshot();
		// We can't set the width or height
	} 
	bw_trace2( $image, "image" );
	bw_backtrace();
	return( $image );	
}

/**
 * Return the GitHub repository screenshot file
 *
 */
function oikthf_github_repo_screenshot() {
	$image_file = null;
	$post = get_post( null );
	//bw_trace2( $post, "post", null );
	if ( $post->post_type == "oik-themes" ) {
		$gitrepo = get_post_meta( $post->ID, "_oikp_git", true );
		//bw_trace2( $gitrepo, "gitrepo", null );
		if ( $gitrepo ) {
			$image_file = oikthf_github_image_file( $gitrepo, "screenshot.png" ); 
		}
	}
	return( $image_file );
}

	

/**
 * Registers hooks to automatically set missing fields.
 */
function oikthf_oik_admin_menu() {
	add_action( "save_post_oik-themes", "oikthf_save_post_oik_themes", 10, 3 );
	add_action( "save_post", "oikthf_save_post", 10, 3 );
}


/**
 * Implements 'save_post_oik-themes' action for oik-theme-fields
 * 
 * Lazy loads the logic
 * 
 * @param ID $post_ID ID of the post 
 * @param object $post the post object
 * @param bool $update true if it's an update
 */ 
function oikthf_save_post_oik_themes( $post_ID, $post, $update ) {
	if ( "auto-draft" !== $post->post_status ) { 
		oik_require( "admin/oik-theme-fields-save-post.php", "oik-theme-fields" );
		oikthf_lazy_save_post_oik_themes( $post_ID, $post, $update );
	}
	//gob();

}

/**
 * Implements 'save_post' for oik-plugin-fields
 * 
 * Not a good idea when working with other post types
 * but this can be used to produce a Fatal message during the post update
 * before we redirect to edit post, a separate transaction. 
 */
function oikthf_save_post( $post_ID, $post, $update ) {	
	//gob();
}


 

