<?php 
/*
Plugin Name: My WPWA User Manager
Plugin URI: -
Description: User management module for the portfolio management application.
Author: Hiroto Murai
Version: 1.0.0
Author URI: https://github.com/sundaycrafts
*/
class WPWA_User_Manager {
  public function __construct () {
    // manage user roles
    register_activation_hook(__FILE__, array($this, 'add_application_user_roles'));
    register_activation_hook(__FILE__, array($this, 'remove_application_user_roles'));
    register_activation_hook(__FILE__, array($this, 'add_application_user_capabilities'));

    // manage URI rooting
    register_activation_hook(__FILE__, array($this, 'flush_application_rewrite_rules'));
    add_filter('query_vars', array($this, 'manage_user_routes_query_vars'));

    // URI base controller
    add_action('template_redirect', array($this, 'front_controller'));
    add_action('wpwa_register_user', array($this, 'register_user'));
    add_action('wpwa_activate_user', array($this, 'activate_user'));
    add_action('wpwa_login_user', array($this, 'login_user'));

    // authntication
    add_filter('authenticate', array($this, 'authenticate_user'));
  }

  public function front_controller () {
    global $wp_query;
    $control_action = isset($wp_query->query_vars['control_action']) ?
      $wp_query->query_vars['control_action'] : '';

    switch ($control_action) {
      case 'register':
        do_action('wpwa_register_user');
        break;
      case 'login':
        do_action('wpwa_login_user');
        break;
      case 'activate':
        do_action('wpwa_activate_user');
        break;
    }
  }

  public function activate_user () {
    $activation_code = isset($_GET['activation_code']) ? $_GET['activation_code'] : '';
    $message = '';

    // get user activation code recode
    $user_query = new WP_User_Query(array(
      'meta_key'   => 'activation_code',
      'meta_value' => $activation_code
    ));
    $users = $user_query->get_results();

    // refresh activation status
    if (!empty($users)) {
      $user_id = $users[0]->ID;
      update_user_meta($user_id, 'activation_status', 'active');
      $message = 'Account activated successfully.';
    } else {
      $message = 'Invalid Activation Code.';
    }

    include dirname(__FILE__).'/templates/info.php';
    exit;
  }

  public function flush_application_rewrite_rules () {
    $this->manage_user_routes();
    flush_rewrite_rules();
  }

  public function add_application_user_roles () {
    add_role('follower', 'Follower', array('read' => true));
    add_role('developer', 'Developer', array('read' => true));
    add_role('developer', 'Member', array('read' => true));
  }

  public function register_user () {
    $errors = array();
    $user_login = $user_email = $user_type = '';


    // get POST data
    $is_post = $_SERVER['REQUEST_METHOD'] === 'POST';
    if ($is_post) {
      $user_login = (isset($_POST['user']) ? $_POST['user'] : '');
      $user_email = (isset($_POST['email']) ? $_POST['email'] : '');
      $user_type  = (isset($_POST['user_type']) ? $_POST['user_type'] : '');
      $sanitized_user_login = sanitize_user($user_login);

      // validats
      if (!empty($user_email) && !is_email($user_email)) {
        array_push($errors, 'Please valid e-mail.');
      } elseif (email_exists($user_email)) {
        array_push($errors, 'User with this email already registerd.');
      }

      print_r(sanitize_user($user_login));
      if (empty($sanitized_user_login) || !validate_username($user_login)) {
        array_push($errors, 'Invalid username.');
      } elseif (username_exists($sanitized_user_login)) {
        array_push($errors, 'Username already exists.');
      }

      if (empty($user_type)) {
        array_push($errors, 'Please enter a user type.');
      }
    }

    if (empty($errors) && $is_post) {
      $user_pass = wp_generate_password();
      $user_id = wp_insert_user(array(
        'user_login' => $sanitized_user_login,
        'user_email' => $user_email,
        'role'       => $user_type,
        'user_pass'  => $user_pass
      ));

      if (!$user_id) {
        array_push($errors, 'Registration failed.');
      } else {
        $activation_code = $this->random_string();
        update_user_meta($user_id, 'activation_code', $activation_code);
        update_user_meta($user_id, 'activation_status', 'inactive');
        wp_new_user_notification($user_id, $user_pass, $activation_code);

        $success_message = "Registration completed successfully.
          Please check your email for activation link.";

      }

      if (!is_user_logged_in()) {
        echo 'ID: '.$user_id;
        echo 'Pass: '.$user_pass;
        echo 'Activation: '.$activation_code;
        include dirname(__FILE__).'/templates/login.php';
        exit;
      }
    }

    // include template
    if (!is_user_logged_in()) {
      include dirname(__FILE__).'/templates/register.php';
      exit;
    }
  }

