<?php
/*

Plugin Name: Tidy.ro
Plugin URI: http://www.tidy.ro
Description: Plugin de management Tidy.ro
Version: 1.3
Author: P. Razvan
Author URI: http://www.tidy.ro
*/



define('tidy_plugin_version', '1.3');


add_action( 'wp_login', 'td_check_for_version', 10, 2 ); 

function td_check_for_version($user_login, $user){


	if(is_super_admin( $user->data->ID )){

		$string_to_send = "action=get_latest_plugin_version";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,'http://tidy.ro/processor');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
			$string_to_send  );

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
			
		$answer = json_decode($server_output);

		update_option('td_current_version', $answer->version);
		update_option('td_message', $answer->message);

	}

}


add_action( 'admin_init', 'td_version_reminder' );



function td_version_reminder(){

	$td_current_version = get_option('td_current_version');
	if( $td_current_version != tidy_plugin_version ){
		add_action( 'admin_notices', 'td_update_notice');
	}

} 

function td_update_notice(){

		$html  =  '<div class="error fade">';
		$msg   = get_option('td_message', $answer->message);
		$html .= $msg;
		$html .= '</div>';

		echo $html;

}



if ( ! defined( 'ABSPATH' ) ) die();








load_plugin_textdomain('atc-menu', false, dirname(plugin_basename(__FILE__)) . '/languages/');

register_activation_hook( __FILE__, 'td_atc_install' );
register_uninstall_hook( __FILE__, 'td_atc_uninstall' );
//register_deactivation_hook( __FILE__, 'td_deactivate' );

function td_my_enqueue($hook) {

    wp_enqueue_script( 'my_custom_script', plugins_url( 'tidy/chart/Chart.js') );
}


add_action( 'admin_enqueue_scripts', 'td_my_enqueue' );

add_action('admin_head', 'td_set_image_size');
add_action('admin_init', 'td_set_image_size');
add_action( 'init', 'td_set_image_size' );


function td_set_image_size(){
	add_image_size( 'td_thumb', 300, 300, true );
}




add_action( 'admin_notices', 'activation_notice');

function activation_notice(){

	$sk = get_option( 'td_secret_key');

	if( strlen($sk) < 2 ){

		$html =  '<div class="error fade">';
		
		$html .= '<p style="font-style:italic; font-size:16px;">Pluginul <span style="color:red">tidy.ro</span> nu a fost setat. </p>';

		$html .= '<strong>Cum sa setezi pluginul ?</strong><br/><hr/><br/>';
		$html .= '<strong>1.</strong> Duceti-va la pagina  <a href="'.site_url().'/wp-admin/admin.php?page=tidy" >tidy.ro</a>, din meniul lateral . <br/> <br/>';
		$html .= '<strong>2.</strong> Introduce-ti userul si parola din reteaua tidy.ro si apasati  "Logare".  <br/> <br/>';
		$html .= '<strong>3.</strong> Daca date de logare sunt corecte, selectati website-ul dorit".<br/><br/> ';
		$html .= '<p style="font-weight:bold">Setarea a fost finalizata! Felicitari !</p>';


		$html .= '</div>';
		echo $html;

	}

}

add_filter('manage_posts_columns', 'td_ST4_columns_head');
add_action('manage_posts_custom_column', 'td_ST4_columns_content', 10, 2);


function your_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=tidy">Setari</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );



// ADD NEW COLUMN
function td_ST4_columns_head($defaults) {
    $defaults['tidy'] = 'tidy.ro';
    return $defaults;
}
 

function td_ST4_columns_content($column_name, $post_ID) {
    if ($column_name == 'tidy') {
    	global $wpdb;
  			$td_article_url = end($wpdb->get_results( 'SELECT *  FROM '.$wpdb->prefix.'tidy_connections  WHERE ID_post =  '.$post_ID.'  LIMIT 1'));

			if( isset( $td_article_url->URL ) ) {
				echo '<p style="color:#7ad03a">Submited</p>';
			}
			else{
				echo '<p style="color:#a00">Not submited</p>';
			}
    }
}


