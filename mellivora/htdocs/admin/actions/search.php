<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);
enforce_instance_auth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST['xsrf_token']);

    if ($_POST['search_in'] == 'users') {
        redirect(CONFIG_SITE_ADMIN_RELPATH.'list_users?search_for='.$_POST['search_for']);
    }

    else if ($_POST['search_in'] == 'ip_log') {
        redirect(CONFIG_SITE_ADMIN_RELPATH.'list_ip_log?ip='.$_POST['search_for']);
    }
}