<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONFIG_UC_MODERATOR);

head('Site management');
menu_management();

section_subhead('New hint');
form_start(CONFIG_SITE_ADMIN_RELPATH . 'actions/new_hint');
form_textarea('Body');

$opts = db_query_fetch_all('
    SELECT
       ch.id,
       ch.title,
       ca.title AS category
    FROM challenges AS ch
    LEFT JOIN categories AS ca ON ca.id = ch.category
    WHERE ca.instanceID = \''.$_SESSION["IID"].'\'
    ORDER BY ca.title, ch.title'
);

form_input_text('Value',0);
form_select($opts, 'Challenge', 'id', array_get($_GET, 'id', 0), 'title', 'category');
form_input_checkbox('Visible');
form_hidden('action', 'new');
form_button_submit('Create hint');
form_end();

foot();