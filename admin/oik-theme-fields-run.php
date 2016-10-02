<?php // (C) Copyright Bobbing Wide 2016

/**
 * Run oik-theme-fields batch processes
 * 
 * Create the initial post terms for the taxonomies
 */
function oikthf_lazy_run_oikthf() {
	oikthf_initialise_theme_tags();
	oikthf_initialise_theme_layouts();
	oikthf_initialise_postFormats();
	oikthf_initialise_browsersSupported();
}

/**
 * Set empty terms
 *
 * Create entries for each term we'd expect to find in a letter category.
 *
 * @TODO Allow user selection of the letter terms
 *
 * @param string $taxonomy
 * @param array $terms of name => description 
 */
function oikthf_set_terms( $taxonomy='theme_tags', $terms=null ) {
	foreach ( $terms as $term => $description ) {
		$term = trim( $term );
		$description = trim( $description );
		$args = array( "name" => $term
								 , "taxonomy" => $taxonomy
								 , "description" => $description
								 );
		wp_insert_term( $term, $taxonomy, $args ); 
	}
}

/**
 * Initialise theme_tags aka features
 *
 * These are the 'boolean' features
 * 
 * Tag     | Description
 * -----   | -----------------
 * free	   | If the theme is a free theme - see _oikth_type
 * premium | If the theme is a premium theme - see _oikth_type
 * html5   | Supports HTML5
 * XHTML   | Supports XHTML
 * responsive	| The theme displays well at various screen sizes
 * fixedWidth |	The theme doesn't display well at various screen sizes
 * mobileMenu	| The theme displays a mobile-specific menu at small screen sizes
 * accessibilityReady	| If the theme meets all of the guidelines at http://make.wordpress.org/themes/guidelines/guidelines-accessibility/, 
 * customBackground | if the theme supports the WordPress custom background feature
 * customColors | If the theme allows a user to choose custom colors via an interface
 * editorStyle If the theme includes styles for the post editor that match the front-end
 * featuredImageHeader | If the theme displays a featured image as a header
 * grunt | If the theme includes support for *grunt* to allow development tasks to be automated
 * parallax | If the theme design includes a parallax feature
 * psds | If the theme includes PhotoShop (PSD) files of the original design
 * rtlLanguageSupport | If the theme includes right-to-left (RTL) style sheets
 * starter | If the theme author considers this theme to be a starter theme
 * translationReady | If the theme has all strings internationalised, loads a child theme text domain, and includes a .pot file, false if it doesn't
 */
function oikthf_initialise_theme_tags() {

	$theme_tags = array( 
	 "accessibilityReady	" => "If the theme meets all of the guidelines at http://make.wordpress.org/themes/guidelines/guidelines-accessibility/"
	, "customBackground" => "if the theme supports the WordPress custom background feature"
	, "customColors" => "If the theme allows a user to choose custom colors via an interface"
	, "editorStyle" => " If the theme includes styles for the post editor that match the front-end"
	, "featuredImageHeader" => "If the theme displays a featured image as a header"
	, "fixedWidth" =>	"The theme doesn't display well at various screen sizes"
	, "free" => "If the theme is a free theme" // see _oikth_type
	, "grunt" => "If the theme includes support for grunt to allow development tasks to be automated"
	, "mobileMenu	" => "The theme displays a mobile-specific menu at small screen sizes"
	, "parallax" => "If the theme design includes a parallax feature"
	, "premium" => "If the theme is a premium theme" // - see _oikth_type
	, "psds" => "If the theme includes PhotoShop (PSD) files of the original design"
	, "responsive	" => "The theme displays well at various screen sizes"
	, "rtlLanguageSupport" => "If the theme includes right-to-left (RTL) style sheets"
	, "starter" => "If the theme author considers this theme to be a starter theme"
	, "translationReady" => "If the theme has all strings internationalised, loads a child theme text domain, and includes a .pot file"
	, "HTML5  " => "Supports HTML5"
	, "XHTML  " => "Supports XHTML"
	);
	oikthf_set_terms( "theme_tags", $theme_tags );
}


/**
 * Initialise theme_layouts taxonomy
 * 
 * The layouts the theme supports.
 * Genesis default is 6 but child theme may register others.
 * 
 * Name            | Description
 * --------------- | -----------------------------
 * content-sidebar | Content, Primary Sidebar
 * sidebar-content | Primary Sidebar, Content
 * content-sidebar-sidebar | Content, Primary Sidebar, Secondary Sidebar
 * sidebar-sidebar-content | Secondary Sidebar, Primary Sidebar, Content
 * sidebar-content-sidebar | Secondary Sidebar, Content, Primary Sidebar
 * full-width-content | Full Width Content
 *
 */
function oikthf_initialise_theme_layouts() {
	global $_genesis_layouts;
	print_r( $_genesis_layouts );
	$theme_layouts = array();
	foreach ( $_genesis_layouts as $term => $layout ) {
		$theme_layouts[ $term ] =  $layout['label'];
	}
	oikthf_set_terms( "theme_layouts", $theme_layouts );
}

/**
 * Initialise the postFormats taxonomy
 * 
 * List of post formats the theme support
 *
 * Post format | Description
 * ----------- | -------------
 * aside |
 * audio | 
 * chat | 
 * gallery | 
 * image | 
 * link | 
 * quote |
 * standard | 
 * status | 
 * video | 
 *
 * Themes use add_theme_support( 'post-formats', array( 'aside', 'gallery' ) );
 * to register the post-formats. 
 */
function oikthf_initialise_postFormats() {
	//$formats = get_theme_support( 'post-formats' );
	$formats = get_post_format_strings();
	oikthf_set_terms( "postFormats", $formats );
	print_r( $formats );

}

/**
 * Initialise the browsersSupported taxonomy 
 * 
 * @TODO Decide if this should be a taxonomy 
 * structured along the lines of the taxonomies for 'Required version' and 'Compatible up to'
 * so that we can specify the minimum supported versions
 */
function oikthf_initialise_browsersSupported() {
	$browsers = array(  "IE" => "Internet Explorer"
		, "Edge" => "Microsoft Edge"
		,	"Firefox" => "Mozilla Firefox"
		,	"Opera" => "Opera"
		,	"Safari" => "Safari"
		,	"Chrome" => "Google Chrome"
		);
	oikthf_set_terms( "browsersSupported", $browsers );

}
 


