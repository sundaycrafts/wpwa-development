<?php
/*
 Plugin Name: WPWA Custom Posts Manager
 Plugin URI: -
 Description: Core data management using Custom Types for the portfolio
 management application.
 Author: Hiroto Murai
 Version: 1.0
 Author URI: http://www.github.com/sundaycrafts
*/

spl_autoload_register('wpwa_autoloader');
require __DIR__ . '/vendor/autoload.php';

function wpwa_autoloader($class_name) {
  $class_components = explode('_', $class_name);
  if (isset($class_components[0]) && $class_components[0] === 'WPWA'
    && isset($class_components[1])) {

    $class_directory = $class_components[1];
    unset($class_components[0], $class_components[1]);

    $file_name = implode('_', $class_components);
    $base_path = plugin_dir_path(__FILE__);
    switch($class_directory) {
      case 'Model':
        $file_path = $base_path.'models/class-wpwa-model-'.lcfirst($file_name).'.php';
        if (file_exists($file_path) && is_readable($file_path)) {
          include $file_path;
        }
        break;
    }
  }
}

class WPWA_Custom_Post_Manager {
  private $base_path; // manage the path from root dirctory to plugin folder
  private $template_parser; // the template system instance it use
  private $projects; // manage project custom post type object to use any function

  public function __construct () {
    $this->base_path = plugin_dir_path(__FILE__);
    require_once $this->base_path . 'class-twig-initializer.php';
    $this->template_parser = Twig_Initializer::initialize_templates();
    $this->projects = new WPWA_Model_Project($this->template_parser);
  }
}
$custom_post_manager = new WPWA_Custom_Post_Manager();