// add page visible to editors
add_action( 'admin_menu', 'td_register_my_page' );

function td_register_my_page(){
    add_menu_page( 'tidy.ro', 'tidy.ro', 'edit_others_posts', 'tidy', 'td_my_page_function', plugins_url( 'tidy/icon.png' ), 99999 ); 
}

// modify capability
function td_my_page_capability( $capability ) {
	return 'edit_others_posts';
}
add_filter( 'option_page_capability_my_page_slug', 'td_my_page_capability' );


function td_my_page_function(){
	include('tabs/main.php');
}


function td_deactivate() {

	// delete_option( 'td_atc_settings' );

}



function td_atc_uninstall() {

	delete_option( 'td_atc_settings' );

}

function td_atc_install() {

$installed_ver = get_option( "td_atc_settings" );

	if ( $installed_ver != 1 ) {
	
		global $wpdb;
		$sql = 'CREATE TABLE IF NOT EXISTS `'. $wpdb->prefix.'tidy_connections` (
				  `ID_post` int(10) NOT NULL,
				  `URL` varchar(200) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		';

		$wpdb->query( $sql );

		add_option( 'td_atc_settings', 1 );
	}




	
}

// Hook for adding admin menus
if ( is_admin() ){ // admin actions

  // Hook for adding admin menu
  add_action( 'admin_menu', 'td_atc_op_page' );
  add_action( 'admin_init', 'td_atc_register_setting' );

// Display the 'Settings' link in the plugin row on the installed plugins list page
	//add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'atc_admin_plugin_actions', -10);


} else { // non-admin enqueues, actions, and filters

}


// action function for above hook
function td_atc_op_page() {

    // Add a new submenu under Settings:
   //add_menu_page( 'tidy.ro', 'tidy.ro', 'edit_others_posts', 'tidy', 'my_page_function', plugins_url( 'tidy/icon.png' ), 99999 ); 
   add_submenu_page('tidy', 'Statistics', 'Statistics', 'manage_options', 'tidy-statistics', 'td_atc_settings_page'); 

   // add_options_page(__('tidy Widget Manager','atc-menu'), __('tidy Widget Manager','atc-menu'), 'manage_options', __FILE__, 'atc_settings_page');
}
function td_atc_register_setting() {	
register_setting( 'atc_options', 'atc_settings' );
}

// atc_settings_page() displays the page content 
function td_atc_settings_page() { 
	include('tabs/statistics.php');
 }



/*  doest the processing here*/


/* PROCCESS THE ADD WIDGET TO CATEGORIES */
if(isset($_POST['widget_cat_script'])){


	$id = $_POST['widget_script_id'];
	$content = $_POST['widget_cat_script'];

	$category_widgets = get_option('category_widgets');
	

	if($category_widgets == '')
		$cat_info = array();
	else
		$cat_info = json_decode( $category_widgets,true );

	$cat_info[$_POST['widget_script_id_ref']] = $id;


	update_option( 'sexcat_'.$id, $content );
	update_option('category_widgets', json_encode($cat_info));
	td_clear_w3();

}


if( isset($_POST['remove_from_category'])){


	
	$category_widgets = get_option('category_widgets');
	$cat_info = json_decode( $category_widgets,true );

	delete_option( 'sexcat_'.$_POST['widget_script_id'] );
	
	unset($cat_info[$_POST['widget_script_id_ref']]) ;


	update_option('category_widgets', json_encode($cat_info));
	td_clear_w3();


}

if(isset($_POST['filter_period'])){
	
	update_option('statistics_period', $_POST['show_period']);

}

function td_clear_w3(){

	if (function_exists('w3tc_dbcache_flush')) { w3tc_dbcache_flush(); }
	if ( function_exists( 'w3tc_objectcache_flush' ) ){ w3tc_objectcache_flush(); }
}

