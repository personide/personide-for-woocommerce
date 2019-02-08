
<?php 

class Personide_Util {

	public static function get_var_script() {
		return "
		Personide.setKey('". Personide_Util::get_option('access_token') ."')
		Personide.set('currentPage', '". Personide_Util::get_pagedata('type') ."')
		";
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

	public static function get_option($key) {
		try {
			$options = get_option('personide');
			return (isset($options[$key]) && !empty($options[$key])) ? $options[$key] : NULL;	
		} catch(Exception $e) {
			$logger = wc_get_logger();
			$logger->debug(print_r($e, TRUE));
		}
		
	}

	public static function get_pagedata($key = NULL) {

		$data = array();

		global $wp_query, $post;
		$query_object = $wp_query->get_queried_object();

		$type = 'other';
		$properties = NULL;
		$id = '';

		if ( $wp_query->is_page ) {
			$type = is_front_page() ? 'front' : 'page';
		}

		if ( is_product() ) {
			$type = 'item';
			$data['type'] = 'product';
			$product = wc_get_product( $post->ID );
			$id = $product->get_id();

		} elseif ( is_shop() ) {
			$type = 'listing';

		} elseif ( is_product_category() ) {
			$type = 'category';
			$id = $query_object->term_id;
			$properties = array(
				'name' => $query_object->name
			);

		} elseif ( is_product_tag() ) {
			$type = 'tag';

		} elseif ( is_product_taxonomy() ) {
			$type = 'taxonomy';

		} elseif ( is_cart() ) {
			$type = 'cart';

		} elseif ( is_checkout() ) {
			$type = 'checkout';

		} elseif ( $wp_query->is_search ) {
			$type = 'search';

		} elseif ( $wp_query->is_404 ) {
			$type = 'notfound';
		}

		$data["properties"] = $properties;
		$properties = $properties == NULL ? $properties : json_encode($properties);
		
		$data['event'] = self::get_event( 'view', 'user', '', $properties, $type, $id);
		$data['type'] = !empty($data['type']) ? $data['type'] : $type;

		return ($key) ? $data[$key] : $data;
	}
}

?>