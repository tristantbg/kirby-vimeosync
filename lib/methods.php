<?php

file::$methods['cloudinary_tag'] = function($file, $options = array('')) {

  if($file->cloudinaryid()->empty()) k_cloudinary_upload($file);

  $file_tag = '';

  switch ($file->type()) {
    case 'image':
      $main_options = array(
      	'fetch_format' => 'auto',
        'quality' => 'auto',
        'format' => 'jpg',
        'crop' => 'limit',
      );
      $options = array_merge($main_options, $options);
      $file_tag = cl_image_tag($file->cloudinaryid()->value(), $options);

      break;
    case 'video':
      $main_options = array(
      	'html_width' => '100%',
      	'source_types' => ['mp4'],
      	'data-stream' => cloudinary_url($file->cloudinaryid()->value().'.m3u8', ['streaming_profile' => 'full_hd', 'resource_type' => 'video']),
      	'preload' => 'auto',
      	'class' => 'video-player',
      	);
      $options = array_merge($main_options, $options);
      $file_tag = cl_video_tag($file->cloudinaryid()->value(), $options);
      break;
    default:
      # code...
      break;
  }
  return $file_tag;
};

file::$methods['cloudinary_url'] = function($file, $options = array('')) {

  if($file->cloudinaryid()->empty()) k_cloudinary_upload($file);

  $file_path = '';

  switch ($file->type()) {
    case 'image':
      $main_options = array(
      	'fetch_format' => 'auto',
        'quality' => 'auto',
        'format' => 'jpg',
        'crop' => 'limit',
      );
      $options = array_merge($main_options, $options);
      $file_path = cloudinary_url($file->cloudinaryid()->value(), $options);
    break;

    default:
    break;
  }
  return $file_path;
};


kirby()->hook('panel.page.move', function($page, $oldPage = null) {
  if($oldPage->uri() != $page->uri()) {
  	foreach ($page->index()->files() as $key => $file) {
    	k_cloudinary_update($file);
  	}
  }
});

kirby()->hook('panel.file.upload', function($file) {
  k_cloudinary_upload($file);
});

kirby()->hook('panel.file.delete', function($file) {
  k_cloudinary_destroy($file);
});

kirby()->hook('panel.file.replace', function($file, $oldFile) {
  k_cloudinary_destroy($oldFile);
  k_cloudinary_upload($file);
});
