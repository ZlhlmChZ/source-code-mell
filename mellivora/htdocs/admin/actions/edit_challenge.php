<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);
// validateAuthority(4, $_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_id($_POST['id']);
    validate_xsrf_token($_POST['xsrf_token']);

    if ($_POST['action'] == 'edit') {

       db_update(
            'challenges',
            array(
                'title'=>$_POST['title'],
                'description'=>$_POST['description'],
                'flag'=>$_POST['flag'],
                'automark'=>$_POST['automark'],
                'case_insensitive'=>$_POST['case_insensitive'],
                'points'=>$_POST['points'],
                'category'=>$_POST['category'],
                'num_attempts_allowed'=>$_POST['num_attempts_allowed'],
                'min_seconds_between_submissions'=>$_POST['min_seconds_between_submissions'],
                'relies_on'=>$_POST['relies_on']
            ),
            array('id'=>$_POST['id'])
        );

        redirect(CONFIG_SITE_ADMIN_RELPATH . 'edit_challenge.php?id='.$_POST['id'].'&generic_success=1');
    }

    else if ($_POST['action'] == 'delete') {

        if (!$_POST['delete_confirmation']) {
            message_error('Please confirm delete');
        }

        delete_challenge_cascading($_POST['id']);

        redirect(CONFIG_SITE_ADMIN_RELPATH . '?generic_success=1');
    }

    else if ($_POST['action'] == 'upload_file') {

        store_file($_POST['id'], $_FILES['file']);

        redirect(CONFIG_SITE_ADMIN_RELPATH . 'edit_challenge.php?id='.$_POST['id'].'&generic_success=1');
    }

    else if ($_POST['action'] == 'delete_file') {
        delete_file($_POST['id']);

        redirect(CONFIG_SITE_ADMIN_RELPATH . 'edit_challenge.php?id='.$_POST['challenge_id'].'&generic_success=1');
    }
    
     else if ($_POST['action'] == 'purge_file') {
        
          db_update(
            'challenges',
            array(
                'cloneOf'=>'0'
            ),
            array('id'=>$_POST['challenge_id'])
        );
          

        redirect(CONFIG_SITE_ADMIN_RELPATH . 'edit_challenge.php?id='.$_POST['challenge_id'].'&generic_success=1');
    }
}