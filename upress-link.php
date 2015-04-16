<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://upress.co.il
 * @since             1.0.0
 * @package           Upress_Link
 *
 * @wordpress-plugin
 * Plugin Name:       uPress Link
 * Plugin URI:        http://upress.co.il
 * Description:       uPress Link is a companion plugin for the WordPress hosting manager at https://upress.co.il
 * Version:           1.0.0
 * Author:            Drubit Raid LTD.
 * Author URI:        http://upress.co.il
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       upress-link
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'UPL_VERSION', '1.0.0' );
define( 'UPL_PATH', dirname( __FILE__ ) );
define( 'UPL_PATH_INCLUDES', dirname( __FILE__ ) . '/includes' );
define( 'UPL_PATH_ADMIN', dirname( __FILE__ ) . '/admin' );
define( 'UPL_FOLDER', basename( UPL_PATH ) );
define( 'UPL_URL', plugins_url() . '/' . UPL_FOLDER );
define( 'UPL_URL_INCLUDES', UPL_URL . '/includes' );
define( 'UPL_URL_ADMIN', UPL_URL . '/admin' );

define( 'UPRESS_API_BASE', 'https://my.upress.co.il/api/wordpress/' );

class uPress_Link {
	public function __construct() {
		$this->plugin_name = "uPress Link";
		$this->plugin_slug = "upress-link";
		$this->text_domain = "upresslink";
		$this->options_name = "upress_options";

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'upl_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'upl_admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'upl_admin_enqueue_scripts' ) );

			add_action( 'wp_ajax_check_api_key', array( $this, 'upl_ajax_check_api_key' ) );
			add_action( 'wp_ajax_send_request', array( $this, 'upl_ajax_send_request' ) );
		}

		add_action( 'edit_post', array( $this, 'upl_edit_post_action' ) );
	}


	/* settings page config actions */
	/* ****************************************** */
	function upl_admin_menu() {
		add_options_page( _x($this->plugin_name, 'admin page title', $this->text_domain), _x($this->plugin_name, 'admin page menu item', $this->text_domain), 'manage_options', $this->plugin_slug, array( $this, 'upl_admin_view' ) );
	}
	function upl_admin_init() {
		register_setting( $this->plugin_slug, $this->options_name );
	}
	function upl_admin_enqueue_scripts( $hook ) {
		if( 'settings_page_' . $this->plugin_slug != $hook ) return;

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-effects-core' );
		wp_enqueue_script( 'wp-ajax-response' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'lc_switch', UPL_URL_ADMIN . '/js/lc_switch.min.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( $this->plugin_slug . '_js', UPL_URL_ADMIN . '/js/upress-link.js', array( 'jquery', 'json2' ), '1.0.0', true );
		wp_localize_script( $this->plugin_slug . '_js', 'upressAjax', array(
				'_nonce' => wp_create_nonce( $this->plugin_slug . '_ajax' ),
				'on' => __( 'ON' ),
				'off' => __( 'OFF' ),
				'requestSuccess' => __( 'Operation completed successfully', $this->text_domain )
			)
		);

		wp_register_style( 'lc_switch', UPL_URL_ADMIN . '/css/lc_switch.css', false, '1.0' );
		wp_register_style( $this->plugin_slug . '_css', UPL_URL_ADMIN . '/css/upress-link.css', false, '1.0.0' );
		wp_enqueue_style( 'lc_switch' );
		wp_enqueue_style( $this->plugin_slug . '_css' );
	}
	function upl_admin_view() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

		$options = get_option( $this->options_name );
		$opt_name = $this->options_name;
		$error_message = false;
		$package = false;
		$upress_available = true;

		//check api key
		$is_api_key_correct = false;
		$is_api_key_set = isset( $options['api_key'] ) && !empty( $options['api_key'] );
		if( $is_api_key_set ) {
			$response = wp_remote_post( UPRESS_API_BASE . 'apikey', array(
				'method' => 'POST',
				'timeout' => 45,
				'blocking' => true,
				'body' => array(
					'api_key' => $options['api_key']
				)
			) );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				$upress_available = false;
			} else {
				$data = json_decode( $response['body'] );
				if( $data->status === "fail" ) {
					$error_message = $data->message;
				} elseif( $data->status === "success" ) {
					$is_api_key_correct = true;
					$package = $data->data;
				}
			}
		}

		require_once( UPL_PATH_ADMIN . '/options.php' );
	}


	/* ajax actions */
	/* ****************************************** */
	function upl_ajax_check_api_key() {
		/*$response = array(
			'what'=>'foobar',
			'action'=>'update_something',
			'id'=>'1',
			'data'=>'<p><strong>Hello world!</strong></p>'
		);
		$xmlResponse = new WP_Ajax_Response($response);
		$xmlResponse->send();*/
	}
	function upl_ajax_send_request() {
		$nonce = $_POST['_nonce'];
		if ( ! wp_verify_nonce( $nonce, $this->plugin_slug . '_ajax' ) ) wp_die ( 'Not authorized!');
		if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

		$api_key = $_POST['api_key'];
		$api_action = $_POST['api_action'];
		$value = $_POST['value'];
		$api_action_type = $_POST['type'];

		if( ! isset( $api_key ) || ! isset( $api_action ) || ! isset( $value ) || ! isset( $api_action_type ) ) wp_die( 'Something is missing!' );

		$apiresponse = wp_remote_post( UPRESS_API_BASE . $api_action, array(
			'method' => 'POST',
			'timeout' => 45,
			'blocking' => true,
			'body' => array(
				'api_key' => $api_key,
				'value' => $value,
				'type' => $api_action_type
			)
		) );

		$response = array();
		$data = isset( $apiresponse['body'] ) ? json_decode( $apiresponse['body'] ) : null;
		if ( ! $data || is_wp_error( $apiresponse ) || ( $data && $data->status == "fail" ) ) {
			$response = array(
				'status' => 'fail',
				'request' => array(
					'action' => $api_action,
					'value' => $value,
					'type' => $api_action_type
				),
				'data' => json_decode( $apiresponse['body'] ),
				'extradata' => $apiresponse
			);
		} else {
			$response = array(
				'status' => 'success',
				'request' => array(
					'action' => $api_action,
					'value' => $value,
					'type' => $api_action_type
				),
				'data' => $data,
				'extradata' => $apiresponse
			);
		}

		$response = json_encode( $response );
		header( "Content-Type: application/json" );
		echo $response;
		exit;
	}






	/* action hooks */
	/* ****************************************** */
	function upl_edit_post_action( $post_ID ) {
		$options = get_option( $this->options_name );
		if( $options['clear_post_cache'] ) {
			wp_remote_post( UPRESS_API_BASE . 'clear_post_page_cache', array(
				'method' => 'POST',
				'timeout' => 45,
				'blocking' => true,
				'body' => array(
					'api_key' => $options['api_key'],
					'value' => $post_ID,
					'type' => 'set',
					'permalink' => get_permalink( $post_ID ),
					'home_url' => home_url()
				)
			) );
		}
	}
}
new uPress_Link();
