
<?php 

class Personide_Util {

	public static function get_var_script() {
		return "Personide.setKey('". Personide_Util::get_option('access_token') ."')
		Personide.set('currentPage', '". Personide_Util::get_pagetype() ."')";
	}

	public static function get_event($type, $entityType, $entityId, $properties = NULL, $targetEntityType = NULL, $targetEntityId = NULL) {
		
		$template = "
		{
			event: '$type',
			entityType: '$entityType',
			entityId: '$entityId',
			" . ( ($targetEntityId !== NULL) ? "targetEntityId: '$targetEntityId'," : "" ) . "
			" . ( ($targetEntityType !== NULL) ? "targetEntityType: '$targetEntityType'," : "" ) . "
			" . ( ($properties !== NULL) ? "properties: $properties," : "" ) . "
		}
		";

		return $template;
	}

	public static function enqueue_script($code, $store) {

		$store = 'personide_' . $store . '_script';

		if(empty($_SESSION[$store]))
			$_SESSION[$store] = '';

		$_SESSION[$store] = $_SESSION[$store]."\n\n $code";
	}

	public static function get_option($key) {
		$options = get_option('personide');
		return (isset($options[$key]) && !empty($options[$key])) ? $options[$key] : NULL;
	}

	public static function get_pagetype() {
		global $wp_query;
		$loop = 'other';

		if ( $wp_query->is_page ) {
			$loop = is_front_page() ? 'front' : 'page';
		}

		if ( is_product() ) {
			$loop = 'product';
		} elseif ( is_shop() ) {
			$loop = 'listing';
		} elseif ( is_product_category() ) {
			$loop = 'category';
		} elseif ( is_product_tag() ) {
			$loop = 'tag';
		} elseif ( is_product_taxonomy() ) {
			$loop = 'taxonomy';
		} elseif ( is_cart() ) {
			$loop = 'cart';
		} elseif ( is_checkout() ) {
			$loop = 'checkout';
		} elseif ( $wp_query->is_search ) {
			$loop = 'search';
		} elseif ( $wp_query->is_404 ) {
			$loop = 'notfound';
		}

		return $loop;
	}

}

?>