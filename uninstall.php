<?php
/**
 * Custom Product Review Manager Uninstall
 *
 * Script that runs when the plugin Custom Product Review Manager is uninstalled
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit; // exit if the file is directly accessed.

// only proceed if user have an authority to delete
if ( ! current_user_can( 'delete_plugins' ) ) {
	exit;
}

global $wpdb;

// Delete all 'product_review' posts permanently after plugin is uninstalled
$cprm_product_review = get_posts( [
	'post_type'   => 'product_review',
	'numberposts' => - 1,
	'post_status' => 'any',
	'fields'      => 'ids',
] ); // get all posts associated to the custom post_type 'cprm_product_review'

if ( ! empty( $cprm_product_review ) ) {
	foreach ( $cprm_product_review as $review_id ) { //loop through and delete all the reviews posts and post meta
		// delete all associated post meta of reviews, before deleting the reviews(custom posts) itself.
		delete_post_meta( $review_id, '_cprm_rating' );
		delete_post_meta( $review_id, '_cprm_reviewer' );
		delete_post_meta( $review_id, '_cprm_product_id' );

		wp_delete_post( $review_id, true ); // delete review post
	}
}