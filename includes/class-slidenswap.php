<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class slidenswap {

	private static $_instance = null;

	public $settings = null;
	public $_version;
	public $_token;
	public $file;
	public $dir;
	public $assets_dir;
	public $assets_url;

	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'slidenswap';
		
		global $wpdb;
		$this->_table_name = $wpdb->prefix . $this->_token;

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );

		// Actions
		add_action( 'admin_menu', array( $this, 'slidenswap_menu' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		add_shortcode( 'slidenswap', array( $this, 'slidenswap_shortcode' ) );

	}

	public function slidenswap_menu() {
		add_menu_page( 'SlideNSwap Settings', 'SlideNSwap Settings', 'administrator', __FILE__, array( $this, 'slidenswap_settings_page' ) );
	}

	private function get_slide( $slide_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->_table_name WHERE `ID` = %d", $slide_id ) );
	}
	private function get_slides() {
		global $wpdb;
		$slides = $wpdb->get_results( "SELECT * FROM $this->_table_name" );
		return $slides;
	}

	private function update_slides( $slides ) {
		global $wpdb;
		$slides_ids = [];
		$sql = "INSERT INTO $this->_table_name (ID, name, images) VALUES ";
		foreach ( $slides as $key => $slide ) {
			$slide['ID'] = intval( sanitize_key( $key ) );
			$slides_ids[] = $slide['ID'];
			$slide['name'] = sanitize_text_field( $slide['name'] );
			$slide['images'] = sanitize_text_field( $slide['images'] );
			$slide['images'] = empty( $slide['images'] ) ? 'NULL' : $slide['images'];
			$sql .= $wpdb->prepare( '(%d, %s, %s),', $slide['ID'], $slide['name'], $slide['images'] );
		}
		$sql = rtrim( $sql, ',' );
		$sql .= " ON DUPLICATE KEY UPDATE name=VALUES(name), images=VALUES(images)";
		$wpdb->query( $sql );
		$wpdb->query( "DELETE FROM $this->_table_name WHERE `ID` NOT IN (" . implode( ',', $slides_ids ) . ")" );
	}

	public function slidenswap_settings_page() { 
		$nonce_action = 'slidenswap_settings';
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], $nonce_action ) && isset( $_POST['slides'] ) ) {
			$this->update_slides( $_POST['slides'] ); ?>
			<script type="text/javascript">
			window.location='<?php echo ( admin_url( 'admin.php?page=slidenswap/includes/class-slidenswap.php' ) ); ?>';
			</script>
		<?php }	?>
		<h2><?php _e( 'SlideNSwap Settings' , 'slidenswap' ) ?></h2>
		<form method="POST" class="slidenswap_form">
			<?php wp_nonce_field( $nonce_action ) ?>
			<div id="slides_wrapper">
			<?php $i = 0 ?>
			<?php foreach ( $this->get_slides() as $slide ): ?>
				<fieldset class="single_slide">
					<label>
						<span>Name</span>
						<input type="text" class="required" name="slides[<?php echo intval( $slide->ID ) ?>][name]" value="<?php esc_attr_e( $slide->name ) ?>" />
					</label>
					<label>
						<?php
						$img_name = 'slides[' . intval( $slide->ID ) . '][images]';
						$img_id = 'slides_' . intval( $slide->ID ) . '_images';
						$images = explode( ',', $slide->images );
						$images = empty( $images ) ? [0] : $images;
						$j = 0;
						?>
						<?php foreach ( $images as $image ): ?>
							<img id="<?php echo $img_id ?>_preview-<?php echo $j ?>" class="image_preview" src="<?php echo wp_get_attachment_thumb_url( $image ) ?>" />
							<?php $j++; ?>
						<?php endforeach; ?>
						<input id="<?php echo $img_id ?>_button" type="button" data-uploader_title="<?php _e( 'Add images' , 'slidenswap' ) ?>" data-uploader_button_text="<?php _e( 'Use images' , 'slidenswap' ) ?>" class="image_upload_button button" value="<?php _e( 'Add images' , 'slidenswap' ) ?>" />
						<input id="<?php echo $img_id ?>_delete" type="button" class="image_delete_button button" value="<?php _e( 'Remove image' , 'slidenswap' ) ?>" />
						<input id="<?php echo $img_id ?>" class="image_data_field" type="hidden" name="<?php echo $img_name ?>" value="<?php echo $slide->images ?>"/><br />
					</label>
					<label class="remove_slide_wrapper">
						<input type="button" class="button remove_slide" value="Remove Gallery" />
					</label>
					<div class="shortcode_tip">To Insert in post: <strong>[slidenswap id="<span class="slider_id"><?php echo $slide->ID ?></span>" width="200px" height="200px"]</strong> - make sure Width & Height are smaller than the images</div>
				</fieldset>
			<?php $i++; endforeach; ?>
			</div>
			<input type="button" value="Add Gallery" class="button" id="add_slide">
			<label class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></label>
		</form>
	<?php }

	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	}

	public function enqueue_scripts () {
		wp_register_script( $this->_token . 'jquery-bgpos', esc_url( $this->assets_url ) . 'js/jquery.bgpos.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-slidenswap-plugin', esc_url( $this->assets_url ) . 'js/slidenswap.js', array( 'jquery', $this->_token . 'jquery-bgpos' ), $this->_version );
		wp_enqueue_script( $this->_token . '-slidenswap-plugin' );
	}

	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	}

	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );
		wp_enqueue_media();
		wp_register_script( $this->_token . '-settings', esc_url( $this->assets_url ) . 'js/settings.js', array( 'farbtastic', 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-settings' );
	}

	public function activate () {
		global $wpdb;
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}
		$sql = "CREATE TABLE $this->_table_name (
		  ID mediumint(9) NOT NULL AUTO_INCREMENT,
		  name tinytext NOT NULL,
		  images text NULL,
		  UNIQUE KEY ID (ID)
		) $charset_collate;";

		// Executing the query
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		//Insert first slide
		$wpdb->query( $wpdb->prepare( "INSERT INTO `$this->_table_name` (`name`, `images`) VALUES (%s, %s)", __( 'Example Slide', 'slidenswap' ), '' ) );

		$this->_log_version_number();

	}

	public function deactivate () {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS $this->_table_name" );
		delete_option( $this->_token . '_version' );
	}

	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	}

	public function slidenswap_shortcode ( $args ) {
		if ( ! isset( $args['id'] ) ) {
			return false;
		}
		$controllers = 'true';
		if ( isset( $args['controllers'] ) && ( $args['controllers'] == 'off' || $args['controllers'] == 'false' ) ) {
			$controllers = 'false';
		}
		$slide = $this->get_slide( $args['id'] );
		?>
		<script type="text/javascript">
			controllers = <?php echo $controllers ?>;
		</script>
		<div class="swapWrapper" style="<?php echo isset ( $args['height'] ) ? 'height:' . $args['height'] . ';' : '' ?><?php echo isset ( $args['width'] ) ? 'width:' . $args['width'] . ';' : '' ?>" >
			<?php foreach ( explode(',', $slide->images) as $image ): ?>
				<?php echo wp_get_attachment_image( $image, 'full' ); ?>
			<?php endforeach; ?>
		</div>
	<?php }

	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	}

}