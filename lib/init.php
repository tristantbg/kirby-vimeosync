<?php

if (c::get('kirby.cloudinary')) {

  \Cloudinary::config(array(
    'cloud_name' => c::get('kirby.cloudinary.cloud_name'),
    'api_key' => c::get('kirby.cloudinary.api_key'),
    'api_secret' => c::get('kirby.cloudinary.api_secret')
  ));

  require_once __DIR__ . DS . 'methods.php';

} else {

  require_once __DIR__ . DS . 'fallback.php';

}
