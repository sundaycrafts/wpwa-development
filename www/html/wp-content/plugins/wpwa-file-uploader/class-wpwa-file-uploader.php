<?php
/*
Plugin Name: WPWA File Uploader
Plugin URI: -
Description: Automatically convert file fields into multi file uploader.
Version: 1.0
Author: Hiroto Murai
Author URI: https://github.com/sundaycrafts
License: -
*/

class WPWA_File_Uploader {
  public function __construct () {
    add_action('admin_enqueue_scripts', array($this, 'include_script'));
    add_filter('upload_mimes', array($this, 'filter_mime_types'));
  }

  public function include_script () {
    wp_enqueue_script('jquery');

    if (function_exists('wp_enqueue_media')) {
      wp_enqueue_media();
    } else {
      wp_enqueue_style('thickbox');
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
    }

    wp_register_script('wpwa_file_upload',
      plugins_url('js/wpwa-file-uploader.js', __FILE__), array('jquery'));

    wp_enqueue_script('wpwa_file_upload');
  }

  public function filter_mime_types ($mimes) {
    $mimes = array(
      'jpg|jpeg|jpe' => 'image/jpeg'
    );
    do_action_ref_array('wpwa_custom_mimes', array(&$mimes));
    return $mimes;
  }
}
$file_uploader = new WPWA_File_Uploader();
