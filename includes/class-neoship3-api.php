<?php
/**
 * The file that defines the neoship 3 api
 *
 * A class definition that includes attributes and functions used to communication with neoship api
 *
 * @link  https://www.neoship.sk
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
 * @author     IT <it@neoship.sk>
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
			$this->error_message( __( 'Please setup neoship login credentials', 'neoship' ), ! $test );
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
        $has_packeta = false;
        $has_123 = false;
        $has_dpd = false;
		foreach ( $user['user_shipper_price_lists'] as $value ) {
			if ( 'GLS' === $value['shipper']['shortcut'] ) {
				$has_gls = true;
			}
            if ( 'Packeta' === $value['shipper']['shortcut'] ) {
                $has_packeta = true;
            }
            if ( '123' === $value['shipper']['shortcut'] ) {
                $has_123 = true;
            }
			if ( 'DPD' === $value['shipper']['shortcut'] ) {
				$has_dpd = true;
			}
		}

		update_option( 'neoship_has_gls', $has_gls );
		update_option( 'neoship_has_packeta', $has_packeta );
		update_option( 'neoship_has_123', $has_123 );
		update_option( 'neoship_has_dpd', $has_dpd );
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
	public function print_acceptance_protocol($action) {
		if ( false === $this->access_data ) {
			$this->login();
		}

		$url  = NEOSHIP3_API_URL . '/package/bulk/';
        if ($action == '123') {
            $date = new DateTime('now');
            $args = [
                'headers' => $this->get_headers(),
                'body' => json_encode([
                    'action' => 'k123_acceptance_protocol',
                    'date' => $date->format('Y-m-d')
                ])
            ];
        } else {
            $args = [
                'headers' => $this->get_headers(),
                'body' => json_encode([
                    'action' => 'daily_closing',
                ])
            ];
        }

		$response = wp_remote_post( $url, $args );
		return json_decode( wp_remote_retrieve_body( $response ), true );
	}


	/**
	 * Get parcelshops.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param bool $all Get all data.
	 */
	public function get_parcel_shops( $shipper_id ) {
		$url      = NEOSHIP3_API_URL . '/all/parcelshop/list/' . $shipper_id;
		$response = wp_remote_get( $url, [
			'headers' => [
				'Content-Type' => 'application/json'
			],
		] );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again', 'neoship' ) );
		}

		$parcel_shops = json_decode( wp_remote_retrieve_body( $response ), true );

		$return_parcel = array();
		foreach ( $parcel_shops as $parcelshop ) {
			$return_parcel[ $parcelshop['id'] ] = $parcelshop['city'] . ', ' . $parcelshop['name'];
		}
		return $return_parcel;
	}

	/**
	 * Get parcelshop.
	 *
	 * @since  3.0.0
	 * @access public
	 *
	 * @param bool $all Get all data.
	 */
	public function get_parcel_shop( $parcelshop_id ) {
		$url      = NEOSHIP3_API_URL . '/all/parcelshop/' . $parcelshop_id;
		$response = wp_remote_get( $url, [
			'headers' => [
				'Content-Type' => 'application/json'
			],
		] );

		if ( wp_remote_retrieve_response_code( $response ) === 404 ) {
			return null;
		}

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->error_message( __( 'Something is wrong. Please refresh the page and try again', 'neoship' ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Get user credit.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_user_credit() {
		$user = $this->get_user();
		return round( $user['credit'], 2 );
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
}
