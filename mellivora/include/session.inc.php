<?php
function user_is_logged_in () {
    if (isset($_SESSION['id'])) {
        return $_SESSION['id'];
    }

    return false;
}

function user_is_staff () {
    if (user_is_logged_in() && $_SESSION['class'] >= CONFIG_UC_MODERATOR) {
        return true;
    }

    return false;
}

function showEditorOnly(){
    
    return '<script>   window.onload = function(){CKEDITOR.replace("body", {
 filebrowserUploadUrl: "actions/upload.php"
})};</script>';

}
function showConfirmDialogNoEditor($formID, $message){

return '<script>   window.onload = function(){

$("#'.$formID.'").click(function(e){
                  e.preventDefault();
bootbox.dialog({
message: "'.$message.'",
title: "Confirm deletion",
buttons: {
danger: {
label: "Confirm deletion",
className: "btn-danger",
callback: function() {
$("#'.$formID.'").closest("form").submit();
}
},
}
}); 
});


} </script>';
}

function showConfirmDialogEditor($formID, $message){

return '<script>   window.onload = function(){CKEDITOR.replace("body", {
 filebrowserUploadUrl: "actions/upload.php"
});

$("#'.$formID.'").click(function(e){
                  e.preventDefault();
bootbox.dialog({
message: "'.$message.'",
title: "Confirm deletion",
buttons: {
danger: {
label: "Confirm deletion",
className: "btn-danger",
callback: function() {
$("#'.$formID.'").closest("form").submit();
}
},
}
}); 
});


} </script>';
}
function validate_xsrf_token($token) {
    if ($token != $_SESSION['xsrf_token']) {
        log_exception(new Exception('Invalid XSRF token. Was: "' . $token.'". Wanted: "' . $_SESSION['xsrf_token'].'"'));
        logout();

        // explicitly exit, even though
        // it's already done in logout()
        exit();
    }
}

function validateAuthority ($type, $id){
    $sqlTable = "";
    switch($type){
	case 1: $sqlTable = "users"; break;
	case 2: $sqlTable = "categories"; break;
	case 3: $sqlTable = "submissions"; break;
	case 4: $sqlTable = "challenges"; break;
	
    }
    $db = db_select_one(
        $sqlTable,
        array(
            'instanceID',
	    'id'
        ),
        array(
            'id'=>$id
        )
    );
    
    if($db['instanceID'] != $_SESSION['IID']){
	if($_SESSION['class'] != 2){
	log_exception(new Exception('Attempted access to unauthorized instance.'));
	}
    //	logout();
	
    }
    
    
    
}

function loadInstance($id){
       $instance = db_query_fetch_one('SELECT * FROM instances WHERE id ='.$id);
    
       $_SESSION["IName"] = $instance['name'];
       $_SESSION["IID"] = $instance['id'];
       $_SESSION["IRQ"] = $instance['registrationToken'];
}

function loadInstanceURI($uri){
    $instance = db_query_fetch_one('SELECT * FROM instances WHERE instanceURI ='.$uri);
    
       $_SESSION["IName"] = $instance['name'];
       $_SESSION["IID"] = $instance['id'];
       $_SESSION["IRQ"] = $instance['registrationToken'];
}
function user_class_name ($class) {
    switch ($class) {
        case CONFIG_UC_SUPERADMIN:
            return 'Superadmin';
        case CONFIG_UC_MODERATOR:
            return 'Game Master';
        case CONFIG_UC_USER:
            return 'User';
    }

    return 'Unknown user class';
}

function login_session_refresh() {
    // if users session has expired, but they have the "remember me" cookie
    if (!user_is_logged_in() && login_cookie_isset()) {
        login_session_create_from_login_cookie();
    }
}

