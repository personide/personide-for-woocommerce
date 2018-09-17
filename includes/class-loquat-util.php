
<?php 

class Loquat_Util {

	public static function get_event($type, $entityType, $entityId, $properties, $targetEntityId = NULL, $targetEntityType = NULL) {
		
		$template = "
		{
			event: '$type',
			entityType: '$entityType',
			entityId: '$entityId',
			" . ( ($targetEntityId !== NULL) ? "targetEntityId: '$targetEntityId'," : "" ) . "
			" . ( ($targetEntityType !== NULL) ? "targetEntityType: '$targetEntityType'," : "" ) . "
			properties: $properties
		}
		";

		return $template;
	}

	public static function enqueue_script($code, $store) {

		$store = 'loquat_' . $store . '_script';

		if(empty($_SESSION[$store]))
			$_SESSION[$store] = '';

		$_SESSION[$store] = $_SESSION[$store]."\n\n $code";
	}

}

?>