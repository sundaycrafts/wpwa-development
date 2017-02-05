<?php
/*
Plugin Name: WPWA Admin Dashboard
Plugin URI: -
Description: Customize admin dashboard to suit web applications.
Version: 1.0
Author: Hiroto Murai
Author URI: github.com/sundaycrafts
License: -
*/
class WPWA_Dashboard {
  public function __construct () {
    add_action('wp_before_admin_bar_render', array($this, 'customize_admin_toolbar'));
  }

  public function set_frontend_toolbar ($status) {
    show_admin_bar($status);
  }

  public function customize_admin_toolbar () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('updates');
    $wp_admin_bar->remove_menu('comments');
    $wp_admin_bar->remove_menu('new-content');

    if (current_user_can('edit_posts')) {
      $wp_admin_bar->add_menu(array(
        'id'    => 'wpwa-developers',
        'title' => 'Developer Components',
        'href'  => admin_url()
      ));
      $wp_admin_bar->add_menu(array(
        'id'     => 'wpwa-new-books',
        'title'  => 'Books',
        'href'   => admin_url().'post-new.php?post_type=wpwa_book',
        'parent' => 'wpwa-developers'
      ));
      $wp_admin_bar->add_menu(array(
        'id'     => 'wpwa-new-projects',
        'title'  => 'Projects',
        'href'   => admin_url().'post-new.php?post_type=wpwa_project',
        'parent' => 'wpwa-developers'
      ));
    }
  }
}
$admin_dashboard = new WPWA_Dashboard();
$admin_dashboard->set_frontend_toolbar(FALSE);
