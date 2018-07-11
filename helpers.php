<?php

$maxWidth = 5000;

function k_cloudinary_upload($file) {
	$public_id = str_replace('.'.$file->extension(), '', $file->uri());
	switch ($file->type()) {
	case 'video':
		if($file->size() < 104857600) {
			$response = \Cloudinary\Uploader::upload($file->root(),
				array(
					"resource_type" => "video",
					"public_id" => $public_id,
					"eager" => array(
						["format" => "mp4", "video_codec" => "auto"],
						["format" => "webm", "video_codec" => "auto"],
						["streaming_profile" => "full_hd", "format" => "m3u8"],
					),
					"eager_async" => true
				));

			$file->update(['cloudinaryID' => $response['public_id']]);
		} elseif(false) {
			$response = \Cloudinary\Uploader::upload_large($file->root(),
				array(
					"resource_type" => "video",
					"public_id" => $public_id,
					"eager" => array(
						["format" => "mp4", "video_codec" => "auto"],
						["format" => "webm", "video_codec" => "auto"],
						["streaming_profile" => "full_hd", "format" => "m3u8"],
					),
					"eager_async" => true
				));

	        $file->update(['cloudinaryID' => $response['public_id']]);
		}
		break;
	default:
		if($file->size() < 10485760*2) {
			$response = \Cloudinary\Uploader::upload($file->root(),
				array("public_id" => $file->uri(), "width" => 5000, "height" => 5000, "crop" => "limit"));

			$file->update(['cloudinaryID' => $response['public_id']]);
		}
	break;
	}
}

function k_cloudinary_destroy($file) {
	if($file->cloudinaryid()->isNotEmpty()) {
		switch ($file->type()) {
		case 'video':
			\Cloudinary\Uploader::destroy($file->cloudinaryid()->value(),
				array("resource_type" => "video"));
      $file->update(['cloudinaryID' => '']);
		break;
		default:
			\Cloudinary\Uploader::destroy($file->cloudinaryid()->value());
      $file->update(['cloudinaryID' => '']);
		break;
		}
	}
}

function k_cloudinary_update($file) {
	if($file->cloudinaryid()->isNotEmpty()) {
		switch ($file->type()) {
		case 'video':
			$response = \Cloudinary\Uploader::rename($file->cloudinaryid()->value(), $file->uri(), array("resource_type" => "video"));
			$file->update(['cloudinaryID' => $response['public_id']]);
		break;
		default:
			$response = \Cloudinary\Uploader::rename($file->cloudinaryid()->value(), $file->uri());
			$file->update(['cloudinaryID' => $response['public_id']]);
		break;
		}
	}
}
