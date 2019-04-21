<?php

if (c::get('kirby.vimeosync')) {

    if (!c::get('kirby.vimeosync.access_token')) {
        throw new Exception(
            'You can not upload a file without an access token. You can find this token on your app page, or generate ' .
            'one using `auth.php`.'
        );
        require_once __DIR__ . DS . 'fallback.php';
    } else {
        require_once __DIR__ . DS . 'methods.php';
    }

} else {

    require_once __DIR__ . DS . 'fallback.php';

}
