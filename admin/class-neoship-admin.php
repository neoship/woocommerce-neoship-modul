<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://www.kuskosoft.com
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
 * @author     Mirec <hutar@kuskosoft.com>
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
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings    = get_option( 'neoship_login' );
		$this->api         = new Neoship_Api();

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/neoship-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'woocommerce_stylesheet', plugin_dir_url( dirname( __FILE__ ) ) . '../woocommerce/assets/css/admin.css', array(), '3.8.1', 'all' );

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/neoship-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'wc-backbone-modal', plugin_dir_url( dirname( __FILE__ ) ) . '../woocommerce/assets/js/admin/backbone-modal.min.js', array( 'backbone', 'jquery' ), false, false );
		wp_enqueue_script( 'wc-orders', plugin_dir_url( dirname( __FILE__ ) ) . '../woocommerce/assets/js/admin/wc-orders.min.js', array( 'underscore', 'backbone', 'wp-util' ), false, false );
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
			array( $this, 'export_packages_to_neoship_step' ),
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
			__( 'Client ID', 'neoship' ),
			array( $this, 'neoship_settings_id_callback' ),
			'neoship-settings',
			'neoship-settings-section'
		);

		add_settings_field(
			'clientsecret',
			__( 'Client secret', 'neoship' ),
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
		echo '<input id="clientsecret" autocomplete="off" class="regular-text" type="text" name="neoship_login[clientsecret]" value="' . esc_html( get_option( 'neoship_login' )['clientsecret'] ) . '">';
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
		$actions['neoship_export']                       = __( 'Export to Neoship', 'neoship' ) . ' (' . $this->api->get_user_credit() . '€)';
		$actions['neoship_print_stickers']               = __( 'Print stickers (PDF)', 'neoship' );
		$actions['neoship_print_stickers_zebra_102x152'] = __( 'Print zebra stickers(PDF) 102x152', 'neoship' );
		$actions['neoship_print_stickers_zebra_80x214']  = __( 'Print zebra stickers (PDF) 80x214', 'neoship' );
		$actions['neoship_acceptance_protocol']          = __( 'Acceptance protocol', 'neoship' );
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

		if ( ! in_array( $action, array( 'neoship_print_stickers', 'neoship_print_stickers_zebra_102x152', 'neoship_print_stickers_zebra_80x214' ), true ) ) {
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

		$this->api->print_sticker( $template, $posts_ids );

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

		if ( 'neoship_acceptance_protocol' !== $action ) {
			return $redirect_to;
		}

		$this->api->print_acceptance_protocol( $posts_ids );

	}

	/**
	 * Handle neoship bulk action admin notice.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function neoship_bulk_action_admin_notice() {
		if ( ! empty( $_REQUEST['neoship_export'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {

			if ( false !== wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'neoship_notice_nonce' ) ) {

				$success = isset( $_REQUEST['success'] ) === true ? json_decode( sanitize_text_field( ( wp_unslash( $_REQUEST['success'] ) ) ), true ) : array();
				$failed  = isset( $_REQUEST['failed'] ) === true ? json_decode( sanitize_text_field( ( wp_unslash( $_REQUEST['failed'] ) ) ), true ) : array();

				echo '<div class="notice notice-success is-dismissible"><p>';
				/* translators: %d: number of orders */
				$output = sprintf( _n( '%d order was exported', '%d orders was exported', count( $success ), 'neoship' ), count( $success ) );
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
					$output = sprintf( _n( '%d order was not exported', '%d orders was not exported', count( $failed ), 'neoship' ), count( $failed ) );
					echo esc_html( $output );
					echo '</p>';
					foreach ( $failed as $value ) {
						echo '<p>';
						/* translators: %d: number of orders */
						$output = printf( '<strong>' . esc_html__( 'Order %d', 'neoship' ) . '</strong>: ' . esc_html( $value['result'] ), intval( $value['variableNumber'] ) );
						echo esc_html( $output );
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
			$order_id                     = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$actions['export-to-neoship'] = array(
				'url'    => NEOSHIP_TRACKING_URL . '/tracking/packageReference/' . $this->settings['userid'] . '/' . $order_id,
				'name'   => __( 'Tracking', 'neoship' ),
				'action' => 'export-to-neoship',
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

			$user_address = $this->api->get_user_address();
			$states       = $this->api->get_states_ids();
			$currencies   = $this->api->get_currencies_ids();

			$packages = array();
			foreach ( $_POST['packages'] as $pkg ) {
				$order      = wc_get_order( intval( $pkg['id'] ) )->get_data();
				$parcelshop = false;
				foreach ( $order['meta_data'] as $meta ) {
					if ( '_parcelshop_id' === $meta->key ) {
						$parcelshop = true;
						break;
					}
				}
				$package                        = array();
				$package['sender']              = $user_address;
				$package['receiver']            = array();
				$package['receiver']['name']    = $order['shipping']['first_name'] . ' ' . $order['shipping']['last_name'];
				$package['receiver']['company'] = $order['shipping']['company'];
				$package['receiver']['street']  = $order['shipping']['address_1'];
				$package['receiver']['city']    = $order['shipping']['city'];
				$package['receiver']['zIP']     = $order['shipping']['postcode'];
				$package['receiver']['email']   = $order['billing']['email'];
				$package['receiver']['phone']   = $order['billing']['phone'];
				$package['receiver']['state']   = $states[ $order['shipping']['country'] ];
				$package['variableNumber']      = $order['id'];

				if ( 'cod' === $order['payment_method'] ) {
					$package['cashOnDeliveryPrice']    = $order['total'];
					$package['cashOnDeliveryCurrency'] = $currencies[ $order['currency'] ];
				} else {
					$package['cashOnDeliveryPrice']    = '';
					$package['cashOnDeliveryCurrency'] = '';
				}
				$package['cashOnDeliveryPayment'] = '';

				$package['insurance']         = sanitize_text_field( $pkg['insurance'] );
				$package['insuranceCurrency'] = $currencies['EUR'];
				if ( isset( $pkg['holddelivery'] ) ) {
					$package['holdDelivery'] = true;
				}
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
			}

			$response = $this->api->create_packages( $packages );

			$success = array();
			$failed  = array();

			foreach ( $response as $value ) {
				$content = json_decode( $value['responseContent'], true );
				$variable_number = $content['variableNumber'];
				$order           = wc_get_order( intval($variable_number) );
				if ( 201 === $value['responseCode'] ) {
					$success[]       = $value;
					$order->update_status( 'export-to-neoship', gmdate( 'd-m-Y H:i:s' ) );
				} else {
					$failed[] = [
						'variableNumber' => $order->data['number'],
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
						<table class="wp-list-table widefat fixed striped posts">
							<tbody>
			<?php
			foreach ( $posts_ids as $index => $post_id ) {
				$id = intval( $post_id, 0 );
				if ( 0 === $id ) {
					continue;
				}
				$order         = wc_get_order( $id )->get_data();
				$parcelshop_id = 0;
				foreach ( $order['meta_data'] as $meta ) {
					if ( '_parcelshop_id' === $meta->key ) {
						$parcelshop_id = $meta->value;
						break;
					}
				}
				?>
									<tr>
										<td scope="col" class="manage-column">
											<input type="hidden" name="packages[<?php echo esc_html( $index ); ?>][id]" value="<?php echo esc_html( $order['id'] ); ?>">
											<a href="#" class="order-preview" data-order-id="<?php echo esc_html( $order['id'] ); ?>"><strong><?php echo esc_html( '#' . $order['id'] . ' ' . $order['billing']['first_name'] . ' ' . $order['billing']['last_name'] ); ?></strong></a>
										</td>
										<td scope="col" class="manage-column">
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][sms]" value="1" checked>
											<label for="packages[<?php echo esc_html( $index ); ?>][sms]"><?php esc_html_e( 'Send SMS', 'neoship' ); ?></label>
											<br>
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][email]" value="1" checked>
											<label for="packages[<?php echo esc_html( $index ); ?>][email]"><?php esc_html_e( 'Send email', 'neoship' ); ?></label>
										</td>
										<td scope="col" class="manage-column">
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][holddelivery]" value="1">
											<label for="packages[<?php echo esc_html( $index ); ?>][holddelivery]"><?php esc_html_e( 'Hold delivery', 'neoship' ); ?></label>
											<br>
											<input type="checkbox" name="packages[<?php echo esc_html( $index ); ?>][saturdaydelivery]" value="1">
											<label for="packages[<?php echo esc_html( $index ); ?>][saturdaydelivery]"><?php esc_html_e( 'Saturday delivery', 'neoship' ); ?></label>
										</td>
										<td scope="col" class="manage-column">
				<?php if ( 0 === $parcelshop_id ) { ?>
												<label for="packages[<?php echo esc_html( $index ); ?>][amount]"><?php esc_html_e( 'Amount of packages', 'neoship' ); ?></label><br>
												<input type="number" min="1" step="1" name="packages[<?php echo esc_html( $index ); ?>][amount]" value="1">
				<?php } else { ?>
												<strong>Parcelshop</strong>
				<?php } ?>
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo esc_html( $index ); ?>][insurance]"><?php esc_html_e( 'Amount of insurance', 'neoship' ); ?> (€)</label><br>
											<input type="number" step="0.01" name="packages[<?php echo esc_html( $index ); ?>][insurance]" value="1000">
										</td>
										<td scope="col" class="manage-column">
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
								<button type="submit" class="button action"><?php esc_html_e( 'Export', 'neoship' ); ?></buttpn>
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
		return $methods;
	}

}
