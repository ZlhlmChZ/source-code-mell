<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST['xsrf_token']);

    if ($_POST['action'] == 'new') {

       $id = db_insert(
          'challenges',
          array(
             'added'=>time(),
             'added_by'=>$_SESSION['id'],
             'title'=>$_POST['title'],
             'description'=>$_POST['description'],
             'flag'=>$_POST['flag'],
             'automark'=>$_POST['automark'],
             'case_insensitive'=>$_POST['case_insensitive'],
             'points'=>$_POST['points'],
             'category'=>$_POST['category'],
             'num_attempts_allowed'=>$_POST['num_attempts_allowed'],
             'min_seconds_between_submissions'=>$_POST['min_seconds_between_submissions'],
             'available_from'=>strtotime('2015-02-03 21:17:57'),
             'available_until'=>strtotime('2099-02-03 21:17:57'),
	     'instanceID'=>$_SESSION['IID']
          )
       );

       if ($id) {
          redirect(CONFIG_SITE_ADMIN_RELPATH . 'edit_challenge.php?id='.$id);
       } else {
          message_error('Could not insert new challenge.');
       }
    }
}