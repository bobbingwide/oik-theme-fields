<?php // (C) Copyright Bobbing Wide 2017

/**
 * Class: OIK_themes_featured_image
 */
class OIK_themes_featured_image {

	public $oik_theme_fields;
	public $featured_image;
	public $file;
	public $requested_file;
	
	function __construct( $oik_theme_fields ) {
		$this->oik_theme_fields = $oik_theme_fields;
		$this->featured_image = null;
		$this->file = null;
	}
	
	/**
	 * Sets the featured image
	 *
	 * Note: In the current version this routine gets invoked when the featured image is not set ( -1 in _thumbnail_id )
	 * Perhaps we need to cater for when it is already set. 
	 */
	function set_featured_image() {
		$this->determine_featured_image();
		bw_trace2( $this, "this", false );
		$this->attach_image();
		$this->set_thumbnail_id();
	}
	
	/**
	 * Determines the featured image
	 *  
	 * - We need the slug to locate the plugin locally, or on WordPress.org, or on GitHub 
	 * - And to find the name of the plugin's banner and icon images
	 * - If the GitHub repo is set then the slug should have been determined from that.
	 *
	 */
	function determine_featured_image() {
		$this->file = null;
		$methods = $this->determine_finder_methods();
		if ( $methods ) {
			$methods = bw_as_array( $methods );
			foreach ( $methods as $method ) {
				if ( null === $this->file ) {
					$this->file = $this->$method();
				}
			}
		}
	}
	
	/**
	 * Uploads the image and attaches it to the post.
	 * 
	 * We don't call media_sideload_image since we've downloaded the file ourselves.
	 * Do we need the attachment ID?
	 */
	function attach_image() {
		if ( $this->file ) {
			$name = $this->attachment_name();
			$desc = $this->attachment_desc();
			$this->featured_image = $this->create_attachment( $this->file, $name, $desc, $this->oik_theme_fields->post_ID );
		}
	}
	
	/**
	 * Determines the attachment file name
	 * 
	 * Note: The temporary file name created may not have the file extension of the file
	 * we downloaded. We need to set this value during the download so that we can access it later.
	 * i.e. We need to know the requested file name as well as the actual file name.
	 * 
	 * 
	 */
	function attachment_name() {
		$filename = $this->oik_theme_fields->oikth_slug;
		$filename .= "-banner-772x250.";
		$filename .= pathinfo ( $this->requested_file, PATHINFO_EXTENSION );
		//$filename .= "jpg";
		return( $filename );
	}
	
	function attachment_desc() {
		$desc = $this->oik_theme_fields->oikth_slug;
		$desc .= " banner";
		return $desc;
	}
	
