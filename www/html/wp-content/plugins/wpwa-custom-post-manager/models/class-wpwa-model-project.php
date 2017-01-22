<?php
class WPWA_Model_Project {
  private $post_type;
  private $template_parser;

  private $technology_taxonomy;
  private $project_type_taxonomy;

  public function __construct () {
    $this->post_type = 'wpwa_project';
    $this->technology_taxonomy = 'wpwa_technology';
    $this->project_type_taxonomy = 'wpwa_project_type';

    add_action('init', array($this, 'create_projects_post_type'));
    add_action('init', array($this, 'create_projects_custom_taxonomies'));

    add_action('add_meta_boxes', array($this, 'add_projects_meta_boxes'));
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

  public function create_projects_custom_taxonomies () {
    register_taxonomy(
      $this->technology_taxonomy,
      $this->post_type,
      array(
        'labels' => array(
          'name'              => __('Technology', 'wpwa'),
          'singular_name'     => __('Technology', 'wpwa'),
          'search_items'      => __('Search Technology', 'wpwa'),
          'all_items'         => __('All Technology', 'wpwa'),
          'parent_item'       => __('Parent Technology', 'wpwa'),
          'parent_item_colon' => __('Parent Technology:', 'wpwa'),
          'edit_item'         => __('Edit Technology', 'wpwa'),
          'update_item'       => __('Update Technology', 'wpwa'),
          'add_new_item'      => __('Add New Technology', 'wpwa'),
          'new_item_name'     => __('New Technology Name', 'wpwa'),
          'menu_name'         => __('Technology', 'wpwa')
        ),
        'hierarchical' => true
      )
    );

    register_taxonomy(
      $this->project_type_taxonomy,
      $this->post_type,
      array(
        'labels' => array(
          'name'              => __('Project Type', 'wpwa'),
          'singular_name'     => __('Project Type', 'wpwa'),
          'search_items'      => __('Search Project Type', 'wpwa'),
          'all_items'         => __('All Project Type', 'wpwa'),
          'parent_item'       => __('Parent Project Type', 'wpwa'),
          'parent_item_colon' => __('Parent Project Type:', 'wpwa'),
          'edit_item'         => __('Edit Project Type', 'wpwa'),
          'update_item'       => __('Update Project Type', 'wpwa'),
          'add_new_item'      => __('Add New Project Type', 'wpwa'),
          'new_item_name'     => __('New Project Type Name', 'wpwa'),
          'menu_name'         => __('Project Type', 'wpwa')
        ),
        'hierarchical' => true,
        'capabilities' => array(
          'manage_terms' => 'manage_project_type',
          'edit_terms'   => 'edit_project_type',
          'delete_terms' => 'delete_project_type',
          'assign_terms' => 'assign_project_type'
        )
      )
    );
  }

  public function add_projects_meta_boxes () {
    add_meta_box('wpwa-projects-meta', 'Projects Details',
      array($this, 'display_projects_meta_boxes'), $this->post_type);
  }

  public function display_projects_meta_boxes () {
    global $post;

    $html =
<<< EOF
  <table class="form-table">
    <tr>
      <th><label for="Project URL">Project URL</label></th>
      <td><input type="text" name="txt_url" id="txt_url" value="" class="widefat"></td>
    </tr>
    <tr>
      <th><label for="Project Duration">Project Duration</label></th>
      <td><input type="text" class="widefat" id="txt_duration" value=""></td>
    </tr>
  </table>
EOF;

    echo $html;
  }
}
