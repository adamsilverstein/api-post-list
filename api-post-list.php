<?php
namespace apipostlist;

/**
 * Plugin Name: API Post List
 * Version: 0.0.1
 * Description: An API driven post list shortcode.
 * Author: adamsilverstein
 * Author URI:
 * Plugin URI:
 * Text Domain: api-post-list
 * Domain Path: /languages
 * @package API Image Grid
 */

define( 'API_POST_LIST_URL', plugin_dir_url( __FILE__ ) );
define( 'API_POST_LIST_VERSION', '1.0.0' );

/**
 * Build the shortcode.
 * @param  String $atts The shortcode parameters.
 * @return String       The rendered shortcode.
 */
function api_post_list_shortcode( $atts ) {
	global $post;

	wp_enqueue_script( 'api-pl-js', API_POST_LIST_URL . 'js/api-post-list.js', array( 'wp-backbone', 'wp-api' ), API_POST_LIST_VERSION, true );
	wp_enqueue_style( 'api-pl-css', API_POST_LIST_URL . 'css/api-post-list.css', API_POST_LIST_VERSION, true );

	$api_settings = array (
		'canEdit'        => current_user_can( 'edit_post', $post->ID ),
		'isUserLoggedIn' => is_user_logged_in(),
	);

	wp_localize_script( 'api-pl-js', 'apiPostListSettings', $api_settings );

	$rand_id = rand();
	$to_return = '<div class="api-post-list-container" data-plid="' . esc_attr( $rand_id ) . '" data-posts="' . esc_attr( json_encode( $atts ) ) .'">';

	// Build a shell.
	$data = explode( ',', $atts['ids'] );
	$count = sizeof( $data ) ;

	// Add one block per post in the view.
	while ( $count-- > 0 ) {
		$to_return .= '
			<div class="list-loading">
				<div class="api-post-list-image"></div>
				<div class="api-post-list-title"></div>
			</div>
		';
	}

	$to_return .= '</div>';

	return $to_return;
}
add_shortcode( 'api_post_list', '\apipostlist\api_post_list_shortcode' );

/**
 * Add the app templates to the site footer.
 */
function api_post_list_backbone_tempalates( ) {
	global $post;

	$content_editable = current_user_can( 'edit_post', $post->ID ) ? 'contenteditable' : '';
	?>
	<script type="text/html" id="tmpl-single-post">
		<#
		if ( apiPostListSettings.isUserLoggedIn ) {
		#>

			<div class="api-post-list-highlight{{ data.postListFavorite ? ' is-favorite' : '' }}">
				<span>☆</span>
			</div>
		<#
		}
		#>
		<#
		if ( ! _.isUndefined( data.attributes._embedded['wp:featuredmedia'] ) ) {
		if ( ! _.isUndefined( data._embedded['wp:featuredmedia'] ) ) {
		#>
			<div class="api-post-list-image">
				<a href="{{ data.link }}">
					<img width="50px" height="50px" src="{{ data._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail.source_url }}" />
				</a>
			</div>
		<#
		}
		#>
		<div class="api-post-list-title" <?php echo esc_attr( $content_editable ); ?> >
			<a href="{{ data.link }}">
				{{ data.title.rendered }}
			</a>
		</div>
		<div class="api-post-list-author-name">
			By: {{ data._embedded.author[0].name }}
		</div>
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', '\apipostlist\api_post_list_backbone_tempalates' );