function login_create($email, $password, $remember_me) {

    if(empty($email) || empty($password)) {
        message_error('Please enter your email and password.');
    }

    $user = db_select_one(
        'users',
        array(
            'id',
            'passhash',
            'class',
            'team_name',
            'enabled',
            '2fa_status',
            'instanceID'
        ),
        array(
            'email'=>$email
        )
    );
    
    $instanceInformation = db_select_one(
        'instances',
        array(
            'id',
            'instanceURI',
            'name',
            'authoratativeAccountID',
            'registrationToken'
        ),
        array(
            'id'=>$user['instanceID']
        )
    );
    
    $_SESSION['UIID'] = $user['instanceID'];
    $_SESSION['IID'] = $instanceInformation['id'];
    $_SESSION['IRQ'] = $instanceInformation['registrationToken'];
    $_SESSION['IName'] = $instanceInformation['name'];
    $_SESSION['IAID'] = $instanceInformation['authoratativeAccountID'];
    $_SESSION['UName'] = $user['team_name'];

    if (!check_passhash($password, $user['passhash'])) {
        message_error('Login failed');
    }

    if (!$user['enabled']) {
        message_generic('Ooops!', 'Your account is not enabled.
        If you have just registered, this is normal - an email with instructions will be sent out closer to the event start date!
        In all other cases, please contact the system administrator with any questions.');
    }

    login_session_create($user);
    
    if ($remember_me) {
        login_cookie_create($user);
    }

    log_user_ip($user['id']);

    return true;
}

function verifySAGlobal(){
    // verifies superadmin status at all times, even if assuming instance view.
    if($_SESSION['class'] == 2){
         return true;
    }
    else{
         return false;
    }
}
function verifySA(){
    // verifies superadmin status by checking class.
    // verifySA only works when SA is not assuming instance view
    
    // DEPRECATED
if ($_SESSION['class'] == 2 && $_SESSION['IID'] == 0){
    return true;
}
else {return false;}
}

use Mailgun\Mailgun;
function sendEmail($to, $subject, $text){
    
    //$to = 'julietohara@outlook.com'; // HARDCORED FOR DEVELOPMENT PURPOSES

    $mgClient = new Mailgun('key-239aff439d5c36b7a4c2cd3607d50a63');
    $domain = "sandbox4a531ad87c044de49c1ee15c6d9f209b.mailgun.org";

    $result = $mgClient->sendMessage($domain, array(
    'from'    => 'CTF Director <ctf-director@sandbox4a531ad87c044de49c1ee15c6d9f209b.mailgun.org>',
    'to'      => 'CTF Platform User<'.$to.'>',
    'subject' => $subject,
    'text'    => $text
     ));
}

function login_session_create($user) {
    $_SESSION['id'] = $user['id'];
    $_SESSION['class'] = $user['class'];
    $_SESSION['enabled'] = $user['enabled'];
    $_SESSION['2fa_status'] = $user['2fa_status'];
    $_SESSION['fingerprint'] = get_fingerprint();
    $_SESSION['xsrf_token'] = generate_random_string(64);
}

function login_cookie_create($user, $token_series = false) {

    $time = time();
    $ip = get_ip(true);

    if (!$token_series) {
        $token_series = generate_random_string(16);
    }
    $token = generate_random_string(64);

    db_insert(
        'cookie_tokens',
        array(
            'added'=>$time,
            'ip_created'=>$ip,
            'ip_last'=>$ip,
            'user_id'=>$user['id'],
            'token_series'=>$token_series,
            'token'=>$token
        )
    );

    $cookie_content = array (
        't'=>$token,
        'ts'=>$token_series
    );

    setcookie(
        'login_tokens', // name
        json_encode($cookie_content), // content
        $time+CONFIG_COOKIE_TIMEOUT, // expiry
        '/', // path
        null, // domain
        CONFIG_SSL_COMPAT, // serve over SSL only
        true // httpOnly
    );
}

function login_cookie_destroy() {

    if (!login_cookie_isset()) {
        return;
    }

    $cookie = login_cookie_decode();

    db_delete(
        'cookie_tokens',
        array(
            'token'=>$cookie['t'],
            'token_series'=>$cookie['ts']
        )
    );

    unset($_COOKIE['login_tokens']);
    setcookie('login_tokens', '', time() - 3600);
}

function login_cookie_isset() {
    return isset($_COOKIE['login_tokens']);
}

function login_cookie_decode() {

    if (!login_cookie_isset()) {
        log_exception(new Exception('Tried to decode nonexistent login cookie'));
        logout();
    }

    $cookieObj = json_decode($_COOKIE['login_tokens']);

    return array('t'=>$cookieObj->{'t'}, 'ts'=>$cookieObj->{'ts'});
}

function login_session_create_from_login_cookie() {

    if (!login_cookie_isset()) {
        log_exception(new Exception('Tried to create session from nonexistent login cookie'));
        logout();
    }

    $cookie = login_cookie_decode();

    $cookie_token_entry = db_select_one(
        'cookie_tokens',
        array(
            'user_id'
        ),
        array(
            'token'=>$cookie['t'],
            'token_series'=>$cookie['ts']
        )
    );

    if (!$cookie_token_entry['user_id']) {

        /*
         * TODO, here we could check:
         *    - if the token_series matches but
         *    - the token does not match
         * this probably means someone has already
         * used this cookie to re-authenticate.
         * This probably mean the cookie has been stolen.
         */

        log_exception(new Exception('An invalid cookie token was used. Cookie likely stolen. TS: ' . $cookie['ts']));
        logout();

        // explicitly exit here, even
        // though we do in redirect()
        exit();
    }

    // get the user whom this token
    // was issued for
    $user = db_select_one(
        'users',
        array(
            'id',
            'class',
            'enabled',
            '2fa_status'
        ),
        array(
            'id'=>$cookie_token_entry['user_id']
        )
    );

    // remove the cookie token from the db
    // as it is used, and we don't want it
    // to every be used again
    db_delete(
        'cookie_tokens',
        array(
            'token'=>$cookie['t'],
            'token_series'=>$cookie['ts']
        )
    );

    // issue a new login cookie for the user
    // using the same token series identifier
    login_cookie_create($user, $cookie['ts']);

    login_session_create($user);
}

function log_user_ip($userId) {

    if (!$userId) {
        message_error('No user ID was supplied to the IP logging function');
    }

    $time = time();
    $ip = get_ip(true);

    $entry = db_select_one(
        'ip_log',
        array(
            'id',
            'times_used'
        ),
        array(
            'user_id'=>$userId,
            'ip'=>$ip
        )
    );

    // if the user has logged in with this IP previously
    if ($entry['id']) {

        db_query_fetch_none('
            UPDATE ip_log SET
               last_used=UNIX_TIMESTAMP(),
               ip=:ip,
               times_used=times_used+1
            WHERE id=:id',
            array(
                'ip'=>$ip,
                'id'=>$entry['id']
            )
        );
    }
    // if this is a new IP
    else {
        db_insert(
            'ip_log',
            array(
                'added'=>$time,
                'last_used'=>$time,
                'user_id'=>$userId,
                'ip'=>$ip
            )
        );
    }
}

function make_passhash($password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    if (!$hash) {
        $error_message = 'Could not generate password hash. Do you have PHP 5.3.7+ installed?';
        log_exception(new Exception($error_message));
        message_error($error_message);
    }

    return $hash;
}

function check_passhash($password, $hash) {
    return password_verify($password, $hash);
}

function get_fingerprint() {
    return md5(get_ip());
}

function login_session_destroy () {
    session_unset();
    session_destroy();
}

function enforce_instance_auth(){

    login_session_refresh();
    if(!verifySAGlobal()){
        logout();
    }
}

function enforce_authentication($minClass = CONFIG_UC_USER) {
    login_session_refresh();

    if (!user_is_logged_in()) {
        logout();
    }
    
    if ($_SESSION['IID'] != $_SESSION['UIID'] && !verifySAGlobal()){
    logout();
    }
 

    if ($_SESSION['class'] < $minClass) {
        log_exception(new Exception('Class less than required'));
        logout();
    }

    if (user_is_staff() && $_SESSION['fingerprint'] != get_fingerprint()) {
        logout();
    }

    enforce_2fa();
}

function enforce_2fa() {
    if ($_SESSION['2fa_status'] == 'enabled') {
        redirect('two_factor_auth');
    }
}

function session_set_2fa_authenticated() {
    $_SESSION['2fa_status'] = 'authenticated';
}

function logout() {
    $id = $_SESSION['IID'];
    login_session_destroy();
    login_cookie_destroy();
    
    redirect("home?instanceID=".$id);
}

function register_account($email, $password, $team_name, $country, $type = null,$phoneNo,$age,$eduI,$eduLevel,$fullName,$instanceID) {

    if (!CONFIG_ACCOUNTS_SIGNUP_ALLOWED) {
        message_error('Registration is currently closed.');
    }
    

    if (empty($email) || empty($password) || empty($team_name)) {
        message_error('Please fill in all the details correctly.');
    }

    if (isset($type) && !is_valid_id($type)) {
        message_error('That does not look like a valid team type.');
    }

    if (strlen($team_name) > CONFIG_MAX_TEAM_NAME_LENGTH || strlen($team_name) < CONFIG_MIN_TEAM_NAME_LENGTH) {
        message_error('Your team name was too long or too short.');
    }

    validate_email($email);

    if (!allowed_email($email)) {
        message_error('Email not on whitelist. Please choose a whitelisted email or contact organizers.');
    }

    $num_countries = db_select_one(
        'countries',
        array('COUNT(*) AS num')
    );

    if (!isset($country) || !is_valid_id($country) || $country > $num_countries['num']) {
        message_error('Please select a valid country.');
    }

    $user = db_select_one(
        'users',
        array('id'),
        array(
            'team_name' => $team_name,
            'email' => $email
        ),
        null,
        'OR'
    );

    if ($user['id']) {
        message_error('An account with this team name or email already exists.');
    }

    $user_id = db_insert(
        'users',
        array(
            'email'=>$email,
            'passhash'=>make_passhash($password),
            'team_name'=>$team_name,
            'added'=>time(),
            'enabled'=>(CONFIG_ACCOUNTS_DEFAULT_ENABLED ? '1' : '0'),
            'user_type'=>(isset($type) ? $type : 0),
            'country_id'=>$country,
			'DOB'=>$age,
            'mobileNo'=>$phoneNo,
			'eduInstitution'=>$eduI,
			'eduLevel'=>$eduLevel,
            'fullName'=>$fullName,
            'instanceID'=>$instanceID

			
        )
    );

    // insertion was successful
    if ($user_id) {

        // log signup IP
        log_user_ip($user_id);

        
        // if account isn't enabled by default, display message and die
        if (!CONFIG_ACCOUNTS_DEFAULT_ENABLED) {
            message_generic('Signup successful', 'Thank you for registering!
            Your chosen email is: ' . htmlspecialchars($email) . '.
            Make sure to check your spam folder as emails from us may be placed into it.
            Please stay tuned for updates!');
        } else {
            return true;
        }
    }

    // no rows were inserted
    return false;
}