if(isset($_POST['widget_id_add'])){

	$widget_id = $_POST['widget_id_add'];
	update_option( 'td_widget_all_id', $_POST['widget_id_add'] );
	update_option( 'td_widget_all_script', $_POST['widget_id_add_script'] );
	
	td_clear_w3();
}

if(isset($_POST['widget_id_remove'])){

	$widget_id = $_POST['widget_id_remove'];

	delete_option( 'td_widget_all_id' );
    delete_option( 'td_widget_all_script' );	

	td_clear_w3();
}


/* PROCESS THE ADD SECRET KEY */

if(isset($_POST['td_update_account'])){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,'http://tidy.ro/processor');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	                "action=check_secret_key&td_username=".$_POST['td_username'].'&td_password='.$_POST['td_password']  
	            );

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec ($ch);
	curl_close ($ch);
	


	global $answer;
	$answer = json_decode($server_output);


	if ($answer->error == "0") { 

		update_option( 'td_sites', json_encode($answer->websites) );
		update_option( 'td_username', $_POST['td_username'] );
		update_option( 'td_password', $_POST['td_password'] );
	} 
		else { 

			echo '<script>alert("Combinatie username/parola incorecta. ")</script>';
	}

}



/* PROCESS THE SELECT WEBSITE FORM */

if( isset($_POST['td_select_web'])):

	update_option( 'td_sweb', $_POST['td_sweb'] );

	$json = array();
	$json['td_sweb'] =  $_POST['td_sweb'] ;
	$json['td_skey'] =  $sk; ;


	$sw = get_option( 'td_sites' );
	$sites = json_decode($sw );
	$sk = '';
	$sn = '';
	$su = '';

	foreach( $sites as $site):
		if($site->ID == $_POST['td_sweb'] ):
			$sk = $site->secret_key;
			$su = $site->website_url;
			$sn = $site->website;
		endif;
	endforeach;

	$json = array();

	$json['td_sweb'] =  $_POST['td_sweb'] ;
	$json['td_skey'] =  $sk ;
	$json['td_name'] =  $sn ;
	$json['td_url']  =  $su ;

	update_option( 'td_selected_web' , json_encode( $json) );
	update_option( 'td_secret_key' ,  $sk );



endif;

/* PROCESS THE SUBMIT FORM ! */

if(isset($_POST['td_submit_article_final_form'])){

	$sk = get_option( 'td_secret_key');

	$article_url = $_POST['td_article_url'];
	$article_name = $_POST['td_article_title'];
	$article_content = $_POST['td_article_title_content'];
	$article_thumb  = $_POST['td_article_thumb'];

	global $wpdb;
	$size = sizeof($wpdb->get_results( 'SELECT *  FROM '.$wpdb->prefix.'tidy_connections   WHERE ID_post  ='. (int)$_POST['td_post_id']  ));

	if( $size == 0){
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'tidy_connections ( ID_post, URL) VALUES ( '.(int)$_POST['td_post_id'].', "'.$article_url.'" )');
	}
	else{
		$wpdb->query('UPDATE '.$wpdb->prefix.'tidy_connections SET URL = "'.$article_url.'"  WHERE ID_post = '.(int)$_POST['td_post_id']);
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,'http://tidy.ro/processor');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"action=submitarticle&secret_key=".$sk."&article_url=".$article_url."&article_name=".$article_name."&article_content=".$article_content."&article_thumb=".$article_thumb );

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec ($ch);
	curl_close ($ch);



}



function td_hook_content($content){


	$widget_all = get_option( 'td_widget_all_script' );

	$categories = get_categories();

	$c = '';
	foreach( $categories as $cat ):
	
		global $post;

		$content_widget = get_option( 'sexcat_'.$cat->term_id );
		$cat_array = array(0 => $cat->term_id); 

		if(  in_category( $cat_array ) &&  is_single() && ( $content_widget  != '') ){
			$c =  '<div >'.$content_widget .'</div>';
		}
		else{

			$c =  '<div>'.$widget_all .'</div>';
		}

		endforeach;
		
		if( is_single() )
			$content .= stripslashes(urldecode($c));

	return $content;


}



