<?php
class WPWA_Model_Project {
  private $post_type;
  private $template_parser;

  public function __construct () {
    $this->post_type = 'wpwa_project';
    add_action('init', array($this, 'create_projects_post_type'));
  }

  function create_projects_post_type () {
    $labels = array(
      'name'               => __('Projects' , 'wpwa'),
      'singular_name'      => __('Project' , 'wpwa'),
      'add_new'            => __('Add New' , 'wpwa'),
      'add_new_item'       => __('Add New Project' , 'wpwa'),
      'edit_item'          => __('Edit Project' , 'wpwa'),
      'new_item'           => __('New Project' , 'wpwa'),
      'all_items'          => __('All Projects' , 'wpwa'),
      'view_item'          => __('View Project' , 'wpwa'),
      'search_items'       => __('Search Projects' , 'wpwa'),
      'not_found'          => __('No projects found' , 'wpwa'),
      'not_found_in_trash' => __('No projects found in the Trash' , 'wpwa'),
      'parent_item_colon'  => '',
      'menu_name'          => __('Projects' , 'wpwa')
    );
    $args = array(
      'labels'              => $labels,
      'hierarchical'        => true,
      'description'         => 'Projects',
      'supports'            => array('title', 'editor'),
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_nav_menus'   => true,
      'publicly_queryable'  => true,
      'exclude_from_search' => false,
      'has_archive'         => true,
      'query_var'           => true,
      'can_export'          => true,
      'rewrite'             => true,
      'capability_type'     => 'post'
    );
    register_post_type($this->post_type, $args);
  }
}
