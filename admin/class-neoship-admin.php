<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.kuskosoft.com
 * @since      1.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/admin
 */

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
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Neoship settings from db
	 */
	private $settings;

	/**
	 * NeoshipApi
	 */
	private $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = get_option('neoship_login');
		$this->api = new NeoshipApi();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
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
		wp_enqueue_style('woocommerce_stylesheet', plugin_dir_url( dirname(__FILE__) ). '../woocommerce/assets/css/admin.css',false,null,"all");
		
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
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
		wp_enqueue_script( 'wc-backbone-modal', plugin_dir_path( dirname( __FILE__ ) ) . '/../woocommerce/assets/js/admin/backbone-modal.min.js' , array( 'jquery' ), null, false );
		wp_enqueue_script( 'wc-orders', plugin_dir_path( dirname( __FILE__ ) ) . '/../woocommerce/assets/js/admin/wc-orders.min.js' , array( 'underscore', 'backbone', 'wp-util' ), null, false );
	}

	function settingsPage() {
		add_options_page( __('Neoship settings'), __('Neoship'), 'manage_options', 'neoship-settings-page', array($this, 'neoshipSettingsPageHtml') );

		add_submenu_page( 
			null, 
			__('Neoship export'), // title 
			'', 
			'manage_woocommerce', 
			'neoship-export',
			array($this, 'exportPackagesToNeoshipStep'), 
			null 
		);
	}

	function settingsInit() {
		register_setting( 'neoship-settings', 'neoship_login' );

		add_settings_section(
			'neoship-settings-section',
			__( 'Login settings', 'neoship' ),
			array($this, 'neoshiipSettingsSectionCallback'),
			'neoship-settings'
		);

		add_settings_field(
			'clientid',
			__( 'Client ID', 'neoship' ),
			array($this, 'neoshipSettingsIdCallback'),
			'neoship-settings',
			'neoship-settings-section'
		);
	
		add_settings_field(
			'clientsecret',
			__( 'Client secret', 'neoship' ),
			array($this, 'neoshipSettingsSecretCallback'),
			'neoship-settings',
			'neoship-settings-section'
		);
		
	}

	function neoshiipSettingsSectionCallback() {
		echo __( 'Please fill data which you receive from neoship', 'neoship' );
	}

	function neoshipSettingsIdCallback() {
		echo '<input id="clientid" autocomplete="off" class="regular-text" type="text" name="neoship_login[clientid]" value="' . get_option('neoship_login')['clientid'] . '">';
	}

	function neoshipSettingsSecretCallback() {
		echo '<input id="clientsecret" autocomplete="off" class="regular-text" type="text" name="neoship_login[clientsecret]" value="' . get_option('neoship_login')['clientsecret'] . '">';
	}

	function neoshipSettingsPageHtml() {

		if (!current_user_can('manage_options')) {
			return;
		}

		$this->api->login(true);
	
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

	function neoshipOrderListBulkActions( $actions ) {
		$actions['neoship_export'] = __( 'Export to Neoship', 'neoship' ) . ' (' . $this->api->getUserCredit() . '€)';
		$actions['neoship_print_stickers'] = __( 'Print stickers (PDF)', 'neoship' );
		$actions['neoship_print_stickers_zebra_102x152'] = __( 'Print zebra stickers(PDF) 102x152', 'neoship' );
		$actions['neoship_print_stickers_zebra_80x214'] = __( 'Print zebra stickers (PDF) 80x214', 'neoship' );
		$actions['neoship_acceptance_protocol'] = __( 'Acceptance protocol', 'neoship' );
		return $actions;
	}

	function handleBulkActionExportPackagesToNeoship( $redirect_to, $action, $posts_ids ) {

		if ( $action !== 'neoship_export' )
			return $redirect_to; 

		return $redirect_to = add_query_arg( array(
			'posts_ids' => implode( ',', $posts_ids ),
		), admin_url('admin.php?page=neoship-export') );

	}

	function handleBulkActionPrintStickers( $redirect_to, $action, $posts_ids ) {

		if ( !in_array($action, ['neoship_print_stickers', 'neoship_print_stickers_zebra_102x152', 'neoship_print_stickers_zebra_80x214']) )
			return $redirect_to;

		$template = 0;
		switch ($action) {
			case 'neoship_print_stickers_zebra_102x152':
				$template = 1;
				break;
			case 'neoship_print_stickers_zebra_80x214':
				$template = 1;
				break;
		}

		$this->api->printSticker($template, $posts_ids);

	}

	function handleBulkActionPrintAcceptanceProtocol( $redirect_to, $action, $posts_ids ) {

		if ( $action !== 'neoship_acceptance_protocol' )
			return $redirect_to; 

		$this->api->printAcceptanceProtocol($posts_ids);

	}

	function neoshipBulkActionAdminNotice() {
		if ( !empty( $_REQUEST['neoship_export'] ) ) {
			$success = isset($_REQUEST['success']) && is_array($_REQUEST['success']) ? $_REQUEST['success'] : array();
			$failed = isset($_REQUEST['failed']) && is_array($_REQUEST['failed']) ? $_REQUEST['failed'] : array();

			echo '<div class="notice notice-success is-dismissible"><p>';
			printf(_n("%d order was exported", "%d orders was exported", count($success), 'neoship'), count($success));
			if(count($failed) == 0){
				echo ' ';
				printf(_n("%d order was not exported", "%d orders was not exported", 0, 'neoship'), 0);
			}
			echo '</p></div>';

			if(count($failed) > 0){
				echo '<div class="notice notice-error is-dismissible"><p>';
				printf(_n("%d order was not exported", "%d orders was not exported", count($failed), 'neoship'), count($failed));
				echo '</p>';
				foreach ($failed as $value) {
					echo '<p>';
					printf( '<strong>'.esc_html__( 'Order %d', 'neoship' ).'</strong>: '. esc_html($value['result']) , intval($value['variableNumber']));
					echo '</p>';
				}
				echo '</div>';
			}
		}
		if( !empty( $_REQUEST['neoship_error'] ) && !empty( $_REQUEST['error'] ) ){
			echo '<div class="notice notice-error is-dismissible"><p>';
			echo esc_html($_REQUEST['error']);
			echo '</p></div>';
		}
	}

	function registerNeoshipPostStatuses() {
		register_post_status( 'wc-export-to-neoship', array(
			'label'                     => __('Exported to Neoship', 'neoship'),
			'public'                    => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Exported to Neoship <span class="count">(%s)</span>', 'Exported to Neoship <span class="count">(%s)</span>', 'neoship' ),
		));
	}

	function addNeoshipOrderStatuses($order_statuses) {
		$order_statuses['wc-export-to-neoship'] = __('Exported to Neoship', 'neoship');
		return $order_statuses;
	}

	function addNeoshipOrderListActions($actions, $order) {
		if ( $order->has_status( array( 'export-to-neoship' ) ) ) {
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$actions['export-to-neoship'] = array(
				'url'       => NEOSHIP_TRACKING_URL.'/tracking/packageReference/'.$this->settings['userid'].'/'.$order_id,
				'name'      => __( 'Tracking', 'neoship' ),
				'action'    => "export-to-neoship", // keep "view" class for a clean button CSS
			);
		}
		return $actions;
	}

	function exportPackagesToNeoshipStep() {
		
		if(isset($_POST['packages'])){
			$userAddress = $this->api->getUserAddress();
			$states = $this->api->getStatesIds();
			$currencies = $this->api->getCurrenciesIds();
			
			$packages = array();
			foreach ( $_POST['packages'] as $pkg ) {
				$order = wc_get_order( intval($pkg['id']) )->get_data();
				$parcelshop = false;
				foreach ($order['meta_data'] as $meta) {
					if($meta->key == '_parcelshop_id'){
						$parcelshop = true;
						break;
					}
				}
				$package = array();
				$package['sender'] = $userAddress; 
				$package['receiver'] = array(); 
				$package['receiver']['name'] = $order['shipping']['first_name'].' '.$order['shipping']['last_name']; 
				$package['receiver']['company'] = $order['shipping']['company']; 
				$package['receiver']['street'] = $order['shipping']['address_1']; 
				$package['receiver']['city'] = $order['shipping']['city']; 
				$package['receiver']['zIP'] = $order['shipping']['postcode']; 
				$package['receiver']['email'] = $order['billing']['email']; 
				$package['receiver']['phone'] = $order['billing']['phone']; 
				$package['receiver']['state'] = $states[$order['shipping']['country']]; 
				$package['variableNumber'] = $order['number']; 
				
				if($order['payment_method'] == 'cod'){
					$package['cashOnDeliveryPrice'] = $order['total'];
					$package['cashOnDeliveryCurrency'] = $currencies[$order['currency']];
				}
				else{
					$package['cashOnDeliveryPrice'] = '';
					$package['cashOnDeliveryCurrency'] = '';
				}
				$package['cashOnDeliveryPayment'] = '';

				$package['insurance'] = sanitize_text_field($pkg['insurance']);
				$package['insuranceCurrency'] = $currencies['EUR'];
				if(isset($pkg['holddelivery'])){
					$package['holdDelivery'] = true;
				}
				if(isset($pkg['saturdaydelivery'])){
					$package['saturdayDelivery'] = true;
				}
				$package['express'] = $pkg['deliverytype'] != '' ? intval($pkg['deliverytype']) : null;

				if($parcelshop){
					$package['parcelShopRecieverName'] = $order['billing']['first_name'].' '.$order['billing']['last_name']; 
				}
				else{
					$package['countOfPackages'] = intval($pkg['amount']);
				}
				
				$notification = array();
				if(isset($pkg['email'])){
					$notification[] = 'email';
				}
				if(isset($pkg['sms'])){
					$notification[] = 'sms';
				}

				$package['notification'] = $notification;
				$package['package'] = $package;
				$packages[] = $package;
			}

			$response = $this->api->createPackages($packages);

			$success = array();
			$failed = array();
			
			foreach ($response as $value) {
				if($value['responseCode'] == 201){
					$success[] = $value;
					$variableNumber = json_decode($value['responseContent'], true)['variableNumber'];
					$order = wc_get_order( $variableNumber );
					$order->update_status('export-to-neoship', date("d-m-Y H:i:s"));
				}
				else{
					$failed[] = json_decode($value['responseContent']);
				}
			}
			wp_redirect(add_query_arg( array(
				'neoship_export' => '1',
				'success' => $success,
				'failed' => $failed,
			), admin_url('edit.php?post_type=shop_order')));
			exit();
		}

		if (isset($_GET['posts_ids'])) {
			$posts_ids = explode(',', $_GET['posts_ids']);
			if($posts_ids[0] == ''){
				wp_redirect(admin_url('edit.php?post_type=shop_order'));
				exit();
			}

			$wcTemplate = new WC_Admin_List_Table_Orders();
			$wcTemplate->order_preview_template();

			?>
				<div class="wrap neoship">
					<h1 class="wp-heading-inline"><?php _e('Export orders to neoship', 'neoship') ?></h1>
					<form method="post">
						<table class="wp-list-table widefat fixed striped posts">
							<tbody>
								<?php foreach ( $posts_ids as $index => $post_id ) { 
									$id = intval($post_id, 0);
									if($id == 0){
										continue;
									}
									$order = wc_get_order( $id )->get_data();
									$parcelshop_id = 0;
									foreach ($order['meta_data'] as $meta) {
										if($meta->key == '_parcelshop_id'){
											$parcelshop_id = $meta->value;
											break;
										}
									}
									?>
									<tr>
										<td scope="col" class="manage-column">
											<input type="hidden" name="packages[<?php echo $index; ?>][id]" value="<?php echo $order['id']; ?>">
											<a href="#" class="order-preview" data-order-id="<?php echo $order['id'] ?>"><strong><?php echo '#'.$order['id'].' '.$order['shipping']['first_name'].' '.$order['shipping']['last_name'];?></strong></a>
										</td>
										<td scope="col" class="manage-column">
											<input type="checkbox" name="packages[<?php echo $index; ?>][sms]" value="1" checked>
											<label for="packages[<?php echo $index; ?>][sms]"><?php _e('Send SMS', 'neoship')?></label>
											<br>
											<input type="checkbox" name="packages[<?php echo $index; ?>][email]" value="1" checked>
											<label for="packages[<?php echo $index; ?>][email]"><?php _e('Send email', 'neoship')?></label>
										</td>
										<td scope="col" class="manage-column">
											<input type="checkbox" name="packages[<?php echo $index; ?>][holddelivery]" value="1">
											<label for="packages[<?php echo $index; ?>][holddelivery]"><?php _e('Hold delivery', 'neoship')?></label>
											<br>
											<input type="checkbox" name="packages[<?php echo $index; ?>][saturdaydelivery]" value="1">
											<label for="packages[<?php echo $index; ?>][saturdaydelivery]"><?php _e('Saturday delivery', 'neoship')?></label>
										</td>
										<td scope="col" class="manage-column">
											<?php if($parcelshop_id == 0) {?>
												<label for="packages[<?php echo $index; ?>][amount]"><?php _e('Amount of packages', 'neoship')?></label><br>
												<input type="number" min="1" step="1" name="packages[<?php echo $index; ?>][amount]" value="1">
											<?php } else {?>
												<strong>Parcelshop</strong>
											<?php }?>
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo $index; ?>][insurance]"><?php _e('Amount of insurance', 'neoship')?> (€)</label><br>
											<input type="number" step="0.01" name="packages[<?php echo $index; ?>][insurance]" value="1000">
										</td>
										<td scope="col" class="manage-column">
											<label for="packages[<?php echo $index; ?>][deliverytype]"><?php _e('Delivery type', 'neoship')?></label><br>
											<select name="packages[<?php echo $index; ?>][deliverytype]">
												<option value=""><?php _e('Standard delivery', 'neoship')?></option>
												<option value="1"><?php _e('Express to 12:00', 'neoship')?></option>
												<option value="2"><?php _e('Express to 9:00', 'neoship')?></option>
											</select>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
						<div class="tablenav bottom">
							<div class="alignright actions">
								<a href="<?php echo admin_url('edit.php?post_type=shop_order') ?>" class="button action"><?php _e('Back') ?></a>
								<button type="submit" class="button action"><?php _e('Export', 'neoship') ?></buttpn>
							</div>
						</div>
					</form>
				</div>
			<?php	
			exit();
		}
		wp_redirect(admin_url('edit.php?post_type=shop_order'));
	}

	public function requestShippingInit(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-parcelshop-method.php';
	}

	public function requestShippingMethod( $methods ) {
		$methods['parcelshop'] = 'Neoship_WC_Parcelshop_Shipping_Method';
		return $methods;
	}

}
