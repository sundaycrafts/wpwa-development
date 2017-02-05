<?php
class WPWA_Model_Project {
  private $post_type;
  private $template_parser;

  private $technology_taxonomy;
  private $project_type_taxonomy;
  private $error_message;

  public function __construct ($template_parser) {
    $this->post_type             = 'wpwa_project';
    $this->technology_taxonomy   = 'wpwa_technology';
    $this->project_type_taxonomy = 'wpwa_project_type';

    $this->error_message = '';

    $this->template_parser = $template_parser;

    add_action('init', array($this, 'create_projects_post_type'));
    add_action('init', array($this, 'create_projects_custom_taxonomies'));

    add_action('add_meta_boxes', array($this, 'add_projects_meta_boxes'));

    add_action('save_post', array($this, 'save_project_meta_data'));
    add_filter( 'post_updated_messages', array( $this, 'generate_project_messages' ) );
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

    $data = array();
    $data['project_meta_nonce']   = wp_create_nonce('wpwa-project-meta');
    $data['project_url']          = esc_url(get_post_meta( $post->ID, "_wpwa_project_url", true ));
    $data['project_duration']     = esc_attr(get_post_meta( $post->ID, "_wpwa_project_duration", true ));
    $data['project_download_url'] = esc_attr(get_post_meta( $post->ID, "_wpwa_project_download_url", true ));
    $data['project_status']       = esc_attr(get_post_meta( $post->ID, "_wpwa_project_status", true ));
    $data['project_screens']      = json_decode(get_post_meta($post->ID, '_wpwa_project_screens', true));

    echo $this->template_parser->render('project_meta.html', $data);
  }

  public function save_project_meta_data ($post_id) {
    global $post;
    if (!$post_id || !isset($_POST['project_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['project_meta_nonce'], 'wpwa-project-meta')) return $post->ID;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post->ID;

    if ($this->post_type == $_POST['post_type'] && current_user_can('edit_post', $post->ID)) {
      // sanitizing and casting
      $project_url = isset($_POST['txt_url']) ? (string) esc_url(trim($_POST['txt_url'])) : '';
      $project_duration = isset($_POST['txt_duration']) ? (float) esc_attr(trim($_POST['txt_duration'])) : '';
      $project_download_url = isset($_POST['txt_download_url']) ? (string) esc_attr(trim($_POST['txt_download_url'])) : '';
      $project_status = isset($_POST['sel_project_status']) ? (string) esc_attr(trim($_POST['sel_project_status'])) : '';

      // validate
      if (empty($_POST['post_title'])) {
        $this->error_message .= __('Project name cannot be empty. <br>', 'wpwa');
      }
      if ('0' == $project_status) {
        $this->error_message .= __('Project status cannot be empty. <br>', 'wpwa');
      }
      if ( empty($project_duration)) {
        $this->error_message .= __('Project duration cannot be empty. <br/>', 'wpwa' );
      }

      // error handling
      if (!empty($this->error_message)) {
        remove_action('save_post', array($this, 'save_project_meta_data'));

        $post->post_status = 'draft';
        wp_update_post($post);

        add_action('save_post', array($this, 'save_project_meta_data'));
        $this->error_message = __('Project cration faild. <br>').$this->error_message;
        set_transient('project_error_message_'.$post->ID, $this->error_message, 60*10);
      } else {
        update_post_meta($post->ID, '_wpwa_project_url', $project_url);
        update_post_meta($post->ID, '_wpwa_project_duration', $project_duration);
        update_post_meta($post->ID, '_wpwa_project_download_url', $project_download_url);
        update_post_meta($post->ID, '_wpwa_project_status', $project_status);

        $project_screens = isset($_POST['h_project_screens']) ? $_POST['h_project_screens'] : '';
        $porject_screens = json_encode($project_screens);
        update_post_meta($post->ID, '_wpwa_project_screens', $porject_screens);
      }
    } else {
      return $post->ID;
    }
  }

  public function generate_project_messages( $messages ) {
    global $post, $post_ID;

    $this->error_message = get_transient( "project_error_message_$post->ID" );
    $message_no = isset($_GET['message']) ? (int) $_GET['message'] : '0';
    delete_transient( "project_error_message_$post->ID" );

    if ( !empty( $this->error_message ) ) {
      $messages[$this->post_type] = array( "$message_no" => $this->error_message );
    } else {
      $messages[$this->post_type] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf(__('Project updated. <a href="%s">View Project</a>', 'wpwa' ), esc_url(get_permalink($post_ID))),
        2 => __('Custom field updated.', 'wpwa' ),
        3 => __('Custom field deleted.', 'wpwa' ),
        4 => __('Project updated.', 'wpwa' ),
        5 => isset($_GET['revision']) ? sprintf(__('Project restored to revision from %s', 'wpwa' ), wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6 => sprintf(__('Project published. <a href="%s">View Project</a>', 'wpwa' ), esc_url(get_permalink($post_ID))),
        7 => __('Project saved.', 'wpwa' ),
        8 => sprintf(__('Project submitted. <a target="_blank" href="%s">Preview Project</a>', 'wpwa' ), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        9 => sprintf(__('Project scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Project</a>', 'wpwa' ),
        date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
        10 => sprintf(__('Project draft updated. <a target="_blank" href="%s">Preview Project</a>', 'wpwa' ), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
      );
    }

    return $messages;
  }
}
