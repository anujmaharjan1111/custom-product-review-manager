<?php
// query to fetch latest 5 product reviews.
$query = new WP_Query( [
	'post_type'      => 'product_review',
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC'
] );


if ( $query->have_posts() ) { // Check if there is product review
	?>
    <section class="woocommerce reviews-section">
        <h2 class="woocommerce-Reviews-title"><?php _e( 'Latest Product Reviews', 'custom-product-review' ); ?></h2>
        <ul class="commentlist woocommerce-review-list">
			<?php
			while ( $query->have_posts() ) { //loop through the latest product reviews(posts)
				$query->the_post();
				// Get associated product ID of the review
				$review_id  = get_the_ID();
				$product_id = get_post_meta( $review_id, '_cprm_product_id', true );

				// Get product name and product url using the associated product id.
				$product_name = $product_id ? get_the_title( $product_id ) : '';
				$product_link = $product_id ? get_permalink( $product_id ) : '#';

				// Get rating and reviewer name associated to the review (from the post meta).
				$rating   = intval( get_post_meta( $review_id, '_cprm_rating', true ) );
				$reviewer = esc_html( get_post_meta( $review_id, '_cprm_reviewer', true ) );

				// WooCommerce star rating calculation: 0 to 100%
				$rating_percentage = ( $rating / 5 ) * 100;
				?>
                <li class="review">
                    <div class="comment_container">
	                    <div class="star-rating" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %d out of 5', 'woocommerce' ), $rating ) ); ?>">
							<span style="width:<?php echo esc_attr( $rating_percentage ); ?>%">
								<?php
								// WooCommerce uses text inside the <span> for screen readers only
								printf( esc_html__( 'Rated %d out of 5', 'woocommerce' ), $rating );
								?>
							</span>
	                    </div>
                        <div class="comment-text">
							<?php
							printf( '<p class="meta"><a href="%s">%s</a> ' . esc_html__( 'reviewed by', 'custom-product-review' ) . '<strong class="woocommerce-review__author"> %s</strong> </p>', esc_url( $product_link ), esc_html( $product_name ), $reviewer );
							?>
                            <h4 class="woocommerce-review__title"><?php echo esc_html( get_the_title() ); ?></h4>
                            <div class="description"><?php echo wpautop( esc_html( get_the_content() ) ); ?></div>
                        </div>
                    </div>
                </li>
				<?php
			}
			?>
        </ul>
    </section>
	<?php
} else {
	// show the no review found message in case of empty reviews.
	echo '<p>' . __( 'No reviews found.', 'custom-product-review' ) . '</p>';
}

wp_reset_postdata(); // reset the global post data after doing custom query i.e WP_Query.