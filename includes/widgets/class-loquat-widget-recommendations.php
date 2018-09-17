
<?php 

class Loquat_Widget_Recommendations extends WP_Widget {

	public function __construct() {

		$options = array(
			'classname' => 'loquat_recommendations',
			'description' => 'Loquat Personalized Product Recommendations'
		); 

		// $this->widget_cssclass = 'loquat widget_recommendations';
		// $this->widget_description = 'Loquat Personalized Product Recommendations';
		// $this->widget_id = 'loquat_widget_recommendations';
		// $this->widget_name = 'Loquat Recommendations';

		parent::__construct('Loquat_Widget_Recommendations', 'Loquat Product Recommendations', $options);
	}

	public function widget($args, $instance) {

		echo $args['before_widget'];

		echo '
			<div class="container">
				<h1 class="center">You Must Have</h1>
				<div class="template item">
					<div class="picture"></div>
					<div class="details">
						<span></span>
					</div>
				</div>
			</div>
		';

		echo $args['after_widget'];
	}
}

?>