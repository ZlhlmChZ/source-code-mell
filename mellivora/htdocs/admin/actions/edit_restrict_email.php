<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);
enforce_instance_auth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_id($_POST['id']);
    validate_xsrf_token($_POST['xsrf_token']);

    if ($_POST['action'] == 'edit') {

       db_update(
          'restrict_email',
          array(
             'rule'=>$_POST['rule'],
             'enabled'=>$_POST['enabled'],
             'white'=>$_POST['whitelist'],
             'priority'=>$_POST['priority']
          ),
          array(
             'id'=>$_POST['id']
          )
       );

        redirect(CONFIG_SITE_ADMIN_RELPATH . 'list_restrict_email.php?generic_success=1');
    }

    else if ($_POST['action'] == 'delete') {

        if (!$_POST['delete_confirmation']) {
            message_error('Please confirm delete');
        }

        db_delete(
            'restrict_email',
            array(
                'id'=>$_POST['id']
            )
        );

        redirect(CONFIG_SITE_ADMIN_RELPATH . 'list_restrict_email.php?generic_success=1');
    }
}