<?php
require('../../config.inc.php');
require('autoload.php');

// Application: TestLeague 
$redu = 'http://fman.hopto.org/lib/experiments/gitlab.php';

$provider = new Omines\OAuth2\Client\Provider\Gitlab(
    ['clientId' => 
       '99b938c71df5dc93ac0c5dc81cdc6e33906d9708e8297d1fe2b99f872279fda7',

      'clientSecret' => 
         '57d10fc0e6fe9f95ee9faf371dd5d02033fa0b0e63f3db966763c405e5b118ef',
      'redirectUri' => $redu
         
    ]
);

session_start();

if (!isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    var_dump($_GET['state']);
    die();
    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        echo '<pre>';
        var_dump($user->toArray());
        echo '</pre>';
        // Use these details to create a new profile
        printf('<br>getName %s!', $user->getName());
        printf('<br>getEmail %s!', $user->getEmail());
        printf('<br>getUserName %s!', $user->getUserName());
        echo '<br>';



    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}