<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Google_Service_Gmail::GMAIL_SEND);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$tokenPath = 'token.json';

if (file_exists($tokenPath)) {
    $client->setAccessToken(json_decode(file_get_contents($tokenPath), true));
}

if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
        $authUrl = $client->createAuthUrl();
        echo "Open this link:\n$authUrl\n";
        $authCode = trim(fgets(STDIN));
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);
    }
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
}

$service = new Google_Service_Gmail($client);

$message = new Google_Service_Gmail_Message();

$rawMessage = "From: you@gmail.com\r\n";
$rawMessage .= "To: you@gmail.com\r\n";
$rawMessage .= "Subject: Test Gmail API\r\n\r\n";
$rawMessage .= "Hello from Gmail API";

$encodedMessage = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

$message->setRaw($encodedMessage);

$service->users_messages->send("me", $message);

echo "Email sent!";
