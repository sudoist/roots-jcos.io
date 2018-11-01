<?php

// Include Beans. Do not remove the line below.
require_once( get_template_directory() . '/lib/init.php' );

// Enqueue Font Awesome
add_action( 'wp_enqueue_scripts', 'ifamily_load_fontawesome' );

function ifamily_load_fontawesome() {	
	wp_enqueue_style(
        'font-awesome',
        'https://use.fontawesome.com/releases/v5.0.8/css/all.css',
        array(),
        '5.0.8'
    );	
}

// Remove this action and callback function if you are not adding CSS in the style.css file.
add_action( 'wp_enqueue_scripts', 'beans_child_enqueue_assets' );

function beans_child_enqueue_assets() {
	wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css'
    );
}

// Remove primary menu and permanently enable offcanvas.
beans_remove_action( 'beans_primary_menu' );
beans_modify_action_hook( 'beans_primary_menu_offcanvas_button', 'beans_header' );
beans_replace_attribute( 'beans_primary_menu_offcanvas_button', 'class', 'uk-hidden-large', 'uk-float-right' );

// Add class to home html tag
add_filter('language_attributes', 'add_home_html_class');

function add_home_html_class( $output ) {
    if ( is_front_page() ) {
        return $output . ' class="home"';
    }
}

// Remove post author meta
add_filter( 'beans_post_meta_items', 'beans_child_remove_post_meta_items' );

function beans_child_remove_post_meta_items( $items ) {
    unset( $items['author'] );
    return $items;
}

// Modify left footer text
add_filter( 'beans_footer_credit_text_output', 'modify_left_copyright' );

function modify_left_copyright() {
    wp_nav_menu( array( 'theme_location' => 'footer-menu' ) );
}

// Add a custom menu location for footer right
add_action( 'init', 'register_footer_menu' );

function register_footer_menu() {
    register_nav_menu('footer-menu',__( 'Footer Right Menu' ));
}

// Modify right footer text
add_filter( 'beans_footer_credit_right_text_output', 'modify_right_copyright' );

function modify_right_copyright() {
    return '© ' . date('Y') . ' - jcos.io | Hey! I built this theme with <a href="https://www.getbeans.io">Beans</a>';
}

// Display excerpt instead of full posts
add_filter( 'the_content', 'beans_child_modify_post_content' );

function beans_child_modify_post_content( $content ) {
    // Stop here if we are on a single view.
    if ( is_singular() )
        return $content;

    // Return the excerpt() if it exists other truncate.
    if ( has_excerpt() )
        $content = '<p>' . get_the_excerpt() . '</p>';
    else
        $content = '<p>' . wp_trim_words( get_the_content(), 40, '...' ) . '</p>';
}

// Exclude Work category in main post
add_action( 'pre_get_posts', 'exclude_category' );

function exclude_category( $query ) {
    if ( $query->is_home() && $query->is_main_query() ) {
        $query->set( 'cat', '-15' ); // Work id = 15
    }
}

// Exclude Work category in sidebar
add_filter("widget_categories_args","exclude_widget_categories");

function exclude_widget_categories($args) {
	$exclude = "15"; // The IDs of the excluding categories, Work id = 15
	$args["exclude"] = $exclude;
	return $args;
}

// Exclude Work category in previous next link
add_filter( 'get_next_post_join', 'exclude_cat_from_previous_next_JOIN', 10, 3 );
add_filter( 'get_previous_post_join', 'exclude_cat_from_previous_next_JOIN', 10, 3 );
add_filter( 'get_next_post_where', 'exclude_cat_from_previous_next_WHERE', 10, 3);
add_filter( 'get_previous_post_where', 'exclude_cat_from_previous_next_WHERE', 10, 3);

function exclude_cat_from_previous_next_JOIN( $join = null, $in_same_cat = false, $excluded_categories = '' ) {
    if ( is_admin() ) {
        return $join;
    } else {
        global $wpdb;
        // NOTE: The p in p.ID is assigned from $wpdb->posts in the get_adjacent_post function.
        return $join." INNER JOIN $wpdb->term_relationships ON 
                      (p.ID = $wpdb->term_relationships.object_id) 
                      INNER JOIN $wpdb->term_taxonomy ON 
                      ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";			
    }
}
	
function exclude_cat_from_previous_next_WHERE( $where = null, $in_same_cat = false, $excluded_categories = '' ) {
    if ( is_admin() ) {
        return $where;
    } else {
        global $wpdb;
        $exclude = '15'; //The IDs of the categories to exclude. Work id = 15
        $exclude = apply_filters( 'exclude_cat_from_previous_next_WHERE_filter', $exclude );
        return $where." AND $wpdb->term_taxonomy.taxonomy = 'category' 
                        AND $wpdb->term_taxonomy.term_id NOT IN ($exclude)";
    }
}

