<?php

/*
  Plugin Name: Very Simple Slider
  Plugin URI: http://wordpress.org/
  Description: Simple Slider Ever
  Version: 1.0
  Author: Asad Ali
  Author URI: https://github.com/asadalimca
  Requires at least: 5.6
  Tested up to: 5.7.2
  License: GPLv2
 */

function vss_slider_activation() {

}

register_activation_hook(__FILE__, 'vss_slider_activation');

function vss_slider_deactivation() {

}

register_deactivation_hook(__FILE__, 'vss_slider_deactivation');




add_action('wp_enqueue_scripts', 'vss_scripts');

function vss_scripts() {
    global $post;

    wp_enqueue_script('jquery');

    wp_register_script('slidesjs_core', plugins_url('js/jquery.slides.min.js', __FILE__), array("jquery"));
    wp_enqueue_script('slidesjs_core');


    wp_register_script('slidesjs_init', plugins_url('js/slidesjs.initialize.js', __FILE__));
    wp_enqueue_script('slidesjs_init');

    $effect      = (get_option('vss_effect') == '') ? "slide" : get_option('vss_effect');
    $interval    = (get_option('vss_interval') == '') ? 2000 : get_option('vss_interval');
    $autoplay    = (get_option('vss_autoplay') == 'enabled') ? true : false;
    $playBtn    = (get_option('vss_playbtn') == 'enabled') ? true : false;
        $config_array = array(
            'effect' => $effect,
            'interval' => $interval,
            'autoplay' => $autoplay,
            'playBtn' => $playBtn
        );

    wp_localize_script('slidesjs_init', 'setting', $config_array);

}

add_action('wp_enqueue_scripts', 'vss_styles');

function vss_styles() {

    wp_register_style('slidesjs_example', plugins_url('/css/slider_style.css', __FILE__));
    wp_enqueue_style('slidesjs_example');
    wp_register_style('slidesjs_fonts', plugins_url('/css/font-awesome.min.css', __FILE__));
    wp_enqueue_style('slidesjs_fonts');
}

add_shortcode("very_simple_slider", "vss_display_slider");

function vss_display_slider($attr, $content) {

    extract(shortcode_atts(array(
                'id' => ''
                    ), $attr));

    $gallery_images = get_post_meta($id, "_vss_gallery_images", true);
    $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();
	//print_r($gallery_images); exit;
    $plugins_url = plugins_url();
    $html = '<div class="slider_container">
    <div id="slides">';

    foreach ($gallery_images as $gal_img) {
        if ($gal_img != "") {
            $html .= "<img src='" . esc_url($gal_img) . "' />";
        }
    }

    $html .= '<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="icon-chevron-left icon-large"></i></a>
      <a href="#" class="slidesjs-next slidesjs-navigation"><i class="icon-chevron-right icon-large"></i></a>
    </div>
  </div>';

   echo apply_filters( 'vss_display_slider', $html );
}

add_action('init', 'vss_register_slider');

