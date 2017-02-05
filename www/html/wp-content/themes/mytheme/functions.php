<?php
function wpwa_custom_mimes(&$mimes) {
  $mimes['png'] = 'image/png';
}
add_action('wpwa_custom_mimes', 'wpwa_custom_mimes');
