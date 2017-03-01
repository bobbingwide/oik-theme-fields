<?php // (C) Copyright Bobbing Wide 2017

/**
 * Class: OIK_theme_fields
 *
 * Automatically sets field values for a theme from what's available.
 * Saves us having to look it up.
 * 
 * Fields we'll try to set are:
 * 
 * Name      | Contents
 * --------- | --------------
 * _thumbnail_id | We may need to update the featured image ourselves
 * 
 * tbc
 */
 
class OIK_theme_fields {

	public $oikth_type = null; /* Theme Type  */
	public $oikth_slug = null; /* Theme Slug - i.e. folder name */
	public $oikth_desc = null;
	public $oikp_git = null;
	public $git_owner = null;
	public $git_repo = null;
	public $post_ID = null;
	public $post = null;
	public $featured_image = null;
	// public $readme_txt = null;
	// public $theme_file = null;
	
	/**
	 * Constructor method
	 */
	function __construct() {
	}
	
	/**
	 * Saves the post fields
	 *
	 * Notes: 
	 * 
	 * - We're hooking into save_post_oik-themes ourselves
	 * - We might also get invoked by oik's logic. 
	 * - We don't want to do the hard work twice on each save!
	 * - So we check the $_POST fields before setting them.
	 *
	 * @TODO Check whether or not oik should be changed to not need to call save_post_$post_type
	 *
	 * `
	 C:\apache\htdocs\wordpress\wp-content\themes\oik-bwtrace\includes\bwtrace-actions.php(476:0) bw_trace_attached_hooks(6) 80 2017-02-20T12:14:30+00:00 0.858732 0.000770 cf=save_post 43 188223 2097152/2097152 256M F=582 save_post 
: 0   bw_trace_attached_hooks;9
: 1   genesis_inpost_scripts_save;2 genesis_inpost_layout_save;2
: 10   delete_get_calendar_cache;1 WPSEO_Post_Type_Sitemap_Provider::save_post;1 WPSEO_Primary_Term_Admin::save_primary_terms;1 sharing_meta_box_save;1 bw_effort_save_postdata;3 oik_clone_save_post;3 oikplf_save_post;3
: 999   Post_Type_Switcher::save_post;2
   * `
	 */
	function save_post( $post_ID, $post, $update ) {
		bw_trace2();
		$this->post_ID = $post_ID;
		$this->post = $post;
		$this->query_fields();
		$this->set_fields();
		//$this->set_desc();
		//$this->set_oik_tags();
	
	}
	
	/**
	 * Queries the post's fields
	 * 
	 * The post object itself is pretty useless
	 * since it doesn't contain values for any of the fields we're interested in
	 * these are expected to be in $_POST
	 * 
	 * 
	 */
	function query_fields() {
		bw_trace2( $_POST, "_POST" );
		$this->get_featured_image();
		$this->oikth_type = $this->get_field( "_oikth_type" );
		$this->oikth_slug = $this->get_field( "_oikth_slug" );
		$this->oikp_git = $this->get_field( "_oikp_git" );
		//$this->oikth_desc = $this->get_field( "_oikth_desc" );
	}
	
	/**
	 * Populates slug from GitHub repo
	 */
	function slug_from_git() {
		$this->get_git_fields();
		if ( !$this->oikth_slug && $this->git_repo ) {
			$this->oikth_slug = $this->git_repo;
			$this->set_field( "_oikth_slug", $this->oikth_slug );
		}
	}
	
	
	/**
	 * Sets git_owner and git_repo from oikp_git
	 *
	 * If only the owner is set then we copy assume the repo is the slug
	 *
	 */
	function get_git_fields() {
		if ( $this->oikp_git ) {
			$slashpos = strpos( $this->oikp_git, "/" );
			if ( $slashpos ) {
				$this->git_owner = substr( $this->oikp_git, 0, $slashpos );
				$this->git_repo = substr( $this->oikp_git, $slashpos+1 ); 
			} else {
				$this->git_owner = $this->oikp_git;
				// But it's wrong - so take the slug ?
				$this->git_repo = $this->oikth_slug;
				$this->set_field( "_oikp_git", $this->git_owner . '/' . $this->git_repo );
			}
			
		}
	}
	
	/**
	 * Gets the post ID for the featured image
	 *
	 * Note: -1 means that no image is set.
	 */
	function get_featured_image() {
		$this->featured_image = $this->get_field( "_thumbnail_id" ); 
	}
	
	/**
	 * Get a value from the $_POST array
	 * 
	 * Sanitizing would be a good idea
	 */
	function get_field( $field_name ) {
		$field_value = bw_array_get( $_POST, $field_name, null );
		return $field_value;
	}
	
	/**
	 * Set a value in the $_POST array
	 * 
	 */
	function set_field( $field_name, $field_value ) {
		$_POST[ $field_name ] = $field_value;
	}
	
	/**
	 * Sets the fields we can
	 */
	function set_fields() {
		$this->slug_from_git();
		if ( $this->oikth_slug ) {
			$this->set_featured_image();
		}
	} 
	
	/**
	 * Sets the featured image
	 * Uses the OIK_themes_featured_image class
	 */ 
	function set_featured_image() {
		if ( "-1" === $this->featured_image ) {
			oik_require( "admin/class-oik-themes-featured-image.php", "oik-theme-fields" );
			$featured_image = new OIK_themes_featured_image( $this );
			$featured_image->set_featured_image();
		} else {
			// Featured image already set so no need to do it again.
		}
	}
	
	
	
}
