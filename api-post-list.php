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

function api_post_list_shortcode( $atts ) {

	wp_enqueue_script( 'api_pl', API_POST_LIST_URL . 'js/api-pl.js', array( 'wp-api' ), API_POST_LIST_VERSION, true );
	$rand_id = rand();
?>
	<script type="text/javascript">
	var postListData<?php echo esc_attr( $rand_id ); ?> = <?php echo json_encode( $atts ); ?>;
	</script>
	<div class="api-post-list-container" data-plid="" ><?php echo esc_attr( $rand_id ); ?></div>
	<script type="text/html" id="tmpl-single-post">
		<div class="api-post-list-image">
			<img src="{{ data.imageSrc }}" />
		</div>
		<div class="api-post-list-title">
			{{ data.title }}
		</div>
	</script>
<?
}
add_shortcode( 'api_post_list', 'api_post_list_shortcode' );
