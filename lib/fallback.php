<?php

file::$methods['cloudinary_url'] = function($file, $options = array('')) {
  $file_url = '';

  switch ($file->type()) {
    case 'image':
      $main_options = array(
        'fetch_format'=>'auto',
        'type'=>'fetch',
        'quality' => 'auto',
        'format' => 'auto',
        'crop' => 'limit',
      );
      $options = array_merge($options, $main_options);
      $file_url = $file->width(3000)->url();
      break;

    default:
      # code...
      break;
  }
  return $file_url;
};
