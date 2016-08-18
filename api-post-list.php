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

	wp_enqueue_script( 'api-pl-js', API_POST_LIST_URL . 'js/api-post-list.js', array( 'wp-backbone', 'wp-api', 'jquery-ui-sortable' ), API_POST_LIST_VERSION, true );
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
		<div data-model-id="{{ data.id }}">
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
		</div>
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', '\apipostlist\api_post_list_backbone_tempalates' );


/**
 * Add the post faveorite data to the REST API response.
 * @param $response The API responde object.
 * @param $post     The Post object.
 *
 * @return mixed
 */
function rest_prepare_post_meta( $response, $post ) {

	// If the user isn't logged in, bail.
	if ( ! is_user_logged_in() ) {
		return $response;
	}

	// Get the user's favorites.
	$user_favorites = json_decode( get_user_meta( get_current_user_id(), 'api-post-list-user-favorites', true ) );

	if (
		'' !== $user_favorites &&
		false !== $user_favorites &&
		is_array( $user_favorites )
	) {

		// Is the current post in the user's favorites?
		if ( in_array( $post->ID, $user_favorites ) ) {
			$response->data['postListFavorite'] = true;
		} else {
			$response->data['postListFavorite'] = false;
		}

	}

	// Add the order to the respnse.
	$order             = get_post_meta( $post->ID, 'api-post-list-order', true );
	$response->data['order'] = $order;

	// Return the modified response.
	return $response;
}
add_filter( 'rest_prepare_post', '\apipostlist\rest_prepare_post_meta', 10, 2 );

/**
 * When updating a post, handle the 'post list favorite' oprion.
 *
 * @param  Object $prepared_post The prepared Post object.
 * @param  Object $request       The Request object.
 * @return Object $prepared_post The updated Post object.
 */
function rest_pre_insert_post( $prepared_post, $request ) {
	// If the user isn't logged in, bail.
	if ( ! is_user_logged_in() ) {
		return $prepared_post;
	}

	// Get the user's favorites.
	$meta = get_user_meta( get_current_user_id(), 'api-post-list-user-favorites', true );

	// Set up meta.
	if ( empty( $meta ) ) {
		$meta = array();
	} else {
		$meta = json_decode( $meta );
	}

	// Add the post list favorite to user meta.
	if ( $request['postListFavorite'] ) {

		// Add the post to the user favorites.
		$meta[] = $request['id'];

		// Ensure no duplicates in the stored array.
		$meta   = array_unique( $meta );

	} else {

		// Remove the post from the user's favorites.
		$meta = array_diff( $meta, array( $request['id'] ) );
	}

	// Update the user's meta.
	update_user_meta( get_current_user_id(), 'api-post-list-user-favorites', json_encode( $meta ) );


	if ( isset( $request['order'] ) ) {
		update_post_meta( $prepared_post->ID, 'api-post-list-order', $request['order'] );
	}



	// Continue the insert post process.
	return $prepared_post;
}
add_filter( 'rest_pre_insert_post', '\apipostlist\rest_pre_insert_post', 10, 2 );

