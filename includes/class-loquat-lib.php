<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Loquat
 * @subpackage Loquat/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Loquat
 * @subpackage Loquat/includes
 * @author     Quanrio <contact@quanrio.com>
 */

class Loquat_Lib {

	private $logger;
	private $api_url;

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->logger = wc_get_logger();
		$this->api_url = 'localhost:9001';
	}

	private function buildUrl($query = array()) {
		return $this->api_url . '/products' . (empty($querystring)) ? '?' : '' . $querystring;
	}


	private function request( $method = 'get', $payload = array() ) {

		$curl_method = '';

		$url = $this->buildUrl(array(
			'id' => $payload['id']
		));

		if ($method === 'post')
			$curl_method = CURLOPT_POST;

		if ($method === 'put')
			$curl_method = CURLOPT_PUT;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . $endpoint);

		if( $method !== 'get' ) {
			curl_setopt($ch, $curl_method, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		return $response;
	}

	public function newProduct( $data ) {
		$this->logger->debug( $data );
		return $this->request('post', $data);
	}

	public function updateProduct( $data = array() ) {
		return $this->request('put', $data);
	}

}