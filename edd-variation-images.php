<?php 
/**
 * Plugin Name: Easy Digital Downloads - Price Variation Images
 * Version: 2.0.2
 * Plugin URI: http://www.github.com/atwellpub/edd-variation-images
 * Description: Change product image by selecting price variation. Add [edd-price-variation-images] shortcode to your download to render variation images.
 * Author:  Hudson Atwell
 * Author URI: http://www.twitter.com/atwellpub
 * Text Domain: edd-price-variation-images
 * Domain Path: assets/lang
*/


if (!class_exists('EDD_Price_Variation_Images') && class_exists('Easy_Digital_Downloads') ) {
	
	class EDD_Price_Variation_Images {
		
		/**
		*  Initiate Class
		*/
		public function __construct() {
			self::define_constants();
			self::load_hooks();
			self::load_text_domain();
		}
		
		/**
		*  Define Constants
		*/
		public static function define_constants() {
			define('EDD_PRICE_VARIATION_IMAGES_CURRENT_VERSION', '2.0.2' );
			define('EDD_PRICE_VARIATION_IMAGES_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
			define('EDD_PRICE_VARIATION_IMAGES_PATH', ABSPATH.'wp-content/plugins/'.plugin_basename( dirname(__FILE__) ).'/' );
			define('EDD_PRICE_VARIATION_IMAGES_PLUGIN_SLUG', 'edd-price-variation-images' );
			define('EDD_PRICE_VARIATION_IMAGES_STORE_URL', 'https://easydigitaldownloads.com/' );
			define('EDD_PRICE_VARIATION_IMAGES_ITEM_NAME', __( 'Screenshots' , 'edd-price-variation-images' ) );
		}
		
		/**
		*  Load Hooks & Filters 
		*/
		public static function load_hooks() {
			
			/* Enqueue frontend scripts */
			add_action( 'wp_enqueue_scripts' , array( __CLASS__ , 'enqueue_frontend_scripts' ) );
			
			/* enqueue admin scripts */
			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_admin_scripts' ) );

			/* add metaboxes to download post type */
			add_action( 'edd_download_price_option_row', array( __CLASS__ , 'add_image_upload' ) , 10 ,3 );
			
			/* add handler to save metabox data */
			add_action( 'save_post', array( __CLASS__ , 'save_variation_images' ) );
			
			/* register shortcode */			
			add_shortcode( 'edd-price-variation-images' , array( __CLASS__ , 'register_screenshots_shortcode' ) );
		}
	
		/**
		*  Enqueue admin css & javascript  
		*/
		public static function enqueue_admin_scripts() {
			wp_enqueue_script('edd-price-variation-images-admin', EDD_PRICE_VARIATION_IMAGES_URLPATH . 'assets/js/admin.js' , array('jquery'));
		}
		
		/**
		*  Enqueue frontend css and javascript
		*/
		public static function enqueue_frontend_scripts()	{

			//wp_enqueue_script("jquery");

			//wp_dequeue_script('jquery-prettyphoto');
			//wp_enqueue_script('jquery-prettyphoto', EDD_PRICE_VARIATION_IMAGES_URLPATH . 'assets/js/jquery-photo-gallery/jquery.prettyPhoto.js');
			
			wp_enqueue_script('edd-price-variation-images-frontend', EDD_PRICE_VARIATION_IMAGES_URLPATH . 'assets/js/frontend.js' , array('jquery'));
			wp_enqueue_style('edd-css-frontend', EDD_PRICE_VARIATION_IMAGES_URLPATH . 'assets/css/styles.css');
			
		}
		
		public static function get_variation_images( $post_id ) {
			$variation_images = get_post_meta($post_id,'edd_variation_images',true);
			$variation_images = json_decode($variation_images,true);
			return (is_array($variation_images)) ? $variation_images : array();
		}
		
		/**
		*  Adds image upload support to price variations
		*/
		public static function add_image_upload($post_id, $key, $args ) {
			global $post;
			
			$variation_images = self::get_variation_images($post_id);

			echo "<table id='edd-price-variation-images-container' style='width:100%'>";
			echo '	<tr>';
			echo '		<td>';
			echo '			Variation Image:';
			echo '		</td>';
			echo '		<td>';
			echo '			<input name="edd_variation_images['.$key.']"  id="edd_variation_images_'.$key.'" type="text" value="'.( isset($variation_images[$key]) ? $variation_images[$key] : '' ).'" style="display:inline-block;width:80%" />';
			echo '		</td>';
			echo '		<td>';
			echo '			<input id="upload_image_button"  style="display:inline-block;" class="edd_variation_images_'.$key.'"  type="button" value="'.__('Upload' , 'edd-price-variation-images').'" />';
			echo '		</td>';
			echo '	<tr>';
			echo "</table>";


			wp_nonce_field( basename( __FILE__ ), 'add_metaboxes_nonce' );	
	
		}
		
		/**
		*  Save screenshot data
		*/
		public static function save_variation_images( $post_id ) {
			global $post;
			
			if (!isset($post)) {
				return;
			}
			
			// check autosave
			if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) return $post_id;
			
			//don't save if only a revision
			if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
				return $post_id;
			}
			
			//don't save if only a revision
			if ( isset( $post->post_type ) && $post->post_type != 'download' )  {
				return $post_id;
			}
			
			$variation_images = $_POST['edd_variation_images'];
			update_post_meta( $post_id, 'edd_variation_images', json_encode($variation_images));

		}
		
		/**
		*  Register [edd-price-variation-images] shortcode
		*/
		public static function register_screenshots_shortcode() {
			global $post;
			
			if (!isset($post)) {
				return;
			}
			

			$variation_images = self::get_variation_images($post->ID);
			$default_price_id = edd_get_default_variable_price( $post->ID );
			$default_price_id = (!$default_price_id) ? 1 : $default_price_id;
			$html = "";

			if (count($variation_images)>0){

				$html .= "<ul class='edd-ul-price-variation-images' >";
				
				foreach ($variation_images as $key=>$image) {			
					$image = trim($image);
					
					$html .= "<li class='imge-item-{$key}' data-id='{$key}' ".( $key!=$default_price_id ? 'style="display:none"' : '' ) ." >
									<img src='{$image}'>
							  </li>";
				}

				$html .= "</ul>";

				return $html;
			}
		}
	
		/**
		*  Load text domain
		*/
		public static function load_text_domain() {
			load_plugin_textdomain( 'edd-price-variation-images' , false , dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		}
	}
	
	/**
	*  Load Class
	*/
	new EDD_Price_Variation_Images;
	
	/**
	*  Register legacy function
	*/
	function edd_add_screenshots() {
		EDD_Price_Variation_Images::register_screenshots_shortcode();
	}
}