function vss_register_slider() {
    $labels = array(
				'menu_name' => 'Very Simple Slider',
                'name' => 'All Simple Sliders',
                'singular_name' => 'Very Simple Slider',
				'all_items' => 'All Sliders',
                'add_new' => 'Add New Slider',
                'add_new_item' => 'Add New Slider',
                'edit' => 'Edit',
                'edit_item' => 'Edit Slider',
                'new_item' => 'New Slider',
                'view' => 'View',
                'view_item' => 'View Slider',
                'search_items' => 'Search Slider',
                'not_found' => 'No Slider found',
                'not_found_in_trash' => 'No Slider found in Trash'
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Slideshows',
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type('vvs_slider', $args);
}

/* Define shortcode column in Rhino Slider List View */
add_filter('manage_edit-vvs_slider_columns', 'vss_set_custom_edit_vvs_slider_columns');
add_action('manage_vvs_slider_posts_custom_column', 'vss_custom_vvs_slider_column', 10, 2);

function vss_set_custom_edit_vvs_slider_columns($columns) {
    return $columns
    + array('slider_shortcode' => __('Shortcode'));
}

function vss_custom_vvs_slider_column($column, $post_id) {

    $slider_meta = get_post_meta($post_id, "_vss_slider_meta", true);
    $slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();

    switch ($column) {
        case 'slider_shortcode':
            echo apply_filters('vss_custom_wps_slider_column',"[very_simple_slider id='$post_id' /]");
            break;
    }
}

add_action('add_meta_boxes', 'vss_slider_meta_box');

function vss_slider_meta_box() {

    add_meta_box("fwds-slider-images", "Slider Images", 'vss_view_slider_images_box', "vvs_slider", "normal");
}

function vss_view_slider_images_box() {
    global $post;

    $gallery_images = get_post_meta($post->ID, "_vss_gallery_images", true);
    $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();
	$gallery_images = apply_filters('images_input_url',$gallery_images);
	//print_r($gallery_images);exit;

    // Use nonce for verification
    $html = '<input type="hidden" name="vss_slider_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    $html .= '<table class="form-table">';

    $html .= "
          <tr>
            <th style=''><label for='Upload Images'>Image 1</label></th>
            <td><input name='gallery_img[]' id='vss_slider_upload' type='text' value='" . sanitize_text_field( $gallery_images[0] ) . "'  /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 2</label></th>
            <td><input name='gallery_img[]' id='vss_slider_upload' type='text' value='" . sanitize_text_field( $gallery_images[1] )  . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 3</label></th>
            <td><input name='gallery_img[]' id='vss_slider_upload' type='text'  value='" . sanitize_text_field( $gallery_images[2] )  . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 4</label></th>
            <td><input name='gallery_img[]' id='vss_slider_upload' type='text' value='" . sanitize_text_field( $gallery_images[3] )  . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 5</label></th>
            <td><input name='gallery_img[]' id='vss_slider_upload' type='text' value='" . sanitize_text_field( $gallery_images[4] )  . "' /></td>
          </tr>          

        </table>";

     // Apply any filters to the final output
    echo apply_filters('final_output', $html);
}

/* Save Slider Options to database */
add_action('save_post', 'vss_save_slider_info');

function vss_save_slider_info($post_id) {


    // verify nonce
    if (!wp_verify_nonce($_POST['vss_slider_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('vvs_slider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

        /* Save Slider Images */
        //echo "<pre>";print_r($_POST['gallery_img']);exit;
        $gallery_images = (isset($_POST['gallery_img']) ? $_POST['gallery_img'] : '');
        $gallery_images = strip_tags(json_encode($gallery_images));
		$gallery_images = sanitize_text_field( $gallery_images);
        update_post_meta($post_id, "_vss_gallery_images", $gallery_images);

       
    } else {
        return $post_id;
    }
}

add_action('admin_menu', 'vss_plugin_settings');

function vss_plugin_settings() {
    //new top-level menu
	add_submenu_page(
                     'edit.php?post_type=vvs_slider', //$parent_slug
                     'VSS Slider Settings',  //$page_title
                     'VSS Slider Settings',        //$menu_title
                     'administrator',           //$capability
                     'vss_settings',//$menu_slug
                     'vss_display_settings'//$function
     );
    
}

function vss_display_settings() {

    $slide_effect = (get_option('vss_effect') == 'slide') ? 'selected' : '';
    $fade_effect = (get_option('vss_effect') == 'fade') ? 'selected' : '';
    $interval = (get_option('vss_interval') != '') ? get_option('vss_interval') : '2000';
    $autoplay  = (get_option('vss_autoplay') == 'enabled') ? 'checked' : '' ;
    $playBtn  = (get_option('vss_playBtn') == 'enabled') ? 'checked' : '' ;

    $html = '<div class="wrap">

            <form method="post" name="options" action="options.php">

            <h2>Select Your Settings</h2>' . wp_nonce_field('update-options') . '
            <table width="100%" cellpadding="10" class="form-table">
                <tr>
                    <td align="left" scope="row">
                    <label>Slider Effect</label><select name="vss_effect" >
                      <option value="slide" ' . $slide_effect . '>Slide</option>
                      <option value="fade" '.$fade_effect.'>Fade</option>
                    </select>
             

                    </td> 
                </tr>
                <tr>
                    <td align="left" scope="row">
                    <label>Enable Auto Play</label><input type="checkbox" '.$autoplay.' name="vss_autoplay" 
                    value="enabled" />

                    </td> 
                </tr>
                <tr>
                    <td align="left" scope="row">
                    <label>Enable Play Button</label><input type="checkbox" '.$playBtn.' name="vss_playBtn" 
                    value="enabled" />

                    </td> 
                </tr>
                <tr>
                    <td align="left" scope="row">
                    <label>Transition Interval</label><input type="text" name="vss_interval" 
                    value="' . $interval . '" />

                    </td> 
                </tr>
            </table>
            <p class="submit">
                <input type="hidden" name="action" value="update" />  
                <input type="hidden" name="page_options" value="vss_autoplay,vss_effect,vss_interval,vss_playBtn" /> 
                <input type="submit" name="Submit" value="Update" />
            </p>
            </form>

        </div>';
   // Apply any filters to the final output
    echo apply_filters('vss_display_settings', $html);
}
?>