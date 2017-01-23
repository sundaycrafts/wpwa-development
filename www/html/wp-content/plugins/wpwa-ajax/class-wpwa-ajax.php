<?php
/*
Plugin Name: WPWA AJAX
Plugin URI: -
Description: Common library for making ajax requests
Version: 1.0
Author: Hiroto Murai
Author URI: https://github.com/sundaycrafts
License: -
*/
class WPWA_AJAX {
  private $ajax_actions;

  public function __construct () {
    $this->configure_actions();
    add_action('wp_enqueue_scripts', array($this, 'include_scripts'));
  }

  public function configure_actions () {
    $this->ajax_actions = array(
      'sample_key' => array(
        'action'   => 'sample_action',
        'function' => 'sample_function_name',
        'logged'   => true
      ),
      'sample_key1' => array(
        'action'   => 'sample_action1',
        'function' => 'sample_function_name1',
      ),
    );

    foreach($this->ajax_actions as $custom_key => $custom_action) {
      if (isset($custom_action['logged']) && $custom_action['logged']) {
        add_action('wp_ajax_'.$custom_action['action'], array($this, $custom_action['function']));

      } else if (isset($custom_action['logged']) && !$custom_action['logged']) {
        add_action('wp_ajax_nopriv_'.$custom_action['action'], array($this, $custom_action['function']));

      } else {
        add_action('wp_ajax_nopriv_'.$custom_action['action'], array($this, $custom_action['function']));
        add_action('wp_ajax_'.$custom_action['action'], array($this, $custom_action['function']));

      }
    }
  }

  function sample_function_name () {
    $nonce = $_POST['nonce'];
    if (!wp_verify_nonce($nonce, 'unique_key')) die('Unauthorized request!');

    echo json_encode($_POST);
    exit;
  }

  public function include_scripts () {
    global $post;

    wp_enqueue_script('jquery');
    wp_register_script('wpwa_ajax', plugins_url('js/wpwa-ajax.js', __FILE__), array('jquery'));
    wp_enqueue_script('wpwa_ajax');

    $nonce = wp_create_nonce('unique_key');

    $config_array = array(
      'ajaxURL'     => admin_url('admin-ajax.php'),
      'ajaxActions' => $this->ajax_actions,
      'ajaxNonce'   => $nonce
    );
    wp_localize_script('wpwa_ajax', 'wpwa_conf', $config_array);
  }
}
$ajx = new WPWA_AJAX();