add_filter( 'the_content', 'td_hook_content' , -1);

function td_get_the_excerpt($post_id) {
  global $post;  
  $save_post = $post;
  $post = get_post($post_id);
  $output = get_the_excerpt();
  $post = $save_post;
  return $output;
}



add_action( 'post_submitbox_misc_actions', 'td_custom_button' );
add_action( 'in_admin_header', 'td_posts_widget_header' );

function td_posts_widget_header(){
$html = '';

if(isset($_GET['post'])){

			$post_info = get_post( $_GET['post'] );
			$url_img = wp_get_attachment_url( get_post_thumbnail_id($_GET['post'], 'td_thumb') );
			
			$html .= '<div id="td_overlay" style="position:fixed; z-index:999; display:none; height:2500px; width:2500px; left:0px; top:0px; background-image:url('.plugins_url('tidy/transparent_bg.png').');"></div>';
			$html .= '<div style="width:600px; display:none;  position:fixed; z-index:9999; left:50%; padding:10px; margin-left:-300px; top:20%; background-color:#fff; border:1px solid #ccc" id="submit_to_td_form">
						<form method="post" action="">
						<input type="hidden" name="td_post_id" value="'.$_GET['post'].'" />
						<input type="hidden" name="td_submit_article_final_form" id="td_submit_article_final_form" value="1">
						<a href="#" style="position:absolute; right:-20px; top:-20px;" id="td_form_close"><img src="'.plugins_url('tidy/close.png').'" s></a>
						
						<div style="float:left">
							<div id="td_thumb_wrapper">'.
								get_the_post_thumbnail($_GET['post'], 'td_thumb', array('id' => 'article_thumb_image','style'=>' width:200px; height:auto; min-height:150px; display:block'));
								if(strlen($url_img) <= 2){
									$html .= '<img src="'.plugins_url('tidy/no_image_found.jpg').'" id="article_thumb_image" style="width:200px; height:200px;">';
								}

					$html .='
							</div>
							
							<br/><br/>
							<input type="submit" class="button-primary" value="Trimite catre tidy.ro" id="td_submit_article_final" name="td_submit_article_final">
						</div>	

						<div style="float:left; width:387px; margin-left:10px;">
							<p style="margin-top:0px;"><label>Link articol</label><br/><input name="td_article_url" id="td_article_url" placeholder="Link articol" type="text" style="width:100%;border:1px solid #cccccc" value="'.get_permalink($_POST['post']).'"></p>
							<p><label>Link image articol( Cea mai buna rezolutie: 300 x 300 px )</label><br/><input type="text" name="td_article_thumb" " style="width:100%;border:1px solid #cccccc" placeholder="Link imagine articol" id="td_article_thumb" value="'.$url_img .'"></p>
							<p><label>Titlu articol</label><br/><input type="text" style="width:100%;border:1px solid #cccccc" placeholder="Titlu articol" name="td_article_title" id="td_article_title" value="'.$post_info->post_title.'"></p>
							<p><label>Descriere scurta articol</label><br/><textarea  style="width:100%;height:130px;border:1px solid #cccccc"  placeholder="Descriere scurta articol" name="td_article_title_content" id="td_article_title_content">'.td_get_the_excerpt($_GET['post']).'</textarea></p>
						</div>
						<p style="font-style:italic">Poti edita manual toate campurile.</p>
						</form>	
					</div>
					<script type="text/javascript">
						jQuery(document).ready(function(){


							jQuery("#td_article_thumb").change(function(){

								jQuery("#article_thumb_image").attr("src", jQuery(this).val() );

							});

							jQuery("#td_submit_article_final").click(function(){

								var error = 0;

								if( jQuery("#td_article_title_content").val().length <2 ){
									error = 1;
									jQuery("#td_article_title_content").attr("style","width:100%; border:1px solid red; height:130px");
								}
								else{
									jQuery("#td_article_title_content").attr("style","width:100%;height:130px; border:1px solid #cccccc");
								}


								if( jQuery("#td_article_thumb").val().length <20 ){
									error = 1;
									jQuery("#td_thumb_wrapper").attr("style","border:1px solid red");
								}
								else{
									jQuery("#td_thumb_wrapper").attr("style","border:0px solid red");
								}



								if( jQuery("#td_article_title").val().length < 2 ){
									error = 1;
									jQuery("#td_article_title").attr("style","width:100%; border:1px solid red");
								}
								else{
									jQuery("#td_article_title").attr("style","width:100%; border:1px solid #cccccc");
								}



								if( jQuery("#td_article_url").val().length < 2 ){
									error = 1;
									jQuery("#td_article_url").attr("style","width:100%; border:1px solid red");
								}
								else{
									jQuery("#td_article_url").attr("style","width:100%; border:1px solid #cccccc");
								}


								if( error == 1)
									return false;

								jQuery(this).hide();
							});
						});


					</script>
			';
		}

echo $html;
}


