<?php // (C) Copyright Bobbing Wide 2017

/**
 * Autosets oik-themes fields
 * 
 * @param ID $ID
 *  
 */
function oikthf_lazy_save_post_oik_themes( $post_ID, $post, $update ) {
	oik_require( "admin/class-oik-theme-fields.php", "oik-theme-fields" );
	$oik_theme_fields = new OIK_theme_fields();
	$oik_theme_fields->save_post( $post_ID, $post, $update );
}



