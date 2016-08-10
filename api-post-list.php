<?php
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
define( 'API_POST_LIST_VERSION', '0.0.1' );




function api_post_list_scripts() {

}
add_action( 'wp_scripts', 'api_post_list_scripts' );




function api_post_list_shortcode( $atts ) {
	wp_enqueue_script( 'api_pl', API_POST_LIST_URL . 'js/api-pl.js', array( 'wp-backbone', 'wp-api' ), API_POST_LIST_VERSION, true );

	$rand_id = rand();
	$to_return = '<div class="api-post-list-container" data-plid="' . esc_attr( $rand_id ) . '" data-posts="' . esc_attr( json_encode( $atts ) ) .'">';

	// Build a shell.
	$data = explode( ',', $atts['ids'] );
	$count = sizeof( $data );
	while ( $count-- >= 0 ) {
		$to_return .= '<div class="list-loading">
						<div class="api-post-list-image">
						</div>
						<div class="api-post-list-title">
						</div>
					 </div>';
	}

	$to_return .= '</div>';

	return $to_return;
}
add_shortcode( 'api_post_list', 'api_post_list_shortcode' );





function api_post_list_backbone_tempalates( ) {
	?>
	<script type="text/html" id="tmpl-single-post">
			<# console.log( data.attributes ); #>
		<div class="api-post-list-image">
 		</div>
		<div class="api-post-list-title">
		{{ data.attributes.title.rendered }}
		</div>
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'api_post_list_backbone_tempalates' );
