<?php

require('../include/mellivora.inc.php');

login_session_refresh();


send_cache_headers('home', CONFIG_CACHE_TIME_HOME);

head('Home');

if (cache_start('home', CONFIG_CACHE_TIME_HOME)) {
    require(CONFIG_PATH_THIRDPARTY . 'nbbc/nbbc.php');
    $bbc = new BBCode();
    $bbc->SetEnableSmileys(false);
    
    $news = db_query_fetch_all('SELECT * FROM news WHERE instanceID =\''.$_SESSION["IID"].'\' ORDER BY added DESC');
    
    foreach ($news as $item) {
        echo '
        <div class="news-container">';
            section_head($item['title']);
            echo '
            <div class="news-body">
                ',$item['body'],'
            </div>
        </div>
        ';
    }

    cache_end('home');
}

foot();