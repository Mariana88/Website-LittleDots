<?php		
	echo '<img src="css/front/img/background.png" original_image="css/front/img/background.png" original_height="' . Pictures::get_pic_height($_SERVER['DOCUMENT_ROOT'] . '/css/front/img/background.png') . '" original_width="' . Pictures::get_pic_width($_SERVER['DOCUMENT_ROOT'] . '/css/front/img/background.png') . '" original_height_backup="' . Pictures::get_pic_height($_SERVER['DOCUMENT_ROOT'] . '/css/front/img/background.png') . '" original_width_backup="' . Pictures::get_pic_width($_SERVER['DOCUMENT_ROOT'] . '/css/front/img/background.png') . '" id="site_background" style="display: none;"/>';

	echo '<img src="css/front/img/back_red1.png" menuplace="1" class="menu_red_hover"/>
		<img src="css/front/img/back_red2.png" menuplace="2" class="menu_red_hover"/>
		<img src="css/front/img/back_red3.png" menuplace="3" class="menu_red_hover"/>
		<img src="css/front/img/back_red4.png" menuplace="4" class="menu_red_hover"/>
		<img src="css/front/img/back_red5.png" menuplace="5" class="menu_red_hover"/>
		<img src="css/front/img/back_red6.png" menuplace="6" class="menu_red_hover"/>';
?>