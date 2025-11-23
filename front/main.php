<?php

/**
 * -------------------------------------------------------------------------
 * DjangoIntegrator plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2025 by the DjangoIntegrator plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/djangointegrator
 * -------------------------------------------------------------------------
 */

// This is the only way to override the X-Frame-Options header sent by GLPI later in the process.
header('X-Frame-Options: ');

include(GLPI_ROOT . "/inc/includes.php");
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
    echo "<div class='center'><h2>" . __('Error: User not logged in or session expired.', 'djangointegrator') . "</h2></div>";
} else {
    $user_email = '';
    $user = new User();
    $user->getFromDB($user_id); // Get user data, mainly for the name if we need to show an error.

    $email_obj = new UserEmail();
    $found_emails = $email_obj->find(['users_id' => $user_id, 'is_default' => 1]);

    if (!empty($found_emails)) {
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
        $username = $user->fields['name'] ?? "ID " . $user_id;
        $message  = sprintf(
            __('Error: No default email address found for the user "%s" in GLPI.', 'djangointegrator'),
            $username
        );
        echo "<div class='center'><h2>{$message}</h2></div>";
    }
}

Html::footer();
