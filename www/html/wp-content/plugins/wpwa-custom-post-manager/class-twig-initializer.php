<?php
class Twig_Initializer {
  public static function initialize_templates () {
    $base_path = plugin_dir_path(__FILE__);
    $loader = new Twig_Loader_Filesystem($base_path.'templates');
    $twig = new Twig_Environment($loader, array(
      'cache' => $base_path.'twig_cache',
    ));
    return $twig;
  }
}
