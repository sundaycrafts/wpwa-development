<?php get_header() ?>
<div id="custom_panel">
  <?php
    $user_login = isset($user_login) ? $user_login : '';
    $user_email = isset($user_email) ? $user_email : '';
    $user_type = isset($user_type) ? $user_type : '';

    function is_selected ($value, $user_type) {
      if ($value === $user_type) {
        echo 'selected';
      }
    }

    if (count($errors) > 0) {
      foreach ($errors as $error) {
        echo '<p class="frm_error">'.$error.'</p>';
      }
    }
  ?>
  <!--HTML code for form-->
  <form method="POST" id="registration-form" action="<?php echo get_site_url().'/user/register' ?>">
    <ul>
      <li>
        <label for="Username" class="frm_label">Username</label>
        <input type="text" class="frm_field" id="username" name="user" value="<?php echo $user_login ?>">
      </li>
      <li>
        <label for="Email" class="frm_label">E-mail</label>
        <input type="text" class="frm_field" id="email" name="email" value="<?php echo $user_email ?>">
      </li>
      <li>
        <label for="User Type" class="frm_label">User Type</label>
        <select name="user_type" class="frm_field">
          <option value="" <?php is_selected('', $user_type) ?>>Select type</option>
          <option value="follower" <?php is_selected('follower', $user_type) ?>>Follower</option>
          <option value="developer" <?php is_selected('developer', $user_type) ?>>developer</option>
          <option value="member" <?php is_selected('member', $user_type) ?>>Member</option>
        </select>
      </li>
      <li>
        <label for="" class="frm_label"></label>
        <input type="submit" value="Register">
      </li>
    </ul>
  </form>
</div>
<?php get_footer() ?>
