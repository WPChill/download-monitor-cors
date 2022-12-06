<?php
/*
	Plugin Name: Download Monitor - CORS
	Plugin URI: https://www.download-monitor.com/
	Description: Allows cross site origin from the specified URL
	Version: 1.0.0
	Author: WPChill
	Author URI: https://wpchill.com
	License: GPL v2
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Plugin main class.
 */
class DLM_CORS {

	const VERSION = '1.0.0';
	/**
	 * Access Origin
	 *
	 * @var string
	 */
	public $dlm_cors_request_url = '';

	/**
	 * DLM's endpoint.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $endpoint = '';

	/**
	 * DLM's endpoint value.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $ep_value = '';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_filter( 'dlm_settings', array( $this, 'requester_url_setting' ) );
			add_action( 'admin_init', array( $this, 'check_dependencies' ) );
		}

		$endpoint = get_option( 'dlm_download_endpoint' );
		$ep_value = get_option( 'dlm_download_endpoint_value' );

		$this->endpoint = ( $endpoint ) ? $endpoint : 'download';
		$this->ep_value = ( $ep_value ) ? $ep_value : 'ID';

		$dlm_cors_request_url = get_option( 'dlm_cors_requester_url' );

		if ( $dlm_cors_request_url && '' != $dlm_cors_request_url ) {

			$this->dlm_cors_request_url = untrailingslashit( $dlm_cors_request_url );
			add_filter( 'dlm_download_headers', array( $this, 'dlm_set_cors_policy' ) );
			add_action( 'send_headers', array( $this, 'dlm_set_wp_cors_policy' ) );
		}

	}

	/**
	 * Check if Download Monitor is installed
	 *
	 * @since 1.0.0
	 */
	public function check_dependencies() {
		if ( class_exists( 'WP_DLM' ) ) {
			return true;
		}
		add_action( 'admin_notices', array( $this, 'dependency_notice' ), 8 );
	}

	/**
	 * Add dependency notice
	 *
	 * @since 1.0.0
	 */
	public function dependency_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Download Monitor - Cors requires Download Monitor to be active in order to work.', 'dlm-cors' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add plugin's settings.
	 *
	 * @param array $settings DLM's settings.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function requester_url_setting( $settings ) {
		$settings['advanced']['sections']['access']['fields'][] = array(
			'name'     => 'dlm_cors_requester_url',
			'std'      => '',
			'label'    => __( 'CORS Requester URL', 'dlm-cors' ),
			'desc'     => __( 'Specify the requester URL so we can allow cross site download requests comming from the specified source. Add <code>*</code> in order to accept all. ', 'dlm-cors' ),
			'type'     => 'text',
			'priority' => 70
		);

		return $settings;
	}

	/**
	 * Set our headers
	 *
	 * @param array $headers DLM's headers.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function dlm_set_cors_policy( $headers ) {

		$headers['Access-Control-Allow-Origin']  = $this->dlm_cors_request_url;
		$headers['Access-Control-Allow-Headers'] = 'dlm-xhr-request';

		return $headers;
	}
	/**
	 * Set our headers
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function dlm_set_wp_cors_policy() {
		global $wp;
		// If DLM not present then bounce. There is a possibility that the settings are here but the plugin is not active/installed.
		if ( ! class_exists( 'WP_DLM' ) ) {
			return;
		}

		// Only set the headers if it is a request for a download.
		if ( ! empty( $wp->query_vars[ $this->endpoint ] ) && ( ( null === $wp->request ) || ( '' === $wp->request ) || ( strstr( $wp->request, $this->endpoint . '/' ) ) ) ) {
			header( 'Access-Control-Allow-Origin: ' . $this->dlm_cors_request_url );
			header( 'Access-Control-Allow-Headers: dlm-xhr-request' );
		}
	}
}

new DLM_CORS();
