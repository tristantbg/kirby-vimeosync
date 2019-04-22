<?php

function k_vimeo_lib()
{
    $client_id     = c::get('kirby.vimeosync.client_id');
    $client_secret = c::get('kirby.vimeosync.client_secret');
    $access_token  = c::get('kirby.vimeosync.access_token');
    // Instantiate the library with your client id, secret and access token (pulled from dev site)
    $lib = new \Vimeo\Vimeo($client_id, $client_secret, $access_token);

    return $lib;
}

function k_vimeo_status($file)
{
    if ($file->type() == 'video' && $file->vimeoURI()->isNotEmpty()) {
        $lib = k_vimeo_lib();
        // Make an API call to see if the video is finished transcoding.
        $status = $lib->request($file->vimeoURI() . '?fields=transcode.status');
        return $status['body']['transcode']['status'] == 'complete';
    }
}

function k_vimeo_upload($file)
{
    if ($file->type() == 'video') {

        $lib = k_vimeo_lib();

        try {
            // Upload the file and include the video title and description.
            $uri = $lib->upload($file->root(), array(
                'name'        => $file->page()->title() . ' (' . $file->filename() . ')',
                'privacy' => [
                  'download' => false,
                  'embed' => 'private',
                  'view' => 'nobody',
                ]
            ));

            k_vimeo_write_infos($file, $uri);

            if (c::get('kirby.vimeosync.project_id')) {
                // Make an API call to edit the folder of the video.
                $response = $lib->request('/me/projects/' . c::get('kirby.vimeosync.project_id') . $uri, [], 'PUT');
            }

        } catch (VimeoUploadException $e) {
            // We may have had an error. We can't resolve it here necessarily, so report it to the user.
            echo 'Error uploading ' . $file->root() . "\n";
            echo 'Server reported: ' . $e->getMessage() . "\n";
        } catch (VimeoRequestException $e) {
            echo 'There was an error making the request.' . "\n";
            echo 'Server reported: ' . $e->getMessage() . "\n";
        }
    }
}

function k_vimeo_destroy($file)
{
    if ($file->vimeoURI()->isNotEmpty()) {
        $lib = k_vimeo_lib();
        $lib->request($file->vimeoURI(), null, 'DELETE');
    }
}

function k_vimeo_update($file)
{
    if ($file->vimeoURI()->isNotEmpty()) {

    }
}

function k_vimeo_replace($oldFile, $file)
{
    if ($file->vimeoURI()->isNotEmpty()) {
        $lib = k_vimeo_lib();
        $lib->replace($file->vimeoURI(), $file->root());
    }
}

function k_vimeo_write_infos($file, $uri)
{
    $lib = k_vimeo_lib();
    // $response        = $lib->request($uri . '?fields=name,description,link,pictures,files', ['per_page' => 1], 'GET');
    $response        = $lib->request($uri, ['per_page' => 1], 'GET');
    $body            = $response['body'];
    $vimeoThumbnails = isset($body['pictures']) ? $body['pictures']['sizes'] : [];
    $vimeoFiles      = isset($body['files']) ? $body['files'] : [];
    dump($body); die;

    usort($vimeoThumbnails, function ($item1, $item2) {
        return $item1['width'] <=> $item2['width'];
    });

    usort($vimeoFiles, function ($item1, $item2) {
        return $item1['width'] <=> $item2['width'];
    });

    if (isset($body['error'])) {
        if ($file) {
            $file->update(array(
                'vimeoData'        => '',
                'vimeoName'        => 'Not found…',
                'vimeoDescription' => '',
                'vimeoURL'         => '',
                'vimeoThumbnails'  => '',
                'vimeoFiles'       => '',
                'template'         => 'vimeo',
            ));
        }

    } else {

        if ($file) {

            $file->update(array(
                'vimeoURI'         => $uri,
                'vimeoData'        => yaml::encode($body),
                'vimeoName'        => $body['name'],
                'vimeoDescription' => $body['description'],
                'vimeoURL'         => $body['link'],
                'vimeoThumbnails'  => yaml::encode($vimeoThumbnails),
                'vimeoFiles'       => yaml::encode($vimeoFiles),
                'template'         => 'vimeo',
            ));

        }

    }
}
