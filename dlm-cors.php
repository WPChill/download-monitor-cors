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

class DLM_CORS {

	const VERSION = '1.0.0';
    public $dlm_cors_request_url = '';

	public function __construct() {

		$this->setup();
	}

	/**
	 * Check if Download Monitor is installed and up to date
	 */
	public function check_dependencies() {
		if ( class_exists( 'WP_DLM' ) ) {
			return true;
		}
		add_action( 'admin_notices', array( $this, 'dependency_notice' ), 8 );
	}

	/**
	 * Add dependency notice
	 */
	public function dependency_notice() {
		?>
		<div class="error">
			<p><?php _e( 'Download Monitor - DLM Cors requires Download Monitor to be active in order to work.', 'dlm-ninja-forms' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Setup the plugin
	 */
	public function setup() {

        if( is_admin() ){
            add_filter( 'dlm_settings', array( $this, 'requester_url_setting' ) );
        }

        $dlm_cors_request_url = get_option( 'dlm_cors_requester_url' );

        if( $dlm_cors_request_url && '' != $dlm_cors_request_url ){
            
            $this->dlm_cors_request_url = untrailingslashit( $dlm_cors_request_url );
            add_filter( 'dlm_download_headers', array( $this, 'dlm_set_cors_policy' ) );
            add_action( 'send_headers', array( $this, 'dlm_set_wp_cors_policy' ) );
        }


	}

    public function requester_url_setting( $settings ){
        $settings['advanced']['sections']['misc']['fields'][] = array(
            'name'     => 'dlm_cors_requester_url',
            'std'      => '',
            'label'    => __( 'CORS Requester URL', 'download-monitor' ),
            'desc'     => __( 'Specify the requester URL so we can allow cross site download requests comming from the specified source.', 'download-monitor' ),
            'type'     => 'text',
            'priority' => 70
       );

        return $settings;
    }
 

    public function dlm_set_cors_policy( $headers ){

        $headers['Access-Control-Allow-Origin']  = $this->dlm_cors_request_url;
        $headers['Access-Control-Allow-Headers'] = 'dlm-xhr-request';
        
        return $headers;
    }
    
   
    public function dlm_set_wp_cors_policy(){

        header( 'Access-Control-Allow-Origin: ' . $this->dlm_cors_request_url );
        header( 'Access-Control-Allow-Headers: dlm-xhr-request' );
    }


}


new DLM_CORS();
