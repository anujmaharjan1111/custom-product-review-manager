<?php

class Custom_Product_Review_Manager {

	public function __construct() {
		add_action( 'init', array(
			$this,
			'register_custom_post_type'
		) ); // register the custom post type required for the plugin.

		// Enqueue admin styles of the plugin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		add_action( 'add_meta_boxes', array(
			$this,
			'add_meta_boxes_for_reviews'
		) );// add meta boxes for custom post type 'product_review'.

		add_action( 'save_post', array( $this, 'save_meta_box' ) ); // hook to trigger, when the post is saved.


		// show some custom columns in admin list view of the custom post type 'product_review'
		add_filter( 'manage_product_review_posts_columns', array( $this, 'show_custom_columns' ) );
		add_action( 'manage_product_review_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

		add_shortcode( 'product_reviews', array(
			$this,
			'display_reviews'
		) ); // create the shortcode to display reviews in frontend

		add_action( 'rest_api_init', array(
			$this,
			'register_rest_routes'
		) ); // register REST API routes/endpoints for 3rd party apps

	}

	// Enqueue custom admin styles
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'cprm-admin-style', CPRM_PLUGIN_URL. 'admin/assets/admin-style.css' );
	}

	// Register the custom post type for product reviews
	public function register_custom_post_type() {
		$labels = [
			'name'               => __( 'Product Reviews', 'custom-product-review' ),
			'singular_name'      => __( 'Product Review', 'custom-product-review' ),
			'menu_name'          => __( 'Product Reviews', 'custom-product-review' ),
			'name_admin_bar'     => __( 'Product Review', 'custom-product-review' ),
			'add_new'            => __( 'Add New', 'custom-product-review' ),
			'add_new_item'       => __( 'Add New Review', 'custom-product-review' ),
			'new_item'           => __( 'New Review', 'custom-product-review' ),
			'edit_item'          => __( 'Edit Review', 'custom-product-review' ),
			'view_item'          => __( 'View Review', 'custom-product-review' ),
			'all_items'          => __( 'All Reviews', 'custom-product-review' ),
			'search_items'       => __( 'Search Reviews', 'custom-product-review' ),
			'not_found'          => __( 'No reviews found.', 'custom-product-review' ),
			'not_found_in_trash' => __( 'No reviews found in Trash.', 'custom-product-review' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'cprm-product-review' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-star-filled',
			'supports'           => [ 'title', 'editor' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'product_review', $args ); // register the post type 'product_review'
	}

	// Add custom meta boxes 'Review Details' to the custom post type 'product_review'
	public function add_meta_boxes_for_reviews() {
		add_meta_box( 'cprm_meta_box', 'Review Details', array(
			$this,
			'render_meta_box_for_review'
		), 'product_review', 'normal', 'high' );
	}

	// Render the meta box fields
	public function render_meta_box_for_review( $post ) {
		wp_nonce_field( 'cprm_save_review_meta_box', 'cprm_review_meta_box_nonce' ); // add nonce token field to the meta box form.

		$product_id = get_post_meta( $post->ID, '_cprm_product_id', true ); // get the product id (saved meta value) associated to current review
		$rating     = get_post_meta( $post->ID, '_cprm_rating', true ); // get the rating (saved meta value) of the product associated to current review
		$reviewer   = get_post_meta( $post->ID, '_cprm_reviewer', true ); // get the reviewer name (saved meta value) of the product associated to current review

		// get all the published WooCommerce products
		$args     = array(
			'status' => 'publish',
			'limit'  => - 1,
			'return' => 'objects',
		);
		$products = wc_get_products( $args );

		echo '<div class="cprm-review-details options_group">';
		if ( ! empty( $products ) ) { // check first if there is published products in store/database
			// create options for product select
			$product_options = array();
			foreach ( $products as $product ) {
				$product_options[ $product->get_id() ] = $product->get_name(); // store all product details into $product_options array, product_id as key and product name as value
			}
			woocommerce_wp_select( array(
				'id'      => 'cprm_product_id',
				'label'   => __( 'Product', 'custom-product-review' ),
				'options' => $product_options,
				'class'   => 'wc-enhanced-select regular-text cprm-review-field',
				'value'   => $product_id,
			) ); // since our plugin is dependent on WooCommerce plugin, we can use WooCommerce library function 'woocommerce_wp_select' to render select field, rather than using custom select html
		} else {
			// if there is no published products in store/database, Display a disabled select field with message
			woocommerce_wp_select( array(
				'id'                => 'cprm_product_id',
				'label'             => __( 'Product', 'custom-product-review' ),
				'options'           => array( '' => __( 'No products found', 'custom-product-review' ) ),
				'class'             => 'regular-text disabled cprm-review-field',
				'custom_attributes' => array( 'disabled' => 'disabled' ),
			) ); // since our plugin is dependent on WooCommerce plugin, we can use WooCommerce library function 'woocommerce_wp_select' to render select field, rather than using custom select html
		}

		woocommerce_wp_select( array(
			'id'      => 'cprm_rating',
			'label'   => __( 'Rating', 'custom-product-review' ),
			'options' => array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5 ),
			'class'   => 'wc-enhanced-select regular-text cprm-review-field',
			'value'   => $rating,
		) ); // render select field for rating the product (1 to 5)


		woocommerce_wp_text_input( array(
			'id'    => 'cprm_reviewer',
			'label' => __( 'Reviewer\'s Name', 'custom-product-review' ),
			'class'   => 'cprm-text-field cprm-review-field',
			'value' => $reviewer,
		) ); // using the WooCommerce function to render text field, rather than using custom text field html
		echo '</div>';
	}

	// Save meta box data/fields securely, when the post is saved.
	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['cprm_review_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cprm_review_meta_box_nonce'], 'cprm_save_review_meta_box' ) ) { // verify nonce token to confirm, if the form submit is legit.
			return; // Exit if nonce is invalid or failed.
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {  // Verify if WordPress is currently auto saving the post. If then, don not proceed and return.
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {  // Check if the current user has permission/authority to edit the post. Return if fails.
			return;
		}

		if ( isset( $_POST['cprm_product_id'] ) ) { // if field cprm_product_id(i.e product id) is set, save it to post meta
			update_post_meta( $post_id, '_cprm_product_id', sanitize_text_field( $_POST['cprm_product_id'] ) );
		}
		if ( isset( $_POST['cprm_rating'] ) ) { // if the rating is valid and (between 1 and 5), save it to post meta.
			$rating = intval( $_POST['cprm_rating'] );
			if ( $rating >= 1 && $rating <= 5 ) {
				update_post_meta( $post_id, '_cprm_rating', $rating );
			}
		}
		if ( isset( $_POST['cprm_reviewer'] ) ) { // Save the reviewer name if it's provided in the form.
			update_post_meta( $post_id, '_cprm_reviewer', sanitize_text_field( $_POST['cprm_reviewer'] ) );
		}

		// this meta field is just to be used for filter out reviews(get reviews) based on product names (while using REST API endpoint)
		if ( isset( $_POST['cprm_product_id'] ) ) { // check if only the product id is set
			$product_name = $_POST['cprm_product_id'] ? get_the_title( $_POST['cprm_product_id'] ) : '';
			update_post_meta( $post_id, '_cprm_product_name', sanitize_text_field( $product_name ) ); // this meta field is just to be used for filter out reviews(get reviews) based on product names (while using REST API endpoint)
		}

	}


	public function show_custom_columns( $columns ) {
		unset( $columns['date'] ); // remove default date column
		$columns['cprm_product']  = __( 'Product', 'custom-product-review' );
		$columns['cprm_rating']   = __( 'Rating', 'custom-product-review' );
		$columns['cprm_reviewer'] = __( 'Reviewer', 'custom-product-review' );
		$columns['date']          = __( 'Date', 'custom-product-review' ); // re-add date at the end

		return $columns;
	}

	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'cprm_product':
				$product_id = get_post_meta( $post_id, '_cprm_product_id', true );
				echo $product_id ? get_the_title( $product_id ) : '-';
				break;

			case 'cprm_rating':
				$rating = get_post_meta( $post_id, '_cprm_rating', true );
				echo $rating ? esc_html( $rating ) . ' / 5' : '-';
				break;

			case 'cprm_reviewer':
				$reviewer = get_post_meta( $post_id, '_cprm_reviewer', true );
				echo esc_html( $reviewer ?: '-' );
				break;
		}
	}

	// function to display 5 latest reviews of WooCommerce Products.
	public function display_reviews() {
		// Start the output buffer to capture HTML output.
		ob_start();
		if ( file_exists( CPRM_PLUGIN_PATH . 'frontend-templates/content-latest-reviews.php' ) ) { //load the shortcode content from the template file
			require CPRM_PLUGIN_PATH . 'frontend-templates/content-latest-reviews.php';
		}

		// Return the buffered content.
		return ob_get_clean();
	}

	// Register custom REST API route
	public function register_rest_routes() {
		register_rest_route( 'cprm/v1', '/reviews', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'rest_api_get_reviews' ),
			'permission_callback' => '__return_true',
		) ); //set the API endpoint
	}

	// REST API callback to fetch reviews with optional filters
	public function rest_api_get_reviews( $request ) {
		$args = array(
			'post_type'      => 'product_review',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'meta_query'     => array()
		);

		// Optional rating filter (only if rating parameter is set)
		$rating = $request->get_param( 'rating' );
		if ( $rating ) {
			$args['meta_query'][] = [
				'key'     => '_cprm_rating',
				'value'   => absint( $rating ),
				'compare' => '=',
				'type'    => 'NUMERIC'
			];
		}

		// Optional product_name filter (only if product_name parameter is set)
		$product_name = $request->get_param( 'product_name' );
		if ( $product_name ) {
			$args['meta_query'][] = [
				'key'     => '_cprm_product_name',
				'value'   => $product_name,
				'compare' => '='
			];
		}

		$query   = new WP_Query( $args ); // create the query with above arguments.
		$reviews = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$review_id  = get_the_ID();
				$product_id = get_post_meta( $review_id, '_cprm_product_id', true );
				$product_id = absint( $product_id );
				$reviews[]  = array(
					'id'           => $review_id,
					'title'        => get_the_title(),
					'content'      => wp_strip_all_tags( get_the_content() ),
					'reviewer'     => sanitize_text_field( get_post_meta( $review_id, '_cprm_reviewer', true ) ),
					'rating'       => absint( get_post_meta( $review_id, '_cprm_rating', true ) ),
					'product_id'   => $product_id,
					'product_name' => sanitize_text_field( get_post_meta( $review_id, '_cprm_product_name', true ) ),
					'product_link' => $product_id ? get_permalink( $product_id ) : '',
				);
			}
			wp_reset_postdata(); // reset global post data to avoid interfering with other queries.
		}

		return rest_ensure_response( $reviews ); // return JSON response using WP REST helper.
	}
}

new Custom_Product_Review_Manager();