function td_custom_button(){

		$html = '';

		if(isset($_GET['post'])){

			$post_info = get_post( $_GET['post'] );
			$url_img = wp_get_attachment_url( get_post_thumbnail_id($_GET['post'], 'td_thumb') );

		}



        $html .= '<div id="major-publishing-actions" style="overflow:hidden">';
        $html .= '<div id="publishing-action">';
    	
    	$sk = get_option( 'td_secret_key');

    		if(strlen($sk) < 2 ){
				echo '<div id="major-publishing-actions" style="overflow:hidden"><div id="publishing-action"><p style="color:red">Configurati pluginul tidy.ro inainte.</p></div></div>';
				return;

			}


    	if(isset($_GET['post'])){
    		
    		global $wpdb;
		

		
			$td_article_url = end($wpdb->get_results( 'SELECT *  FROM '.$wpdb->prefix.'tidy_connections  WHERE ID_post =  '.$_GET['post'].'  LIMIT 1'));
			$td_article_url = $td_article_url->URL;

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,'http://tidy.ro/processor');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,"action=getpostinfo&secret_key=".$sk."&article_url=".$td_article_url );

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec ($ch);
			curl_close ($ch);
		
			$answer_ = json_decode($server_output);
	
			if($answer_->error == 1){

        		$html .= '<a  href="#" class="button-primary" id="submit_to_sb" >Trimite catre tidy.ro</a>';				
			
			}else{

				if($answer_->status == 'draft')
					$html .= 'Se asteapta aprobarea de catre tidy.ro';	
				elseif($answer_->status == 'publish')
        			$html .= 'Articolul a fost <span style="color:#7ad03a">Aprobat</span> de tidy.ro <br/> <a  target="_blank" href="'.$answer_->URL.'" class="button-primary" id="go_to_article" >Vezi pe tidy.ro</a>';		
				else
        			$html .= '<p>Articolul a fost <span style="color:#a00">Refuzat</span> de  tidy.ro</p>';	

			}

    	}
    	else{
        	$html .= '<a href="#" class="button" onclick="alert(\'Articolul trebuie sa fie publicat inainte de submit. \'); return false;" >Trimite catre tidy.ro</a>';
    	}

        $html .= '</div>';
        $html .= '</div>';
  
        $html .= '<script type="text/javascript">
        	jQuery(document).ready(function(){

        		jQuery("#submit_to_sb").click(function(){

        			jQuery("#submit_to_td_form").show();
        			jQuery("#td_overlay").show();


        			return false;

        		});

				jQuery("#td_form_close").click(function(){

					jQuery("#submit_to_td_form").hide();
					jQuery("#td_overlay").hide();

					return false;

				});
        	});

        </script>';		
    echo $html;
}