  public function login_user () {
    if ($_SERVER['REQUEST_METHOD'] != 'POST' && is_user_logged_in()) return;
    if (is_user_logged_in()) {
      wp_redirect(home_url());
      exit;
    }

    // check input values
    $errors = array();
    $username = '';

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if(empty($username)) array_push($errors, 'Please enter a username.');
    if(empty($password)) array_push($errors, 'Please enter password.');

    if(count($errors) > 0) {
      include dirname(__FILE__).'/templates/login.php';
      exit;
    }

    // setup login info
    $credentials = array();
    $credentials['user_login']    = $username;
    $credentials['user_login']    = sanitize_user($credentials['user_login']);
    $credentials['user_password'] = $password;
    $credentials['remember']      = false;

    // login
    $user = wp_signon($credentials, false);
    if (is_wp_error($user)) {
      array_push($errors, $user->get_error_message());
    } else {
      wp_redirect(home_url());
      exit;
    }

    include dirname(__FILE__).'/templates/login.php';
    exit;
  }

  public function authenticate_user ($user, $username, $password) {
    // is admin user
    if (isset($user->data) && isset($user->data->ID) && !in_array('administrator', (array) $user->roles)) {
      // check activation status
      $active_status = '';
      $active_status = get_user_meta($user->data->ID, 'activation_status', true);

      if ('active' != $active_status) {
        $user = new WP_Error('denied', __('<strong>ERROR</strong>: Please activate your account.'));
      }
    }

    return $user;
  }

  public function random_string() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstr = '';
    for ( $i = 0; $i < 15; $i++ ) {
      $randstr .= $characters[rand(0, strlen( $characters )-1)];
    }
    return $randstr;
  }

  public function remove_application_user_roles () {
    remove_role('author');
    remove_role('editor');
    remove_role('contributor');
    remove_role('subscriber');
  }

  public function add_application_user_capabilities () {
    $role = get_role('follower');
    $role->add_cap('follow_developer_activities');
  }

  public function manage_user_routes () {
    add_rewrite_rule('^user/([^/]+)/?', 'index.php?control_action=$matches[1]', 'top');
  }

  public function manage_user_routes_query_vars ($query_vars) {
    $query_vars[] = 'control_action';
    return $query_vars;
  }
}
$user_manage = new WPWA_User_Manager();

/*
 *  Overriden version of wp_new_user_notification function
 *  for sending activation code
*/
if ( !function_exists( 'wp_new_user_notification' ) ) {
  function wp_new_user_notification($user_id, $plaintext_pass = '', $activate_code = '') {
    $user = new WP_User($user_id);

    $user_login = stripslashes($user->user_login);
    $user_email = stripslashes($user->user_email);

    $message = sprintf(__('New user registration on %s:'), get_option('blogname')) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
    $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

    @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

    if (empty($plaintext_pass)) return;

    $activate_link = site_url() . "/user/activate/?activation_code=$activate_code";

    $message = __('Hi there,') . "\r\n\r\n";
    $message .= sprintf(__('Welcome to %s! Please activate your account using the link:'), get_option('blogname')) . "\r\n\r\n";
    $message .= sprintf(__('<a href="%s">%s</a>'), $activate_link, $activate_link) . "\r\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n";
    $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n\r\n";

    wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message);
  }
}
