
<?php 

class Personide_Widget_Recommendations extends WP_Widget {

	public function __construct() {

		$options = array(
			'classname' => 'personide_hotslot',
			'description' => 'Personide Personalized Product Recommendations'
		); 

		// $this->widget_cssclass = 'personide widget_recommendations';
		// $this->widget_description = 'Personide Personalized Product Recommendations';
		// $this->widget_id = 'personide_widget_recommendations';
		// $this->widget_name = 'Personide Recommendations';

		parent::__construct('Personide_Widget_Recommendations', 'Personide Product Recommendations', $options);
	}

	public function widget($args, $instance) {

		echo $args['before_widget'];

		echo '
			<div class="container">
				<h1 class="center">You Must Have</h1>
				<div class="template item personide-product">
					<a class="personide-product__link" href="">
						<div class="personide-product__picture" style="background-image: url(http://localhost/store/wp-content/uploads/2018/06/mekamon_berserker_robot2_-_tejar.jpg)"></div>
						<div class="personide-product__details">
							<p class="personide-product__name">Jingle Bells</p>
							<p class="personide-product__price">Rs. 200</p>
						</div>
					</a>
				</div>
			</div>
		';

		echo $args['after_widget'];
	}
}

?>