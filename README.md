# custom-product-review-manager
A Plugin that manages the custom reviews/ratings for WooCommerce Products.

## Installation
(Note : Install and activate WooCommerce plugin before installing 'Custom Product Review Manager' plugin )
1. Upload the plugin files to the `/wp-content/plugins/` directory.
2. Activate the plugin through the WordPress admin dashboard.
3. Navigate to "Product Reviews" from the admin dashboard.

## Shortcode Usage
Use the shortcode `[product_reviews]` to display latest reviews on any page.

## REST API Usage
Basic endpoint URL: https://yourdomain.com/wp-json/cprm/v1/reviews
Optional rating filter: https://yourdomain.com/wp-json/cprm/v1/reviews?rating=5

curl https://yourdomain.com/wp-json/cprm/v1/reviews