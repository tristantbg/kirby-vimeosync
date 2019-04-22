<?php

file::$methods['vimeo_tag'] = function($file, $options = array('')) {

  if($file->vimeoURI()->empty()) k_vimeo_upload($file);

  if(!$file->vimeoURI()->empty() && $file->vimeoFiles()->empty() && k_vimeo_status($file)) k_vimeo_write_infos($file, $file->vimeoURI());

  $poster = $file->vimeoThumbnails()->isNotEmpty() ? $file->vimeoThumbnails()->toStructure()->last()->link() : '';
  $poster = !empty($options['poster']) ? $options['poster'] : '';
  $video = null;

  $videoContainerClass = 'player-container';
  $videoClass = 'video-player';
  if(!empty($options['class'])) $videoClass .= ' '.$options['class'];
  if(!empty($options['controls']) && $options['controls']) $videoClass .= ' controls';

  $videoContainer = brick('div')->attr('class', $videoContainerClass);
  $video = brick('video')
        ->attr('class', $videoClass)
        ->attr('poster', $poster)
        ->attr('width', '100%')
        ->attr('height', 'auto')
        ->attr('preload', 'auto');

  if(!empty($options['controls']) && $options['controls']) $video->attr('controls', true);
  if(!empty($options['loop']) && $options['loop']) $video->attr('loop', 'loop');
  if(!empty($options['muted']) && $options['muted']) $video->attr('muted', 'muted');
  if(!empty($options['playsinline']) && $options['playsinline']) $video->attr('playsinline', 'true');

  if ($file->vimeoFiles()->isNotEmpty()) {
    if($hls = $file->vimeoHls()->first()) $video->attr('data-stream', $hls->link());
    if($file->vimeoHD()->last()) {
      $hd = $file->vimeoHD()->last()->link();
      $video->attr('data-hd', $hd);
      $video->append('<source src=' . $hd . ' type="video/mp4">');
    }
    if($file->vimeoSD()->last()) {
      $sd = $file->vimeoSD()->last()->link();
      $video->attr('data-sd', $file->vimeoSD()->last()->link());
      if(!isset($hd)) $video->append('<source src=' . $sd . ' type="video/mp4">');
    }
  }
  else {
    $hd = $file->url();
    $video->attr('data-hd', $hd);
    $video->append('<source src=' . $hd . ' type="video/mp4">');
  }

  $videoContainer->append($video);

  return $videoContainer;


};

file::$methods['vimeoSD'] = function($file) {
  return $file->vimeofiles()->toStructure()->filterBy('quality', 'sd');
};

file::$methods['vimeoHD'] = function($file) {
  return $file->vimeofiles()->toStructure()->filterBy('quality', 'hd');
};

file::$methods['vimeoHls'] = function($file) {
  return $file->vimeofiles()->toStructure()->filterBy('quality', 'hls');
};

kirby()->hook('panel.file.upload', function($file) {
  k_vimeo_upload($file);
});

kirby()->hook('panel.file.delete', function($file) {
  k_vimeo_destroy($file);
});

kirby()->hook('panel.file.replace', function($file, $oldFile) {
  k_vimeo_replace($oldFile, $file);
});
