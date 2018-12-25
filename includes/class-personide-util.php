
<?php 

class Personide_Util {

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

}

?>