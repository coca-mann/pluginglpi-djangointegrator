<?php

// GLPI environment is loaded by the framework
include(GLPI_ROOT . "/plugins/djangointegrator/inc/defines.php");

Html::header(
    __('DjangoIntegrator', 'djangointegrator'),
    $_SERVER['PHP_SELF'],
    'tools',
    'djangointegrator'
);

$user_id = Session::getLoginUserID();

if (empty($user_id)) {
    // Case 1: User is not logged in or session has expired.
    Html::displayErrorAndDie(__('Error: User not logged in or session expired.', 'djangointegrator'));
}

// User is logged in, now find their default email from the correct table.
$user_email = '';
$user = new User();
$user->getFromDB($user_id); // Get user data, mainly for the name if we need to show an error.

$email_obj = new UserEmail();
$found_emails = $email_obj->find([
    'users_id'   => $user_id,
    'is_default' => 1
]);

// The find() method returns an array of results. We need to check if it's not empty.
if (!empty($found_emails)) {
    // SUCCESS: Default email found.
    // Let's get the first result from the array.
    $email_data = reset($found_emails);
    $user_email = $email_data['email'];

    $payload = [
        'uid'   => $user_id,
        'email' => $user_email,
        'name'  => $user->fields['name'],
        'ts'    => time(),
    ];

    $json_payload   = json_encode($payload);
    $base64_payload = base64_encode($json_payload);
    $signature      = hash_hmac('sha256', $base64_payload, SECRET_KEY);

    $iframe_src = DJANGO_URL . "?payload={$base64_payload}&sig={$signature}";

    echo "<iframe src='{$iframe_src}' style='width: 100%; height: 80vh; border: none;'></iframe>";

} else {
    // ERROR: No default email found for the user.
    $username = $user->fields['name'] ?? "ID " . $user_id;
    $message  = sprintf(
        __('Error: No default email address found for the user "%s" in GLPI.', 'djangointegrator'),
        $username
    );
    echo "<div class='center'><h2>{$message}</h2></div>";
}

Html::footer();
