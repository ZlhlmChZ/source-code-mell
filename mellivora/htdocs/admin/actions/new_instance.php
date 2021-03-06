<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);
enforce_instance_auth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST['xsrf_token']);

    if ($_POST['action'] == 'new') {

            
             $user_id = db_insert(
        'users',
        array(
            'email'=>$_POST['email'],
            'passhash'=>make_passhash($_POST['password']),
            'team_name'=>$_POST['team_name'],
            'added'=>time(),
            'enabled'=>(CONFIG_ACCOUNTS_DEFAULT_ENABLED ? '1' : '0'),
            'user_type'=>(isset($type) ? $type : 0),
            'country_id'=>'200',
            'class'=>'1',
            'competing'=>'0'
		 
        )
    );
    $instanceID = db_insert(
          'instances',
          array(
             'name'=>$_POST['name'],
             'instanceURI'=>$_POST['uri'],
             'authoratativeAccountID'=>$user_id
          )
        );
    
   
        
        
        if($_POST['import_sample_challenge_set'] == true){
        
//        db_insert_manual('insert into categories (instanceID,added, added_by, title, description, available_from, available_until) select '.$instanceID.' as instanceID,added, added_by, title,description, available_from, available_until from categories where instanceID = 0');
//        $types = db_query_fetch_all('SELECT * FROM categories WHERE instanceID =\''.$instanceID.'\' ORDER BY instanceID ASC');
//        
//        foreach($types as $type){
//            question_replication($type['title'],$type['id']);
//        }
            
            // Get all categories from base instance.
            $baseInstanceCategories = db_query_fetch_all('SELECT * FROM categories WHERE instanceID = 0');
            
            foreach($baseInstanceCategories as $baseCategory){
                $baseChallenges = db_query_fetch_all('SELECT * FROM challenges WHERE category ='.$baseCategory['id']);
                
                // create new category and retrive autoincremented ID
                $categoryID = db_insert(
          'categories',
          array(
             'added'=>time(),
             'added_by'=>$_SESSION['id'],
             'title'=>$baseCategory['title'],
             'instanceID'=>$instanceID,
             'description'=>$baseCategory['description'],
             'available_from'=>strtotime('2015-02-03 21:17:57'),
             'available_until'=>strtotime('2099-02-03 21:17:57')
          )
       );
                
                // loop through each challenge
                foreach($baseChallenges as $baseChallenge){
                    
                    $challengeID = db_insert(
                    'challenges', array(
                    'added' => time(),
                    'added_by' => $_SESSION['id'],
                    'title' => $baseChallenge['title'],
                    'description' => $baseChallenge['description'],
                    'flag' => $baseChallenge['flag'],
                    'automark' => $baseChallenge['automark'],
                    'case_insensitive' => $baseChallenge['case_insensitive'],
                    'points' => $baseChallenge['points'],
                    'category' => $categoryID,
                    'num_attempts_allowed' => $baseChallenge['num_attempts_allowed'],
                    'min_seconds_between_submissions' => $baseChallenge['min_seconds_between_submissions'],
                    'available_from' => strtotime('2015-02-03 21:17:57'),
                    'available_until' => strtotime('2099-02-03 21:17:57'),
                    'instanceID' => $instanceID,
                    'cloneOf'=> $baseChallenge['id']
                        )
                );
                     
                    
                     $challengeHints = db_query_fetch_all('SELECT * FROM hints WHERE challenge ='.$baseChallenge['id']);
                     
                      foreach($challengeHints as $hint){
                          
                           $id = db_insert(
                                'hints', array(
                            'added' => time(),
                            'added_by' => $_SESSION['id'],
                            'challenge' => $challengeID,
                            'visible' => $hint['visible'],
                            'body' => $hint['body'],
                            'instanceID' => $instanceID,
                            'value' => $hint["value"]
                                )
                        );
                    }
                      
                }
                
                
            }
        }
            redirect(CONFIG_SITE_ADMIN_RELPATH . 'list_instance.php');
        
    }
}