	/**
	 * Create the attachment file from the temporary file
	 *
	 * Use media_handle_sideload() to do the validation and storage stuff
	 */
	function create_attachment( $file, $name, $desc, $post_id=0 ) {
		$file_array['tmp_name'] = $file;
		$file_array['type'] = mime_content_type( $file ); 
		$file_array['name'] = $name;   
  
		bw_trace2( $file_array ); 
  
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error( $id ) ) {
			bw_trace2( $id );
		} else {
			// e( "attachment: $id" );
		}    
		return( $id );
	}
	
	/**
	 * Returns the finder methods for the banner image
	 *
	 * Whether or not we find the file we're looking for is another thing.
	 * e.g. Bespoke themes may be lurking about on GitHub but won't be on wordpress.org
	 * 
	 * `
  $theme_types = array( 0 => "None"
                       , 1 => "WordPress theme"
                       , 2 => "FREE oik theme"
                       , 3 => "Premium oik theme"
                       , 4 => "Other premium theme"
                       , 5 => "Bespoke"
                       , 6 => "WordPress and FREE theme"
											 , 7 => "Other theme"
                       );
			`								 
	 * 
	 * @TODO Decide what to do with 4. Other premium themes
	 *
	 * @return array of comma separated finder methods
	 
	 */
	function determine_finder_methods() {
		$type_methods = array( "0" => null
		                , "1" => "local,dotorg,github"
										, "2" => "local,github"
										, "3" => "local,github"
										, "4" => "local"
										, "5" => "local,github"
										, "6" => "local,dotorg,github"
										, "7" => "local,github"
										);
		$methods = bw_array_get( $type_methods, $this->oik_theme_fields->oikth_type, null );
		return( $methods );
	}
	
	/**
	 * Update the thumbnail ID
	 */ 
	function set_thumbnail_id() {
		if ( $this->featured_image ) {
			$_POST['thumbnail_id'] = $this->featured_image;
			update_post_meta( $this->oik_theme_fields->post_ID, "_thumbnail_id", $this->featured_image );
			
		}
	}
	
	/**
	 * Locate the image from a local copy of the plugin
	 * 
	 * Here we assume that there is a file in the assets directory.
	 * 
	 */
	function local() {
		$filename = $this->banner_image_name();
		$this->requested_file = $filename;
		return $filename ;
	}
	
	/**
	 * Returns the screenshot image file name
	 * 
	 * @TODO Same code as oik-zip? 
	 */
	function banner_image_name() {
		$slug = $this->oik_theme_fields->oikth_slug;
		$theme_root = WP_CONTENT_DIR . '/themes/' . $slug . '/';
		$theme_root = str_replace( "\\", "/", $theme_root );
		$filename = $theme_root . "screenshot.png";
		if ( !file_exists( $filename ) ) {
			$filename = $theme_root . "screenshot.jpg";
			if ( !file_exists( $filename ) ) {
				$filename = null;
			}	else {
				bw_trace2( $filename, "screenshot.jpg file does exist", false );
			}
    } else {
			bw_trace2( $filename, "screenshot.png file does exist", false );
		}
		
		return $filename;
  }
	
	/**
	 * Returns the screenshot image file name for a GitHub repo
	 * 
	 * This will be an URL which we need to get as a file.
	 * 
	 *
	 * @TODO Shall we look for screenshot.jpg as well as screenshot.png?
	 * 
	 */
	function github() {
		$file = null;
		if ( $this->oik_theme_fields->oikp_git ) {
			$file = $this->get_github_image_file( $this->oik_theme_fields->oikp_git, "screenshot.png" );
		}
		return $file; 
	
	}
	
	function get_github_image_file( $gitrepo, $file ) {
		$this->requested_file = $file;
		$url = $this->github_image_file_url( $gitrepo, $file );
		$file = $this->download_url( $url );
		return( $file );
	
	}
	
	function download_url( $url ) {
		$file = download_url( $url );
		bw_trace2( $file, "file or WP_Error" );
		if ( is_wp_error( $file ) ) {
			$file = null;
		}
		return( $file );
	}

	/**
	 * Return a GitHub image file URL
	 * (https://raw.githubusercontent.com/$owner/$repository/master/$file
	 * 
	 * @param string $gitrepo consisting of owner/repository e.g. bobbingwide/genesis-oik
	 * @param string $file the image file we want to display - we assume it exists
	 * @return string the full file URL
	 
	 */ 
	function github_image_file_url( $gitrepo, $file ) {
		$github[] = "https://raw.githubusercontent.com";
		$github[] = $gitrepo;
		$github[] = "master";
		$github[] = $file;
		$target = implode( "/", $github );
		return( $target );
	}
	
	/**
	 * Returns the banner image file name for a WordPress repo
	 * 
	 * Anyone of these should be acceptable to oik-themes
	 * though we'd prefer one of the first two.
	 */
	function dotorg() {
		$files = array( "screenshot.png"
									, "screenshot.jpg"
									);
		$this->file = null;
		foreach ( $files as $file ) {
			if ( !$this->file ) {
				$this->file = $this->get_dotorg_image_file( $file );
			}
		}
		return( $this->file );
	}
				
	function get_dotorg_image_file( $file ) {
		$this->requested_file = $file;
		$url = $this->dotorg_image_file_url( $file );
		$file = $this->download_url( $url );
		return( $file );
	
	}
	
	/**
	 * Return a WordPress.org image file URL
	 * 
	 * e.g. "http://ps.w.org/$plugin/assets/screenshot.png"
	 * 
	 * @param string $file the image file we want to display - we assume it exists
	 * @return string the full file URL
	 */ 
	function dotorg_image_file_url( $file ) {
		$dotorg[] = "http://ps.w.org";
		$dotorg[] = $this->oik_theme_fields->oikth_slug;
		$dotorg[] = "assets";
		$dotorg[] = $file;
		$target = implode( "/", $dotorg );
		return( $target );
	}

 
}
