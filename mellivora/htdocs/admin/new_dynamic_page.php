<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);
enforce_instance_auth();

head('Site management');
menu_management();

section_subhead('New dynamic page');
form_start(CONFIG_SITE_ADMIN_RELPATH . 'actions/new_dynamic_page');
form_input_text('Title');
form_textarea('Body');

dynamic_visibility_select();

user_class_select();

form_hidden('action', 'new');

form_button_submit('Create');
form_bbcode_manual();
form_end();

foot();