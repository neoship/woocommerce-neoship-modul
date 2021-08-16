<?php
/**
 * The file that defines the neoship 3 api
 *
 * A class definition that includes attributes and functions used to communication with neoship api
 *
 * @link  https://www.kuskosoft.com
 * @since 3.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Neoship api class.
 *
 * @since      3.0.0
 * @package    Neoship
 * @subpackage Neoship/includes
 * @author     Mirec <hutar@kuskosoft.com>
 */
class Neoship3_Api {

	/**
	 * Access data
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    array    $access_data    Saved access data.
	 */
	private $access_data = false;

	/**
	 * Login data
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    array    $login_data    Saved login data.
	 */
	private $login_data;

	/**
	 * Login user.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param bool $test If testing login.
	 */
	public function login( $test = false ) {
		$this->login_data = get_option( 'neoship_login' );

		if ( false === $this->login_data ) {
			$this->error_message( __( 'Please setup neoship login credentials', 'neohsip' ), ! $test );
		}

		$url  = NEOSHIP3_API_URL . '/login_check';
		$args = [
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body' => json_encode([
				'username' => $this->login_data['clientid'],
				'password' => $this->login_data['clientsecret'],
			])
		];

		$response = wp_remote_post( $url, $args );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Bad login credentials', 'neoship' ), ! $test );
			return;
		}

		$this->access_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $test ) {
			$this->save_user_id();
			$this->success_message( __( 'Login credentials are correct', 'neoship' ), ! $test );
		}
	}

	/**
	 * Save user id to config.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function save_user_id() {
		$user = $this->get_user();

		$this->login_data['userid'] = $user['id'];
		update_option( 'neoship_login', $this->login_data );

		$has_gls = false;
		foreach ( $user['user_shipper_price_lists'] as $value ) {
			if ( 'GLS' === $value['shipper']['shortcut'] ) {
				$has_gls = true;
				break;
			}
		}
		update_option( 'neoship_has_gls', $has_gls );
	}

	/**
	 * Return headers with json type and bearer token.
	 *
	 * @since  3.0.0
	 * @access private
	 */
	private function get_headers() {
		return [
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $this->access_data['token'],
		];
	}

	/**
	 * Get user.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_user() {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url      = NEOSHIP3_API_URL . '/user/';
		$response = wp_remote_get( $url, [
			'headers' => $this->get_headers(),
		] );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again' ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Create new packages.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param array $packages Array of packages details.
	 */
	public function create_packages( $packages, $shipper_id ) {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url  = NEOSHIP3_API_URL . '/package/bulk/' . $shipper_id;
		$args = [
			'headers' => $this->get_headers(),
			'body' => json_encode($packages)
		];

		$response = wp_remote_post( $url, $args );

		return $response;
	}

	/**
	 * Handle sticker print.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param string $reference_numbers Packages reference numbers.
	 * @param string $position Sticker position on A4 paper.
	 */
	public function print_sticker( $reference_numbers, $position = 1 ) {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url  = NEOSHIP3_API_URL . '/package/bulk/';
		$args = [
			'headers' => $this->get_headers(),
			'body' => json_encode([
				'action' => 'send_print_sticker',
				'reference_numbers' => $reference_numbers,
				'sticker_position' => $position,
			])
		];

		$response = wp_remote_post( $url, $args );
		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Handle acceptance protocol print.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 */
	public function print_acceptance_protocol() {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url  = NEOSHIP3_API_URL . '/package/bulk/';
		$args = [
			'headers' => $this->get_headers(),
			'body' => json_encode([
				'action' => 'daily_closing',
			])
		];

		$response = wp_remote_post( $url, $args );
		return json_decode( wp_remote_retrieve_body( $response ), true );
	}




























	/**
	 * Save user id to config.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_user_address() {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url      = NEOSHIP3_API_URL . '/user/?' . http_build_query( $this->access_data );
		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again' ) );
		}

		$user = json_decode( wp_remote_retrieve_body( $response ), true );

		$this->login_data['userid'] = $user['id'];
		update_option( 'neoship_login', $this->login_data );

		$user['address']['state'] = $user['address']['state']['id'];
		unset( $user['address']['id'] );
		$user['address']['zIP'] = $user['address']['zip'];
		unset( $user['address']['zip'] );
		return $user['address'];
	}

	/**
	 * Get user credit.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_user_credit() {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url      = NEOSHIP3_API_URL . '/user/?' . http_build_query( $this->access_data );
		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return 0;
		}

		$user = json_decode( wp_remote_retrieve_body( $response ), true );
		return round( $user['kredit'], 2 );
	}

	/**
	 * Get states code array by ids.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_states_ids() {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url      = NEOSHIP3_API_URL . '/state/?' . http_build_query( $this->access_data );
		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again' ) );
		}

		$states            = json_decode( wp_remote_retrieve_body( $response ), true );
		$state_ids_by_code = array();
		foreach ( $states as $state ) {
			$state_ids_by_code[ $state['code'] ] = $state['id'];
		}
		return $state_ids_by_code;
	}

	/**
	 * Get currencies by code.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_currencies_ids() {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url      = NEOSHIP3_API_URL . '/currency/?' . http_build_query( $this->access_data );
		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again', 'neoship' ) );
		}

		$currencies           = json_decode( wp_remote_retrieve_body( $response ), true );
		$currency_ids_by_code = array();
		foreach ( $currencies as $currency ) {
			$currency_ids_by_code[ $currency['code'] ] = $currency['id'];
		}

		return $currency_ids_by_code;
	}

	/**
	 * Output error message.
	 *
	 * @since  3.0.0
	 * @access private
	 *
	 * @param string $message Message to print.
	 * @param bool   $exit Exit after print.
	 */
	private function error_message( $message, $exit = true ) {
		?>
			<div class="notice error notice-error is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		<?php
		if ( $exit ) {
			exit();
		}
	}

	/**
	 * Output success message.
	 *
	 * @since  3.0.0
	 * @access private
	 *
	 * @param string $message Message to print.
	 * @param bool   $exit Exit after print.
	 */
	private function success_message( $message, $exit = true ) {
		?>
			<div class="notice updated notice-success is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		<?php
		if ( $exit ) {
			exit();
		}
	}


	/**
	 * Handle pdf response.
	 *
	 * @since  3.0.0
	 * @access private
	 *
	 * @param array  $response Response status.
	 * @param string $name File name.
	 */
	private function handle_pdf( $response, $name = 'stickers' ) {
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'neoship_error' => '1',
						'error'         => __( 'You are trying Neoship action on orders which are not imported to neoship', 'neoship' ),
					),
					admin_url( 'edit.php?post_type=shop_order' )
				)
			);
			exit();
		}
		header( 'Cache-Control: public' );
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . $name . '.pdf"' );
		header( 'Content-Length: ' . strlen( wp_remote_retrieve_body( $response ) ) );
		echo wp_remote_retrieve_body( $response );
		exit();
	}

	/**
	 * Get parcelshops.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param bool $all Get all data.
	 */
	public function get_parcel_shops( $all = false ) {
		$url      = NEOSHIP3_API_URL . '/public/parcelshop/';
		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again', 'neoship' ) );
		}

		$parcel_shops = json_decode( wp_remote_retrieve_body( $response ), true );

		$return_parcel = array();
		foreach ( $parcel_shops as $parcelshop ) {
			if ( $all ) {
				$return_parcel[ $parcelshop['id'] ] = $parcelshop;
			} else {
				$return_parcel[ $parcelshop['id'] ] = $parcelshop['address']['city'] . ', ' . $parcelshop['address']['company'];
			}
		}
		return $return_parcel;
	}

	/**
	 * Get gls parcelshops.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param bool $all Get all data.
	 */
	public function get_gls_parcel_shops( $all = false ) {
		$url      = NEOSHIP3_API_URL . '/public/glsparcelshop/';
		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again', 'neoship' ) );
		}

		$parcel_shops = json_decode( wp_remote_retrieve_body( $response ), true );

		$return_parcel = array();
		foreach ( $parcel_shops as $parcelshop ) {
			if ( $all ) {
				$return_parcel[ $parcelshop['parcelShopId'] ] = $parcelshop;
			} else {
				$return_parcel[ $parcelshop['parcelShopId'] ] = $parcelshop['cityName'] . ', ' . $parcelshop['name'];
			}
		}
		return $return_parcel;
	}
}
