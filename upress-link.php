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
 * Version:           1.1.0
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
define( 'UPL_VERSION', '1.1.0' );
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

			//ajax actions
			add_action( 'wp_ajax_check_api_key', array( $this, 'upl_ajax_check_api_key' ) );
			add_action( 'wp_ajax_send_request', array( $this, 'upl_ajax_send_request' ) );

			add_action( 'wp_ajax_fix_media_upload_path', array( $this, 'upl_ajax_fix_media_upload_path' ) );
			add_action( 'wp_ajax_database_search_and_replace', array( $this, 'upl_ajax_database_search_and_replace' ) );
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
	function upl_ajax_fix_media_upload_path() {
		global $wpdb;

		$nonce = $_POST['_nonce'];
		if ( ! wp_verify_nonce( $nonce, $this->plugin_slug . '_ajax' ) ) { wp_die( 'Not authorized!' ); }
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'You do not have sufficient permissions to access this page.' ) ); }

		$result = $wpdb->update( $wpdb->options, array( 'option_value' => null ), array( 'option_name' => 'upload_path' ) );

		$response = array(
			'status' => ( $result === false ? 'fail' : 'success' ),
			'data' => ( $result === false ? $wpdb->last_error : $wpdb->last_query ),
			'debug' => $wpdb
		);

		$response = json_encode( $response );
		header( "Content-Type: application/json" );
		echo $response;
		exit;
	}
	function upl_ajax_database_search_and_replace() {
		global $wpdb;

		$nonce = $_POST['_nonce'];
		if ( ! wp_verify_nonce( $nonce, $this->plugin_slug . '_ajax' ) ) { wp_die( 'Not authorized!' ); }
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'You do not have sufficient permissions to access this page.' ) ); }

		$search = $_POST['replace_from'];
		$replace = $_POST['replace_to'];

		if( ! isset( $search ) || ! isset( $replace ) ) wp_die( 'Something is missing!' );

		$result = $wpdb->get_results(
			'SHOW TABLES',
			ARRAY_N
		);
		/*$result = $wpdb->query(
			$wpdb->prepare(
				'SHOW TABLES'
			)
		);*/
		$tables = array();
		foreach ( $result as $res )
		{
			$tables[] = $res[0];
		}

		$report = array( 'tables' => 0,
		                 'rows' => 0,
		                 'change' => 0,
		                 'updates' => 0,
		                 'start' => microtime( ),
		                 'end' => microtime( ),
		                 'errors' => array( ),
		);

		if ( is_array( $tables ) && ! empty( $tables ) ) {
			foreach( $tables as $table ) {
				$report[ 'tables' ]++;

				$columns = array( );

				$fields = $wpdb->get_results( 'DESCRIBE ' . $table, ARRAY_A );
				foreach( $fields as $column ) {
					$columns[ $column[ 'Field' ] ] = $column[ 'Key' ] == 'PRI' ? true : false;
				}

				$rows_result = $wpdb->get_row( 'SELECT COUNT(*) FROM ' . $table, ARRAY_N );
				$row_count = $rows_result[ 0 ];
				if ( $row_count == 0 )
					continue;

				$page_size = 50000;
				$pages = ceil( $row_count / $page_size );

				for( $page = 0; $page < $pages; $page++ ) {
					$current_row = 0;
					$start = $page * $page_size;
					$end = $start + $page_size;
					// Grab the content of the table
					$data = $wpdb->get_results( sprintf( 'SELECT * FROM %s LIMIT %d, %d', $table, $start, $end ), ARRAY_A );

					if ( ! $data )
						$report[ 'errors' ][] = mysql_error( );

					foreach( $data as $row ) {
						$report[ 'rows' ]++; // Increment the row counter
						$current_row++;

						$update_sql = array( );
						$where_sql = array( );
						$upd = false;

						foreach( $columns as $column => $primary_key ) {
							$edited_data = $data_to_fix = $row[ $column ];

							// Run a search replace on the data that'll respect the serialisation.
							$edited_data = $this->recursive_unserialize_replace( $search, $replace, $data_to_fix );

							// Something was changed
							if ( $edited_data != $data_to_fix ) {
								$report[ 'change' ]++;
								$update_sql[] = $column . ' = "' . esc_sql( $edited_data ) . '"';
								$upd = true;
							}

							if ( $primary_key )
								$where_sql[] = $column . ' = "' . esc_sql( $data_to_fix ) . '"';
						}

						if ( $upd && ! empty( $where_sql ) ) {
							$sql = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
							$result = $wpdb->get_results( $sql, ARRAY_A );
							if ( ! $result )
								$report[ 'errors' ][] = mysql_error( );
							else
								$report[ 'updates' ]++;

						} elseif ( $upd ) {
							$report[ 'errors' ][] = sprintf( '"%s" has no primary key, manual change needed on row %s.', $table, $current_row );
						}
					}
				}
			}
		}

		$report[ 'end' ] = microtime( );

		$errors = '';
		if ( ! empty( $report[ 'errors' ] ) && is_array( $report[ 'errors' ] ) ) {
			foreach( $report[ 'errors' ] as $error )
				$errors .=  $error . "\n";
		}
		$time = array_sum( explode( ' ', $report[ 'end' ] ) ) - array_sum( explode( ' ', $report[ 'start' ] ) );

		$response = array(
			'status' => ( $result === false ? 'fail' : 'success' ),
			'data' => ( $result === false ? $wpdb->last_error : $report ),
			'debug' => $wpdb,
			'time' => $time,
			'success_msg' => sprintf( __( 'Replace completed for the text "%s" which was replaced by "%s". %d tables scanned with %d total rows. Replacement took %f seconds.', $this->text_domain ),
				$search, $replace, $report[ 'tables' ], $report[ 'rows' ], $report[ 'change' ], $report[ 'updates' ], $time ),
			'errors_msg' => $errors
		);

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


	/**
	 * Take a serialised array and unserialise it replacing elements as needed and
	 * unserialising any subordinate arrays and performing the replace on those too.
	 *
	 * @param string $from       String we're looking to replace.
	 * @param string $to         What we want it to be replaced with
	 * @param array  $data       Used to pass any subordinate arrays back to in.
	 * @param bool   $serialised Does the array passed via $data need serialising.
	 *
	 * @return array	The original array with all elements replaced as needed.
	 */
	private function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {
		// some unseriliased data cannot be re-serialised eg. SimpleXMLElements
		try {

			if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
				$data = $this->recursive_unserialize_replace( $from, $to, $unserialized, true );
			}

			elseif ( is_array( $data ) ) {
				$_tmp = array( );
				foreach ( $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursive_unserialize_replace( $from, $to, $value, false );
				}

				$data = $_tmp;
				unset( $_tmp );
			}

			else {
				if ( is_string( $data ) )
					$data = str_replace( $from, $to, $data );
			}

			if ( $serialised )
				return serialize( $data );

		} catch( Exception $error ) {

		}

		return $data;
	}
}
new uPress_Link();