// Remove Query String from Static Resources
// https://wpbeansframework.com/2016/11/30/speedy-theme/
add_filter( 'style_loader_src', 'remove_cssjs_ver', 10, 2 );
add_filter( 'script_loader_src', 'remove_cssjs_ver', 10, 2 );

function remove_cssjs_ver( $src ) {
    if ( strpos( $src, '?ver=' ) )
        $src = remove_query_arg( 'ver', $src );
        return $src;
}

// Add Social share buttons
// http://crunchify.me/2aAAlvK
add_filter( 'the_content', 'crunchify_social_sharing_buttons');

function crunchify_social_sharing_buttons( $content ) {
    global $post;
    if ( is_singular() && !is_home() && !is_front_page() ) {

        // Get current page URL 
        $crunchifyURL = urlencode(get_permalink());

        // Get current page title
        $decodeTitle = html_entity_decode( get_the_title(), ENT_COMPAT, 'UTF-8' );
        $crunchifyTitle = htmlspecialchars( urlencode( $decodeTitle ), ENT_COMPAT, 'UTF-8' );
        $crunchifyEmailTitle = 'JCOS.IO : ' . str_replace( '+', '%20', $crunchifyTitle );

        // Get Post Thumbnail for pinterest
        $crunchifyThumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

        // Social media base URL
        $twitter = 'https://twitter.com/intent/tweet?text=';
        $facebook = 'https://www.facebook.com/sharer/sharer.php?u=';
        $google = 'https://plus.google.com/share?url=';
        $linkedIn = 'https://www.linkedin.com/shareArticle?mini=true&url=';
        $pinterest = 'https://pinterest.com/pin/create/button/?url=' . $crunchifyURL;

        // Email body
        $emailBody = 'Greetings! Check this out ' . $crunchifyURL;

        // Social icons
        $twitterIcon = '<i class="uk-icon-button uk-icon-twitter"></i>';
        $facebookIcon = '<i class="uk-icon-button uk-icon-facebook"></i>';
        $googleIcon = '<i class="uk-icon-button uk-icon-google-plus"></i>';
        $linkedInIcon = '<i class="uk-icon-button uk-icon-linkedin"></i>';
        $pinterestIcon = '<i class="uk-icon-button uk-icon-pinterest"></i>';
        $emailIcon = '<i class="uk-icon-button uk-icon-envelope"></i>';

        // Construct sharing URL without using any script
        $twitterURL = $twitter . $crunchifyTitle .'&amp;url='. $crunchifyURL .'&amp;via=sudoist';
        $facebookURL = $facebook . $crunchifyURL;
        $googleURL = $google . $crunchifyURL;
        $linkedInURL = $linkedIn . $crunchifyURL .'&amp;title='. $crunchifyTitle;
        $emailURL = 'mailto:?subject=' . $crunchifyEmailTitle .'&body=' . $emailBody;

        // Based on popular demand added Pinterest too
        $pinterestURL = $pinterest . '&amp;media=' . $crunchifyThumbnail[0] . '&amp;description=' . $crunchifyTitle;

        // Add sharing button at the end of page/page content
        $content .= '<ul class="uk-subnav share-icons uk-margin-top">';
        $content .= '<h2>Care to share?</h2>';
        $content .= '<li><a href="'. $twitterURL .'" target="_blank">' . $twitterIcon . '</a></li>';
        $content .= '<li><a href="'. $facebookURL.'" target="_blank">' . $facebookIcon . '</a></li>';
        $content .= '<li><a href="'. $googleURL.'" target="_blank">' . $googleIcon . '</a></li>';
        $content .= '<li><a href="'. $linkedInURL.'" target="_blank">' . $linkedInIcon . '</a></li>';
        $content .= '<li><a href="'. $pinterestURL.'" data-pin-custom="true" target="_blank">' . $pinterestIcon . '</a></li>';
        $content .= '<li><a href="'. $emailURL .'" data-pin-custom="true" target="_blank">' . $emailIcon . '</a></li>';
        $content .= '</ul>';

        return $content;
    } else {
        // if not a post/page then don't include sharing button
        return $content;
    }
};

// Add Google Adsense code
add_action('wp_head', 'add_googleAdsense');
function add_googleAdsense() { 
    if ( is_singular() && !is_home() && !is_front_page() && !is_page() ) { ?>
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({
                google_ad_client: "ca-pub-6055949871460102",
                enable_page_level_ads: true
            });
        </script>
<?php 
    }
}

// Add Google Analytics code
add_action('wp_footer', 'add_googleAnalytics');
function add_googleAnalytics() { ?>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-61196019-1', 'auto');
		ga('send', 'pageview');
	</script>
<?php } ?>
