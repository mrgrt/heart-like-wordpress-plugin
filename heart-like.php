<?php
/**
 * Plugin Name: Heart Likes
 * Plugin URI: https://github.com/mrgrt/heart-like-wordpress-plugin
 * Description: Show <3 icon similar to twitter like on blog posts. 
 * Version: 1.0
 * Tested up to: 5.7
 * Author: Grahame Thomson
 * Author URI: https://github.com/mrgrt
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */



function gt_hl_enqueue_scripts_styles() {
    //Load js
    $file = '/script.js';
	wp_register_script( 'ipm-script', plugin_dir_url(__FILE__) . $file, array('jquery'), filemtime( plugin_dir_path(__FILE__) . $file ));
    wp_localize_script( 'ipm-script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
    wp_enqueue_script( 'ipm-script' );
    // Load our CSS file
    $file = '/style.css';
    wp_enqueue_style( 'ipm-style', plugin_dir_url(__FILE__) . $file, array(), filemtime( plugin_dir_path(__FILE__) . $file ) );
  }


add_action( 'wp_enqueue_scripts', 'gt_hl_enqueue_scripts_styles' );


 // Add heart to after title. 
add_filter('the_title', 'gt_hl_add_heart_to_title', 10, 2);
function gt_hl_add_heart_to_title($title, $id) {
	global $wp_query;
	$title = $title;
	if(in_the_loop()){
		$title .= gt_hl_get_heart_html($id);
	}
    return $title;
}


// Add heart class to post classes
add_filter('post_class','gt_hl_class_container', 10, 3);
function gt_hl_class_container($classes, $class, $id){
	array_push($classes, 'heart-container-class');
	return $classes;
}


function gt_hl_get_heart_html($post_id){

    $heart_like_count = get_post_meta($post_id, 'heart_like_count', true);
    
    if(!$heart_like_count){
        $heart_like_count = 0;
    }
    
    $heart_classes = 'heart';
    $heart_likes = gt_hl_get_user_heart_likes();
    
    if(gt_hl_get_post_user_heart_like($heart_likes, $post_id)){
        $heart_classes .= ' active';
    }
	
    $heart = '<div class="gt_heart_like" data-post-id="'.$post_id.'">
                <div class="' . $heart_classes . '">
			        <div class="heart_inner"></div>
		        </div>
		        <div class="count">' . $heart_like_count .'</div>
		    </div>';


    return $heart;

}


function gt_hl_update_heart_like($heart_likes, $post_id, $operator){

    if(!is_array($heart_likes)){
        $heart_likes = array();
    } 

    //Update the count
    $current_count = get_post_meta($post_id, 'heart_like_count', true);
    if($operator=="+"){
        $new_count     = $current_count + 1;
    } else{
        $new_count     = $current_count - 1;
    }

    $heart_likes[$post_id] = $new_count;

    update_post_meta($post_id, 'heart_like_count', $new_count);

    
    return $heart_likes;

}



function gt_hl_get_user_heart_likes(){

    $heart_likes = json_decode(stripslashes($_COOKIE['heartlikes']), true);

    if(!is_array($heart_likes)){
        $heart_likes = array();
    } 

    return $heart_likes;

}


//Determine if the user has liked the post
function gt_hl_get_post_user_heart_like($heart_likes, $post_id){
   
    $post_user_heart_like = false;

    if(!is_array($heart_likes)){
        $heart_likes = array();
    } 

    if(array_key_exists($post_id, $heart_likes)){
        $post_user_heart_like = true;
    }

    return $post_user_heart_like;

}

add_action("wp_ajax_gt_hl_heart_like", "gt_hl_heart_like");
add_action("wp_ajax_nopriv_gt_hl_heart_like", "gt_hl_heart_like");

function gt_hl_heart_like() {

   $post_id       = $_POST['postId'];
   $sign          = $_POST['sign'];

   $heart_likes   = gt_hl_get_user_heart_likes();

   if($sign=='positive'){
        $new_likes = gt_hl_update_heart_like($heart_likes, $post_id, "+");
        setcookie("heartlikes", json_encode($new_likes), time()+3600, '/');
   } else{
        $new_likes = gt_hl_update_heart_like($heart_likes, $post_id, "-");
        $updated_likes = $new_likes;
        unset($updated_likes[$post_id]);
        setcookie("heartlikes", json_encode($updated_likes), time()+3600, '/');
   }

   wp_send_json_success(array("likes" => $new_likes[$post_id]));

   die();

}