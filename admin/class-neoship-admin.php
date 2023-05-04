<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://www.neoship.sk
 * @since 1.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Neoship
 * @subpackage Neoship/admin
 * @author     IT <it@neoship.sk>
 */
class Neoship_Admin {


	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Neoship settings from db
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array    $settings    The current neoship settings.
	 */
	private $settings;

	/**
	 * NeoshipApi
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Neoship_Api    $api    Neoship api instance.
	 */
	private $api;

	/**
	 * NeoshipApi
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    array    $gls_shipping_methods    Array of gls couriers.
	 */
	private $gls_shipping_methods;

    /**
     * NeoshipApi
     *
     * @since  2.0.0
     * @access private
     * @var    array    $packeta_shipping_methods    Array of packeta couriers.
     */
    private $packeta_shipping_methods;

    /**
     * NeoshipApi
     *
     * @since  2.0.0
     * @access private
     * @var    array    $K123_shipping_methods    Array of 123 couriers.
     */
    private $k123_shipping_methods;

	/**
	 * NeoshipApi
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    array    $dpd_shipping_methods    Array of DPD couriers.
	 */
	private $dpd_shipping_methods;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 			= $plugin_name;
		$this->version     			= $version;
		$this->settings    			= get_option( 'neoship_login' );
		$this->api         			= new Neoship3_Api();
		$this->gls_shipping_methods = [ 'neoship_glscourier', 'neoship_glsparcelshop' ];
		$this->packeta_shipping_methods = [ 'neoship_packeta' ];
		$this->k123_shipping_methods = [ 'neoship_123' ];
		$this->dpd_shipping_methods = [ 'neoship_dpdcourier', 'neoship_dpdparcelshop' ];

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Neoship_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Neoship_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( 'admin_page_neoship-export' === $screen->id ) {
			wp_enqueue_style( 'woocommerce_stylesheet', plugin_dir_url( dirname( __FILE__ ) ) . '../woocommerce/assets/css/admin.css', array(), '3.8.1', 'all' );
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/neoship-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Neoship_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Neoship_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();
		if ( 'admin_page_neoship-export' === $screen->id ) {
			wp_enqueue_script( 'wc-backbone-modal', plugin_dir_url( dirname( __FILE__ ) ) . '../woocommerce/assets/js/admin/backbone-modal.min.js', array( 'backbone', 'jquery' ), false, false );
			wp_enqueue_script( 'wc-orders', plugin_dir_url( dirname( __FILE__ ) ) . '../woocommerce/assets/js/admin/wc-orders.min.js', array( 'underscore', 'backbone', 'wp-util' ), false, false );
		}

		if ( 'edit-shop_order' === $screen->id ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/neoship-sticker-position.js', array(), $this->version, false );
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/neoship-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Settings page.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function settings_page() {

		add_options_page( __( 'Neoship settings' ), __( 'Neoship' ), 'manage_options', 'neoship-settings-page', array( $this, 'neoship_ptml' ) );

		add_submenu_page(
			null,
			__( 'Neoship export' ),
			'',
			'manage_woocommerce',
			'neoship-export',
			array( $this, 'export_packages_to_neoship3_step' ),
			null
		);
	}

	/**
	 * Settings init.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function settings_init() {
		register_setting( 'neoship-settings', 'neoship_login' );

		add_settings_section(
			'neoship-settings-section',
			__( 'Login settings', 'neoship' ),
			array( $this, 'neoship_settings_section_callback' ),
			'neoship-settings'
		);

		add_settings_field(
			'clientid',
			__( 'Username', 'neoship' ),
			array( $this, 'neoship_settings_id_callback' ),
			'neoship-settings',
			'neoship-settings-section'
		);

		add_settings_field(
			'clientsecret',
			__( 'Password', 'neoship' ),
			array( $this, 'neoship_settings_secret_callback' ),
			'neoship-settings',
			'neoship-settings-section'
		);

	}

	/**
	 * Not filled login credentials callback.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function neoship_settings_section_callback() {
		esc_html_e( 'Please fill data which you receive from neoship', 'neoship' );
	}

	/**
	 * Render client id field.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function neoship_settings_id_callback() {
		echo '<input id="clientid" autocomplete="off" class="regular-text" type="text" name="neoship_login[clientid]" value="' . esc_html( get_option( 'neoship_login' )['clientid'] ) . '">';
	}

	/**
	 * Render client secret field.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function neoship_settings_secret_callback() {
		echo '<input id="clientsecret" autocomplete="off" class="regular-text" type="password" name="neoship_login[clientsecret]" value="' . esc_html( get_option( 'neoship_login' )['clientsecret'] ) . '">';
	}

	/**
	 * Render option page.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function neoship_ptml() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->api->login( true );

		?>
			<h1>Neoship</h1>
			<form action='options.php' method='post'>
		<?php
		settings_fields( 'neoship-settings' );
		do_settings_sections( 'neoship-settings' );
		submit_button();
		?>
			</form>
		<?php
	}

	/**
	 * Add neoship bulk actions.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array $actions Default actions.
	 */
	public function neoship_order_list_bulk_actions( $actions ) {
		$actions['neoship_acceptance_protocol']          = __( 'Preberací protokol SPS', 'neoship' );
		$actions['neoship_export']                       = __( 'Export to Neoship', 'neoship' ) . ' (' . $this->api->get_user_credit() . '€)';

		$actions['neoship3_print_stickers_sps']               = __( 'Tlač štítkov SPS (PDF)', 'neoship' );
		if ( get_option( 'neoship_has_gls' ) ) {
			$actions['neoship3_print_stickers_gls'] = __( 'Tlač štítkov GLS (PDF)', 'neoship' );
		}
		if ( get_option( 'neoship_has_packeta' ) ) {
			$actions['neoship3_print_stickers_packeta'] = __( 'Tlač štítkov Packeta (PDF)', 'neoship' );
		}
		if ( get_option( 'neoship_has_123' ) ) {
			$actions['neoship3_print_stickers_123'] = __( 'Tlač štítkov 123 kuriér (PDF)', 'neoship' );
			$actions['neoship3_acceptance_protocol_123']          = __( 'Preberací protokol 123 kuriér', 'neoship' );
		}
		if ( get_option( 'neoship_has_dpd' ) ) {
			$actions['neoship3_print_stickers_dpd'] = __( 'Tlač štítkov DPD (PDF)', 'neoship' );
		}

		return $actions;
	}

	/**
	 * Handle export packages.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param string $redirect_to Redirect url name.
	 * @param string $action Neoship action name.
	 * @param array  $posts_ids Posts ids.
	 */
	public function handle_bulk_action_export_packages_to_neoship( $redirect_to, $action, $posts_ids ) {

        if ( 'neoship_export' !== $action ) {
			return $redirect_to;
		}

		return add_query_arg(
			array(
				'posts_ids' => implode( ',', $posts_ids ),
			),
			admin_url( 'admin.php?page=neoship-export' )
		);

	}

	/**
	 * Handle stickers print.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param string $redirect_to Redirect url name.
	 * @param string $action Neoship action name.
	 * @param array  $posts_ids Posts ids.
	 */
	public function handle_bulk_action_print_stickers( $redirect_to, $action, $posts_ids ) {
        
		$allowed_values = array(
			'neoship_print_stickers_gls', 
			'neoship_print_stickers_gls_position_1', 
			'neoship_print_stickers_gls_position_2', 
			'neoship_print_stickers_gls_position_3', 
			'neoship_print_stickers_gls_position_4',
			'neoship_print_stickers', 
			'neoship_print_stickers_position_1', 
			'neoship_print_stickers_position_2', 
			'neoship_print_stickers_position_3', 
			'neoship_print_stickers_position_4', 
			'neoship_print_stickers_zebra_102x152', 
			'neoship_print_stickers_zebra_80x214',
            
            'neoship3_print_stickers_sps',
            'neoship3_print_stickers_sps_position_1',
            'neoship3_print_stickers_sps_position_2',
            'neoship3_print_stickers_sps_position_3',
            'neoship3_print_stickers_sps_position_4',
            'neoship3_print_stickers_gls',
            'neoship3_print_stickers_gls_position_1',
            'neoship3_print_stickers_gls_position_2',
            'neoship3_print_stickers_gls_position_3',
            'neoship3_print_stickers_gls_position_4',
            'neoship3_print_stickers_packeta',
            'neoship3_print_stickers_packeta_position_1',
            'neoship3_print_stickers_packeta_position_2',
            'neoship3_print_stickers_packeta_position_3',
            'neoship3_print_stickers_packeta_position_4',
            'neoship3_print_stickers_123',
            'neoship3_print_stickers_123_position_1',
            'neoship3_print_stickers_123_position_2',
            'neoship3_print_stickers_123_position_3',
            'neoship3_print_stickers_123_position_4',
			'neoship3_print_stickers_dpd',
			'neoship3_print_stickers_dpd_position_1',
			'neoship3_print_stickers_dpd_position_2',
			'neoship3_print_stickers_dpd_position_3',
			'neoship3_print_stickers_dpd_position_4',
		);

		if ( ! in_array( $action, $allowed_values, true ) ) {
			return $redirect_to;
		}

		$template = 0;
		switch ( $action ) {
			case 'neoship_print_stickers_zebra_102x152':
				$template = 1;
				break;
			case 'neoship_print_stickers_zebra_80x214':
				$template = 2;
				break;
		}


		//VERSION NEOSHIP3 SEPARATELLY FOR ALL SHIPPERS ONE BY ONE
//            $position = (int)filter_var($action, FILTER_SANITIZE_NUMBER_INT);
		$position = (int)explode('_',$action)[5];
		$sticker_position = $position ? $position : '1';

		$carrier = explode("_" , $action)[3];

		$reference_numbers = [];
		foreach ($posts_ids as $post_id) {
			$order = wc_get_order(intval($post_id))->get_data();

			$shipping_info  = $order['shipping_lines'][ key($order['shipping_lines']) ]->get_data();
			if (in_array( $shipping_info['method_id'], $this->gls_shipping_methods )) {
				$shipping_method = 'gls';
			} elseif (in_array( $shipping_info['method_id'], $this->packeta_shipping_methods )){
				$shipping_method = 'packeta';
			} elseif (in_array( $shipping_info['method_id'], $this->k123_shipping_methods )) {
				$shipping_method = '123';
			} elseif (in_array( $shipping_info['method_id'], $this->dpd_shipping_methods )) {
				$shipping_method = 'dpd';
			} else {
				$shipping_method = 'sps';
			}

			if ($carrier == $shipping_method){
				$reference_numbers[] = $order['number'];
			}

		}

		$location = add_query_arg(
			array(
				'neoship_print_sticker_export' => 1,
				'reference_numbers' => $reference_numbers,
				'position' => $sticker_position,
				'_wpnonce' => wp_create_nonce('neoship_notice_nonce'),
			),
			admin_url('edit.php?post_type=shop_order')
		);

		wp_safe_redirect(($location));
		exit();

	}

	/**
	 * Handle acceptance protocol print
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param string $redirect_to Redirect url name.
	 * @param string $action Neoship action name.
	 * @param array  $posts_ids Posts ids.
	 */
	public function handle_bulk_action_print_acceptance_protocol( $redirect_to, $action, $posts_ids ) {


        $allowed_values = array(
            'neoship_acceptance_protocol',
            'neoship3_acceptance_protocol_123'
        );

        if ( ! in_array( $action, $allowed_values, true ) ) {
            return $redirect_to;
        }

		$location = add_query_arg(
			array(
				'neoship3_acceptance_protocol' => ($action == 'neoship3_acceptance_protocol_123')  ? '123' : 'SPS',
				'_wpnonce'       			   => wp_create_nonce( 'neoship_notice_nonce' ),
			),
			admin_url( 'edit.php?post_type=shop_order' )
		);

		wp_safe_redirect( ( $location ) );
		exit();

        $reference_numbers = [];
        foreach ($posts_ids as $post_id){
            $order = wc_get_order( intval($post_id))->get_data();
            $reference_numbers[] = $order['number'];
        }

		$this->api->print_acceptance_protocol( $reference_numbers );

	}

	/**
	 * Handle neoship bulk action admin notice.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function neoship_bulk_action_admin_notice() {
		
		if ( ! empty( $_REQUEST['neoship3_acceptance_protocol'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			if ( false !== wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'neoship_notice_nonce' ) ) {

				$protocol = $this->api->print_acceptance_protocol($_REQUEST['neoship3_acceptance_protocol']);

				if ( ! empty( $protocol['errors'] ) ) {
					echo '<div class="notice notice-error is-dismissible"><p>';
					echo '<p>';
					echo '<strong>';
					printf(  esc_html__( $protocol['errors'] ) );
					echo '</strong>';
					echo '</p>';
					echo '</div>';
				}

				if ( $protocol['protocol'] != '' ) {
					echo '<div class="notice notice-success is-dismissible"><p id="neoship_download_sticker_link" >';
					echo 
					'<script>
						var link = document.createElement("a");
						link.classList.add("button");
						link.classList.add("action");
						link.innerHTML = "<strong>' . __( 'Opätovne stiahnuť vygenerované štítky', 'neoship' ) . '</strong>";
						link.download = "stickers.pdf";
						link.href = `data:application/pdf;base64,' . $protocol['protocol'] . '`;
						link.click();
						document.getElementById("neoship_download_sticker_link").appendChild(link);
					</script>';
					echo '</p></div>';
				}
			}
		}

		if ( ! empty( $_REQUEST['neoship_print_sticker_export'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			if ( false !== wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'neoship_notice_nonce' ) ) {
				

				$labels_errors = $this->api->print_sticker( $_REQUEST['reference_numbers'], intval( $_REQUEST['position'] ) );
				$tmp_errors = [];
				if (array_key_exists('errors', $labels_errors)){
					if (is_array($labels_errors['errors'])) {
						foreach ($labels_errors['errors'] as $value) {
							if (isset($value['errors'])){
								$tmp_errors[ $value['reference_number'] ] = implode( ',', $value['errors'] );
							}
						}
					} else {
						$tmp_errors[] = $labels_errors['errors'];
					}
				}
				$labels_errors['errors'] = $tmp_errors;


				$labels_errors = isset( $labels_errors[ 'message' ] ) ? [
					'errors' => [
						$labels_errors[ 'message' ]
					]
				] : $labels_errors;

				$printed_labels = array_key_exists('tracking', $labels_errors) ? $labels_errors['tracking'] : [];
				if (count($_REQUEST['reference_numbers']) > count($printed_labels)){
				    $printed_refs = array_map(function($a){return $a['reference_number'];}, $printed_labels);
				    $diff = array_filter($_REQUEST['reference_numbers'], function($rr) use($printed_refs) {return !in_array($rr, $printed_refs);});
                    echo '<div class="notice notice-error is-dismissible">';
                    $message = sprintf( _n( '%d label was not printed. Check if it is printed already', '%d labels were not printed. Check if they are printed already', count($diff), 'neoship' ), count($diff) );
                    echo '<strong>';
                    echo esc_html( $message );
                    echo '</strong>: ' . esc_html(implode(', ', $diff));
                    echo '</div>';
                }

				if ( count( $labels_errors['errors'] ) > 0 ) {
					echo '<div class="notice notice-error is-dismissible">';

					foreach ( $labels_errors['errors'] as $key => $value ) {
						$value = is_array( $value ) ? implode( ', ', $value ) : $value;
						echo '<p>';
						echo '<strong>';
						printf(  esc_html__( 'Order %s', 'neoship' ), $key );
						echo '</strong>: ' . esc_html( $value );
						echo '</p>';
					}
					echo '</div>';
				}

				if ( $labels_errors['labels'] != '' ) {
					echo '<div class="notice notice-success is-dismissible"><p id="neoship_download_sticker_link" >';
					echo 
					'<script>
						var link = document.createElement("a");
						link.classList.add("button");
						link.classList.add("action");
						link.innerHTML = "<strong>' . __( 'Opätovne stiahnuť vygenerované štítky', 'neoship' ) . '</strong>";
						link.download = "stickers.pdf";
						link.href = `data:application/pdf;base64,' . $labels_errors['labels'] . '`;
						link.click();
						document.getElementById("neoship_download_sticker_link").appendChild(link);
					</script>';
					echo '</p></div>';
				}
			}
		}

		if ( ! empty( $_REQUEST['neoship_export'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {

			if ( false !== wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'neoship_notice_nonce' ) ) {

				$success = isset( $_REQUEST['success'] ) === true ? json_decode( sanitize_text_field( ( wp_unslash( $_REQUEST['success'] ) ) ), true ) : array();
				$failed  = isset( $_REQUEST['failed'] ) === true ? json_decode( sanitize_text_field( ( wp_unslash( $_REQUEST['failed'] ) ) ), true ) : array();

				echo '<div class="notice notice-success is-dismissible"><p>';
				/* translators: %d: number of orders */
				$output = sprintf( _n( '%d order was exported', '%d orders was exported', array_sum(array_map("count", $success)), 'neoship' ), array_sum(array_map("count", $success)) );
				echo esc_html( $output );
				if ( count( $failed ) === 0 ) {
					echo ' ';
					/* translators: %d: number of orders */
					$output = sprintf( _n( '%d order was not exported', '%d orders was not exported', 0, 'neoship' ), 0 );
					echo esc_html( $output );

				}
				echo '</p></div>';

				if ( count( $failed ) > 0 ) {
					echo '<div class="notice notice-error is-dismissible"><p>';
					/* translators: %d: number of orders */
					$output = sprintf( _n( '%d order was not exported', '%d orders was not exported', count($failed), 'neoship' ), count($failed) );
					echo esc_html( $output );
					echo '</p>';
					foreach ( $failed as $key => $value ) {
						echo '<p>';
						/* translators: %d: number of orders */
						//parsing errors are done after creating packages, because of 502 BAD GATEWAY, the json result of our api is huge for url encoding...
						//that is the reason, why are sending through the url only needed information
						printf('<strong>' . esc_html__('Order %d', 'neoship') . '</strong>: ' . esc_html($value), intval($key));
						echo '</p>';
					}

					echo '</div>';
				}
			}
		}
		if ( ! empty( $_REQUEST['neoship_error'] ) && ! empty( $_REQUEST['error'] ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>';
			echo esc_html( sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) );
			echo '</p></div>';
		}
	}

	/**
	 * Register post statuses.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function register_neoship_post_statuses() {
		register_post_status(
			'wc-export-to-neoship',
			array(
				'label'                     => __( 'Exported to Neoship', 'neoship' ),
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %d: number of orders */
				'label_count'               => _n_noop( 'Exported to Neoship <span class="count">(%s)</span>', 'Exported to Neoship <span class="count">(%s)</span>', 'neoship' ),
			)
		);
	}

	/**
	 * Add neoship order statuses.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array $order_statuses Order statuses.
	 */
	public function add_neoship_order_statuses( $order_statuses ) {
		$order_statuses['wc-export-to-neoship'] = __( 'Exported to Neoship', 'neoship' );
		return $order_statuses;
	}

	/**
	 * Add order list actions.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array    $actions Order actions.
	 * @param wc_order $order Order.
	 */
	public function add_neoship_order_list_actions( $actions, $order ) {
		if ( $order->has_status( array( 'export-to-neoship' ) ) ) {
			$order_number   = method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : $order->get_data()['number'];
			$order_obj      = $order->get_data();
			$shipping_info  = $order_obj['shipping_lines'][ key($order_obj['shipping_lines']) ]->get_data();
			$is_gls_package = in_array( $shipping_info['method_id'], $this->gls_shipping_methods );

			$tracking_url = NEOSHIP3_API_URL . '/all/tracking/user/' . $this->settings['userid'] . '/' . $order_number;

			$actions['neoship-tracking'] = array(
				'url'    => $tracking_url,
				'name'   => __( 'Tracking', 'neoship' ),
				'action' => 'neoship-tracking',
			);
		}
		return $actions;
	}

	/**
	 * Neoship export page.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function export_packages_to_neoship_step() {

		if ( isset( $_POST['packages'] ) && is_array( $_POST['packages'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'neoship-form-nonce' ) ) {

			$user_address 		= $this->api->get_user_address();
			$states       		= $this->api->get_states_ids();
			$currencies   		= $this->api->get_currencies_ids();
			$order_number_to_id = [];
			$packages 			= array();
			$gls_packages 		= array();

			foreach ( $_POST['packages'] as $pkg ) {
				$order_obj      = wc_get_order( intval( $pkg['id'] ) );
				$order      	= $order_obj->get_data();
				$shipping_info  = $order['shipping_lines'][ key($order['shipping_lines']) ]->get_data();
				$is_gls_package = in_array( $shipping_info['method_id'], $this->gls_shipping_methods );
				if ( isset( $pkg['shipper'] ) && '' !== $pkg['shipper'] ) {
					$is_gls_package = $pkg['shipper'] === 'gls';
				}
				$parcelshop     = $order_obj->get_meta('_parcelshop_id');
				$glsparcelshop  = $order_obj->get_meta('_glsparcelshop_id');

				$package                        = array();
				$package['sender']              = $user_address;
				$package['receiver']            = array();
				$package['receiver']['name']    = $order['shipping']['first_name'] . ' ' . $order['shipping']['last_name'];
				if ( ! $is_gls_package ) {
					$package['receiver']['company'] = $order['shipping']['company'];
				}
				$package['receiver']['street']  = $order['shipping']['address_1'];
				$package['receiver']['city']    = $order['shipping']['city'];
				$package['receiver']['zIP']     = $order['shipping']['postcode'];
				$package['receiver']['email']   = $order['billing']['email'];
				$package['receiver']['phone']   = $order['billing']['phone'];
				$package['receiver']['state']   = $states[ $order['shipping']['country'] ];
				$package['variableNumber']      = $order['number'];
                $order_number_to_id[$order['number']] = $order['id'];

				if ( in_array( $order['payment_method'], [ 'cod', 'dobirka' ] ) ) {
					$package['cashOnDeliveryPrice'] = sanitize_text_field( $pkg['cod'] );
					if ( ! $is_gls_package ) {
						$package['cashOnDeliveryCurrency'] = $currencies[ $order['currency'] ];
					}
				} else {
					$package['cashOnDeliveryPrice'] = '';
					if (! $is_gls_package) {
						$package['cashOnDeliveryCurrency'] = '';
					}
				}

				$package['insurance'] = sanitize_text_field( $pkg['insurance'] );
				
				if ( ! $is_gls_package ) {
					
					if ( isset( $pkg['holddelivery'] ) ) {
						$package['holdDelivery'] = true;
					}

					$package['cashOnDeliveryPayment'] = '';
					$package['insuranceCurrency'] = $currencies['EUR'];

					if ( isset( $pkg['saturdaydelivery'] ) ) {
						$package['saturdayDelivery'] = true;
					}

					$package['express'] = '' !== $pkg['deliverytype'] ? intval( $pkg['deliverytype'] ) : null;
					
					if ( $parcelshop ) {
						$package['parcelShopRecieverName'] = $order['billing']['first_name'] . ' ' . $order['billing']['last_name'];
					} else {
						$package['countOfPackages'] = intval( $pkg['amount'] );
					}

					$notification = array();
					if ( isset( $pkg['email'] ) ) {
						$notification[] = 'email';
					}
					if ( isset( $pkg['sms'] ) ) {
						$notification[] = 'sms';
					}
	
					$package['notification'] = $notification;

					$package['package']      = $package;
					$packages[]              = $package;
				} else {
					if ( $glsparcelshop ) {
						$package['parcelshopId'] = $glsparcelshop;
					} else {
						$package['countOfPackages'] = 1;
					}

					unset( $package['sender']['company'] );

					$package['package']      = $package;
					$gls_packages[]          = $package;
				}
			}

			$response = [];
			if ( count( $packages ) ) {
				$response = array_merge( $response, $this->api->create_packages( $packages ) );
			}
			
			if ( count( $gls_packages ) ) {
				$response = array_merge( $response, $this->api->create_packages( $gls_packages, true ) );
			}

			$success = array();
			$failed  = array();

			foreach ( $response as $value ) {
				$content = json_decode( $value['responseContent'], true );
				$variable_number = $content['variableNumber'];
				$order           = wc_get_order( intval($order_number_to_id[$variable_number]) );
				if ( 201 === $value['responseCode'] ) {
					$success[]       = $value;
					$order->update_status( 'export-to-neoship', gmdate( 'd-m-Y H:i:s' ) );
				} else {
					$failed[] = [
						'variableNumber' => method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : $order->get_data()['number'],
						'result' 		 => $content['result'],
					];
				}
			}

			$location = add_query_arg(
				array(
					'neoship_export' => 1,
					'success'        => rawurlencode( wp_json_encode( $success ) ),
					'failed'         => rawurlencode( wp_json_encode( $failed ) ),
					'_wpnonce'       => wp_create_nonce( 'neoship_notice_nonce' ),
				),
				admin_url( 'edit.php?post_type=shop_order' )
			);

			wp_safe_redirect( ( $location ) );

			exit();
		}

		if ( isset( $_GET['posts_ids'] ) ) {
			$posts_ids = explode( ',', sanitize_text_field( wp_unslash( $_GET['posts_ids'] ) ) );
			if ( '' === $posts_ids[0] ) {
				wp_safe_redirect( admin_url( 'edit.php?post_type=shop_order' ) );
				exit();
			}

			$wc_template = new WC_Admin_List_Table_Orders();
			$wc_template->order_preview_template();
			$nonce = wp_create_nonce( 'neoship-form-nonce' );

			?>
				<div class="wrap neoship">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Export orders to neoship', 'neoship' ); ?></h1>
					<form method="post">
						<input type="hidden" name="_wpnonce" value="<?php echo esc_html( $nonce ); ?>">
						<table class="wp-list-table widefat fixed striped posts neoship-export-table">
							<tbody>
			<?php
			foreach ( $posts_ids as $index => $post_id ) {
				$id = intval( $post_id, 0 );
				if ( 0 === $id ) {
					continue;
				}
				$order_obj         = wc_get_order( $id );
				$order             = $order_obj->get_data();
				$shipping_info     = $order['shipping_lines'][ key($order['shipping_lines']) ]->get_data();
				$parcelshop_id     = $order_obj->get_meta('_parcelshop_id');
				$glsparcelshop_id  = $order_obj->get_meta('_glsparcelshop_id');
				$is_gls_package	= in_array( $shipping_info['method_id'], $this->gls_shipping_methods );

				?>
									<tr id="neoship-export-row-<?php echo $index ?>" class="<?php echo $is_gls_package ? 'neoship-gls' : 'neoship-sps' ?>">
										<td class="manage-column neoship-col-shipper-select">
											<img src="<?php echo plugins_url( '/../public/images/sps-logo.png', __FILE__ ) ?>" alt="sps-logo" class="sps-logo">
											<img src="<?php echo plugins_url( '/../public/images/gls-logo.png', __FILE__ ) ?>" alt="gls-logo" class="gls-logo">
				<?php if ( '' === $parcelshop_id && '' === $glsparcelshop_id && get_option( 'neoship_has_gls' ) ) { ?>
											<select class="neoship-shipper-change" name="packages[<?php echo esc_html( $index ); ?>][shipper]" data-rowid="#neoship-export-row-<?php echo $index ?>">
												<option value="sps" <?php echo $is_gls_package ? '' : 'selected' ?>>SPS</option>
												<option value="gls" <?php echo $is_gls_package ? 'selected' : '' ?>>GLS</option>
											</select>
				<?php } ?>
										</td>
										<td scope="col" class="manage-column">
											<input type="hidden" name="packages[<?php echo esc_html( $index ); ?>][id]" value="<?php echo esc_html( $order['id'] ); ?>">
											<a href="#" class="order-preview" data-order-id="<?php echo esc_html( $order['id'] ); ?>"><strong><?php echo esc_html( '#' . $order['number'] . ' ' . $order['billing']['first_name'] . ' ' . $order['billing']['last_name'] ); ?></strong></a>
										</td>
										<td scope="col" class="manage-column neoship-col-sms-email">
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][sms]" value="1" checked>
											<label for="packages[<?php echo esc_html( $index ); ?>][sms]"><?php esc_html_e( 'Send SMS', 'neoship' ); ?></label>
											<br>
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][email]" value="1" checked>
											<label for="packages[<?php echo esc_html( $index ); ?>][email]"><?php esc_html_e( 'Send email', 'neoship' ); ?></label>
										</td>
										<td scope="col" class="manage-column neoship-col-holddelivery">
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][holddelivery]" value="1">
											<label for="packages[<?php echo esc_html( $index ); ?>][holddelivery]"><?php esc_html_e( 'Hold delivery', 'neoship' ); ?></label>
											<br>
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][saturdaydelivery]" value="1">
											<label for="packages[<?php echo esc_html( $index ); ?>][saturdaydelivery]"><?php esc_html_e( 'Saturday delivery', 'neoship' ); ?></label>
										</td>
										<td scope="col" class="manage-column">
				<?php if ( '' === $parcelshop_id && '' === $glsparcelshop_id ) { ?>
											<div class="neoship-col-count">
												<label for="packages[<?php echo esc_html( $index ); ?>][amount]"><?php esc_html_e( 'Amount of packages', 'neoship' ); ?></label><br>
												<input type="number" min="1" step="1" name="packages[<?php echo esc_html( $index ); ?>][amount]" value="1">
											</div>
				<?php } elseif ( '' !== $parcelshop_id || '' !== $glsparcelshop_id ) { ?>
											<strong>Parcelshop</strong>
				<?php } ?>
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo esc_html( $index ); ?>][cod]"><?php esc_html_e( 'Amount of COD', 'neoship' ); ?></label><br>
											<input type="number" step="0.01" name="packages[<?php echo esc_html( $index ); ?>][cod]" value="<?php echo in_array( $order['payment_method'], [ 'cod', 'dobirka' ] ) ? $order['total'] : 0 ?>">
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo esc_html( $index ); ?>][insurance]"><?php esc_html_e( 'Amount of insurance', 'neoship' ); ?> (€)</label><br>
											<input type="number" step="0.01" name="packages[<?php echo esc_html( $index ); ?>][insurance]" value="0">
										</td>
										<td scope="col" class="manage-column neoship-deliverytype">
											<label for="packages[<?php echo esc_html( $index ); ?>][deliverytype]"><?php esc_html_e( 'Delivery type', 'neoship' ); ?></label><br>
											<select name="packages[<?php echo esc_html( $index ); ?>][deliverytype]">
												<option value=""><?php esc_html_e( 'Standard delivery', 'neoship' ); ?></option>
												<option value="1"><?php esc_html_e( 'Express to 12:00', 'neoship' ); ?></option>
												<option value="2"><?php esc_html_e( 'Express to 9:00', 'neoship' ); ?></option>
											</select>
										</td>
									</tr>
			<?php } ?>
							</tbody>
						</table>
						<div class="tablenav bottom">
							<div class="alignright actions">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>" class="button action"><?php esc_html_e( 'Back' ); ?></a>
								<button type="submit" class="button action"><?php esc_html_e( 'Export', 'neoship' ); ?></button>
							</div>
						</div>
					</form>
				</div>
			<?php
			exit();
		}
		wp_safe_redirect( admin_url( 'edit.php?post_type=shop_order' ) );
	}

	/**
	 * Include shipping init class.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function request_shipping_init() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-parcelshop-shipping-method.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-spscourier-shipping-method.php';
		if ( get_option( 'neoship_has_gls' ) ) {
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-glsparcelshop-shipping-method.php';
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-glscourier-shipping-method.php';
		}
        if ( get_option( 'neoship_has_packeta' ) ) {
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-packeta-shipping-method.php';
        }
        if ( get_option( 'neoship_has_123' ) ) {
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-123-shipping-method.php';
        }
		if ( get_option( 'neoship_has_dpd' ) ) {
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-dpdparcelshop-shipping-method.php';
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-wc-dpdcourier-shipping-method.php';
		}
	}

	/**
	 * Add parcelshop shipping method.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array $methods Default shipping methods.
	 */
	public function request_shipping_method( $methods ) {
		$methods['parcelshop'] = 'Neoship_WC_Parcelshop_Shipping_Method';
		$methods['neoship_spscourier'] = 'Neoship_WC_SpsCourier_Shipping_Method';
		if ( get_option( 'neoship_has_gls' ) ) {
			$methods['neoship_glsparcelshop'] = 'Neoship_WC_GlsParcelshop_Shipping_Method';
			$methods['neoship_glscourier'] = 'Neoship_WC_GlsCourier_Shipping_Method';
		}
        if ( get_option( 'neoship_has_packeta' ) ) {
            $methods['neoship_packeta'] = 'Neoship_WC_Packeta_Shipping_Method';
        }
        if ( get_option( 'neoship_has_123' ) ) {
            $methods['neoship_123'] = 'Neoship_WC_123_Shipping_Method';
        }
		if ( get_option( 'neoship_has_dpd' ) ) {
			$methods['neoship_dpdparcelshop'] = 'Neoship_WC_DpdParcelshop_Shipping_Method';
			$methods['neoship_dpdcourier'] = 'Neoship_WC_DpdCourier_Shipping_Method';
		}
		return $methods;
	}



	/**
	 * Neoship export page.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function export_packages_to_neoship3_step() {

		if ( isset( $_POST['packages'] ) && is_array( $_POST['packages'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'neoship-form-nonce' ) ) {

			$user		 		= $this->api->get_user();
			$order_number_to_id = [];
			$sps_packages 			= array();
			$gls_packages 			= array();
			$packeta_packages 		= array();
			$k123_packages 		    = array();
			$dpd_packages 		    = array();

			foreach ( $_POST['packages'] as $pkg ) {
				$order_obj      = wc_get_order( intval( $pkg['id'] ) );
				$order      	= $order_obj->get_data();

				$shipping_info  = $order['shipping_lines'][ key($order['shipping_lines']) ]->get_data();
				if (in_array( $shipping_info['method_id'], $this->gls_shipping_methods )) {
                    $carrier = 'gls';
                } elseif (in_array( $shipping_info['method_id'], $this->packeta_shipping_methods )){
                    $carrier = 'packeta';
                } elseif (in_array( $shipping_info['method_id'], $this->k123_shipping_methods )){
                    $carrier = '123';
                } elseif (in_array( $shipping_info['method_id'], $this->dpd_shipping_methods )){
					$carrier = 'dpd';
				} else {
                    $carrier = 'sps';
                }
                $parcelshop_id = $order_obj->get_meta('_parcelshop_id');

				$package = [
					"reference_number" => $order['number'],

					"sender_name" => $user['address']['name'],
					"sender_company" => $user['address']['company'],
					"sender_street" => $user['address']['street'],
					"sender_house_number" => $user['address']['house_number'],
					"sender_city" => $user['address']['city'],
					"sender_zip" => $user['address']['zip'],
					"sender_state_code" => $user['address']['state']['code'],
					"sender_email" => $user['address']['email'],
					"sender_phone" => $user['address']['phone'],


					"receiver_name" => $order['shipping']['first_name'] . ' ' . $order['shipping']['last_name'],
					"receiver_company" => $order['shipping']['company'],
					"receiver_street" => empty($order['shipping']['address_2']) ? $order['shipping']['address_1'] : $order['shipping']['address_1'] . '-' . $order['shipping']['address_2'],
					"receiver_house_number" => null,
					"receiver_city" => $order['shipping']['city'],
					"receiver_zip" => $order['shipping']['postcode'],
					"receiver_state_code" => $order['shipping']['country'],
					"receiver_email" => !empty($order['shipping']['email']) ? $order['shipping']['email'] : $order['billing']['email'],
					"receiver_phone" => !empty($order['shipping']['phone']) ? $order['shipping']['phone'] : $order['billing']['phone'],


					"cod_price" => in_array( $order['payment_method'], [ 'cod', 'dobirka' ] ) ? sanitize_text_field( $pkg['cod'] ) : null,
					"cod_currency_code" => $order['currency'],
					"cod_reference" =>  null,

					"insurance" => sanitize_text_field( $pkg['insurance'] ),
					"reverse" => false,
					"parcelshop" => $parcelshop_id,
					"count_of_packages" => $parcelshop_id ? 1 : intval( $pkg['amount'] )
				];

                if (isset($pkg['weight'])) {
                    $package["weight"] = floatval($pkg['weight']);
                }

				if (isset($pkg['cod_reference'])) {
					$package["cod_reference"] = sanitize_text_field( $pkg['cod_reference'] );
				}

                $order_number_to_id[$order['number']] = $order['id'];

                switch ($carrier) {
                    case 'gls':
                        $gls_packages[] = $package;
                        break;
                    case 'packeta':
                        $packeta_packages[] = $package;
                        break;
                    case 'sps':
                        $sps_packages[] = $package;
                        break;
                    case '123':
                        $k123_packages[] = $package;
                        break;
					case 'dpd':
						$dpd_packages[] = $package;
						break;
                    default :
                        echo 'Carrier not defined!';
                }
			}

            $response = [];
            if ( count( $gls_packages ) ) {
                $response[] = $this->api->create_packages( $gls_packages, 1 ) ;//gls shipper id '1'
            }
            if ( count( $sps_packages ) ) {
                $response[] = $this->api->create_packages( $sps_packages, 2 ) ; //sps shipper id '2'
            }
            if ( count( $packeta_packages ) ) {
                $response[] = $this->api->create_packages( $packeta_packages, 3 ) ;//packeta shipper id '3'
            }
            if ( count( $k123_packages ) ) {
                $response[] = $this->api->create_packages( $k123_packages, 4 ) ;//123 kurier shipper id '4'
            }
			if ( count( $dpd_packages ) ) {
				$response[] = $this->api->create_packages( $dpd_packages, 5 ) ;//dpd shipper id '5'
			}

            $success = [];
            $failed  = [];

            foreach ( $response as $value ) {
                if (200 === wp_remote_retrieve_response_code( $value )) {
                    $success[] =  json_decode( wp_remote_retrieve_body( $value ) );
                }
                if (422 === wp_remote_retrieve_response_code( $value )) {
                    array_push($failed, json_decode( wp_remote_retrieve_body( $value ) , true ));
                }
            }

            foreach ( $success as $value ) {
                foreach ( $value as $imported ) {
                    $order = wc_get_order(intval($order_number_to_id[$imported->reference_number] ));
                    $order->update_status('export-to-neoship', gmdate('d-m-Y H:i:s'));
                }
            }

            $errors = [];

            foreach ($failed as $value) {
                foreach ($value as $fail) {
                    $errors[$fail['reference_number']] = '';
                    foreach ($fail['errors'] as $key => $error) {
                        if ('services' !== $key && !empty($error) && is_array($error)) {
                            $errors[$fail['reference_number']] .= " $key: " . implode(', ', $error);
                        } elseif (is_array($error)) {
                            foreach ($error as $keyService => $errorService) {
                                if (!empty($errorService)) {
									if(is_array($errorService)){
//										var_dump($errorService);die();
									} else {
										$errors[$fail['reference_number']] .= " $keyService: " . implode(', ', $errorService);
									}
                                }
                            }
                        } else {
                            $errors[$fail['reference_number']] .= $error;
                        }
                    }
                }
            }

			$location = add_query_arg(
				array(
					'neoship_export' => 1,
					'success'        => rawurlencode( wp_json_encode( $success ) ),
					'failed'         => rawurlencode( wp_json_encode( $errors ) ),
					'_wpnonce'       => wp_create_nonce( 'neoship_notice_nonce' ),
				),
				admin_url( 'edit.php?post_type=shop_order' )
			);

			wp_safe_redirect( ( $location ) );

			exit();
		}

		if ( isset( $_GET['posts_ids'] ) ) {
			$posts_ids = explode( ',', sanitize_text_field( wp_unslash( $_GET['posts_ids'] ) ) );
			if ( '' === $posts_ids[0] ) {
				wp_safe_redirect( admin_url( 'edit.php?post_type=shop_order' ) );
				exit();
			}

			$wc_template = new WC_Admin_List_Table_Orders();
			$wc_template->order_preview_template();
			$nonce = wp_create_nonce( 'neoship-form-nonce' );

			?>
				<div class="wrap neoship">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Export orders to neoship', 'neoship' ); ?></h1>
					<form method="post">
						<input type="hidden" name="_wpnonce" value="<?php echo esc_html( $nonce ); ?>">
						<table class="wp-list-table widefat fixed striped posts neoship-export-table">
							<tbody>
			<?php
			foreach ( $posts_ids as $index => $post_id ) {
				$id = intval( $post_id, 0 );
				if ( 0 === $id ) {
					continue;
				}
				$order_obj         = wc_get_order( $id );
                $order             = $order_obj->get_data();
                $shipping_info     = $order['shipping_lines'][ key($order['shipping_lines']) ]->get_data();
                $parcelshop_id     = $order_obj->get_meta('_parcelshop_id');

                $carrier = 'sps';
                if( in_array( $shipping_info['method_id'], $this->gls_shipping_methods )){
                    $carrier = 'gls';
                } else if (in_array( $shipping_info['method_id'], $this->packeta_shipping_methods )) {
                    $carrier = 'packeta';
                } else if (in_array( $shipping_info['method_id'], $this->k123_shipping_methods )) {
                    $carrier = '123';
                } else if (in_array( $shipping_info['method_id'], $this->dpd_shipping_methods )) {
					$carrier = 'dpd';
				}

                ?>
									<tr id="neoship-export-row-<?php echo $index ?>" class="<?php echo 'neoship-' . $carrier ?>">
										<td class="manage-column neoship-col-shipper-select">
											<img src="<?php echo plugins_url( '/../public/images/sps-logo.png', __FILE__ ) ?>" alt="sps-logo" class="sps-logo">
											<img src="<?php echo plugins_url( '/../public/images/gls-logo.png', __FILE__ ) ?>" alt="gls-logo" class="gls-logo">
											<img src="<?php echo plugins_url( '/../public/images/packeta-logo.png', __FILE__ ) ?>" alt="packeta-logo" class="packeta-logo">
											<img src="<?php echo plugins_url( '/../public/images/123kurier-logo.png', __FILE__ ) ?>" alt="123-logo" class="k123-logo">
											<img src="<?php echo plugins_url( '/../public/images/dpd-logo.png', __FILE__ ) ?>" alt="dpd-logo" class="dpd-logo">

										</td>
										<td scope="col" class="manage-column">
											<input type="hidden" name="packages[<?php echo esc_html( $index ); ?>][id]" value="<?php echo esc_html( $order['id'] ); ?>">
											<a href="#" class="order-preview" data-order-id="<?php echo esc_html( $order['id'] ); ?>"><strong><?php echo esc_html( '#' . $order['number'] . ' ' . $order['billing']['first_name'] . ' ' . $order['billing']['last_name'] ); ?></strong></a>
										</td>
										<td scope="col" class="manage-column">
				<?php if ( '' === $parcelshop_id ) { ?>
											<div class="neoship-col-count-of-packages">
												<label for="packages[<?php echo esc_html( $index ); ?>][amount]"><?php esc_html_e( 'Amount of packages', 'neoship' ); ?></label><br>
												<input type="number" min="1" step="1" name="packages[<?php echo esc_html( $index ); ?>][amount]" value="1">
											</div>
				<?php } elseif ( '' !== $parcelshop_id ) { ?>
                                    <?php if ( $carrier == 'sps' && strpos($order['shipping']['company'], 'Alzabox') !== false ) { ?>
                                        <strong>Parcelshop - Alzabox</strong>
                                    <?php } else  { ?>
                                        <strong>Parcelshop</strong>
                                    <?php } ?>
                <?php } ?>
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo esc_html( $index ); ?>][cod]"><?php esc_html_e( 'Amount of COD', 'neoship' ); ?></label><br>
											<input type="number" step="0.01" name="packages[<?php echo esc_html( $index ); ?>][cod]" value="<?php echo in_array( $order['payment_method'], [ 'cod', 'dobirka' ] ) ? $order['total'] : 0 ?>">
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo esc_html( $index ); ?>][insurance]"><?php esc_html_e( 'Amount of insurance', 'neoship' ); ?> (€)</label><br>
											<input type="number" step="0.01" name="packages[<?php echo esc_html( $index ); ?>][insurance]" value="">
										</td>
                                    <?php if ( $carrier == 'packeta') { ?>
                                        <td scope="col" class="manage-column">
                                            <label for="packages[<?php echo esc_html( $index ); ?>][weight]">Váha (kg)</label><br>
                                            <input type="number" step="0.01" name="packages[<?php echo esc_html( $index ); ?>][weight]" value="1">
                                        </td>
									<?php } if ( $carrier == 'dpd') { ?>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo esc_html( $index ); ?>][cod_reference]">Referencia dobierky</label><br>
											<input type="number"  name="packages[<?php echo esc_html( $index ); ?>][cod_reference]" value="<?php echo esc_html( $order['number'] ); ?>">
										</td>

									<?php } else {?>
                                        <td scope="col" class="manage-column">
                                        </td>
                                    <?php } ?>
									</tr>
			<?php } ?>
							</tbody>
						</table>
						<div class="tablenav bottom">
							<div class="alignright actions">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>" class="button action"><?php esc_html_e( 'Back' ); ?></a>
								<button type="submit" class="button action"><?php esc_html_e( 'Export', 'neoship' ); ?></button>
							</div>
						</div>
					</form>
				</div>
			<?php
			exit();
		}
		wp_safe_redirect( admin_url( 'edit.php?post_type=shop_order' ) );
	}

}
