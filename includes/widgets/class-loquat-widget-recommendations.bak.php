
<?php 

class Loquat_Widget_Recommendations extends WC_Widget {

	public function __construct() {

		$this->widget_cssclass = 'loquat widget_recommendations';
		$this->widget_description = 'Loquat Personalized Product Recommendations';
		$this->widget_id = 'loquat_widget_recommendations';
		$this->widget_name = 'Loquat Recommendations';

		parent::construct();
	}

	public function widget($args, $instance) {

		$this->widget_start( $args, $instance );

		echo '
			<div class="container">
				<div class="template item">
					<div class="picture">Checks</div>
					<div class="details">
						<span></span>
					</div>
				</div>
			</div>
		';

		$this->widget_end( $args );

	}

}

?>