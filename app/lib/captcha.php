<?php

function verify_captcha($responseToken)
{
    if (empty($responseToken)) {
        return false;
    }

    $secret = $_ENV['RECAPTCHA_SECRET_KEY'];
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret' => $secret,
        'response' => $responseToken
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    return $response['success'] === true;
}
