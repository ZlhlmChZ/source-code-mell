<?php

require('../include/mellivora.inc.php');

login_session_refresh();

send_cache_headers('scores', CONFIG_CACHE_TIME_SCORES);

head('Scoreboard');

if (cache_start('scores', CONFIG_CACHE_TIME_SCORES)) {

    $now = time();

    echo '
    <div class="row">
        <div class="col-lg-6">';

    $user_types = db_select_all(
        'user_types',
        array(
            'id',
            'title'
        )
    );

    // no user types
    if (empty($user_types)) {
        section_head(
            'Scoreboard',
            '<a href="'.CONFIG_SITE_URL.'json?view=scoreboard">
                <img src="'.CONFIG_SITE_URL.'img/json.png" title="View json" alt="json" class="discreet-inline small-icon" />
            </a>',
            false
        );

        $scores = db_query_fetch_all('
            SELECT
               u.id AS user_id,
               u.team_name,
               u.instanceid,
               co.id AS country_id,
               co.country_name,
               co.country_code,
               SUM(c.points) - COALESCE( (

SELECT sum( value )
FROM purchases
WHERE uid = u.id ) , 0
) AS score,
	       SUM(c.points) AS score1,
               MAX(s.added) AS tiebreaker
            FROM users AS u
            LEFT JOIN countries AS co ON co.id = u.country_id
            LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
            LEFT JOIN challenges AS c ON c.id = s.challenge
            WHERE u.competing = 1 AND u.instanceID = \''.$_SESSION["IID"].'\'
            GROUP BY u.id
            ORDER BY score DESC, tiebreaker ASC'
        );

        scoreboard($scores);
    }
    // at least one ser type
    else {
        foreach ($user_types as $user_type) {
            section_head(
                htmlspecialchars($user_type['title']) . ' scoreboard',
                '<a href="'.CONFIG_SITE_URL.'json?view=scoreboard&user_type='.$user_type['id'].'">
                    <img src="'.CONFIG_SITE_URL.'img/json.png" title="View json" alt="json" class="discreet-inline small-icon" />
                 </a>',
                false
            );

            $scores = db_query_fetch_all('
            SELECT
               u.id AS user_id,
               u.team_name,
               u.instanceid,
               co.id AS country_id,
               co.country_name,
               co.country_code,
               SUM(c.points) - SUM(p.value) AS score,
               MAX(s.added) AS tiebreaker
            FROM users AS u
            LEFT JOIN countries AS co ON co.id = u.country_id
            LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
            LEFT JOIN challenges AS c ON c.id = s.challenge
	    LEFT JOIN purchases AS p ON u.id = p.uid
            WHERE
              u.competing = 1 AND
              u.user_type = :user_type AND
              u.instanceID = \'1\'
            GROUP BY u.id
            ORDER BY score DESC, tiebreaker ASC',
                array(
                    'user_type'=>$user_type['id']
                )
            );

            scoreboard($scores);
        }
    }

    echo '
        </div>  <!-- / span6 -->

        <div class="col-lg-6">
        ';
//    section_head('Prize Pool');
//    $prizes = db_query_fetch_all('SELECT * FROM prizes WHERE instanceID = \''.$_SESSION["IID"].'\' ORDER BY instanceID ASC');
//    echo '
//    <table class="team-table table table-striped table-hover">
//    <thead>
//            <tr>
//              <th>#</th>
//              <th>Prize</th>
//              <th>Points Required</th>
//              <th>Quantity Remaining</th>
//            </tr>
//          </thead>
//          <tbody>';
//     foreach($prizes as $prize) {
//    echo '
//           <tr><td>',$prize['prizeID'],'</td><td>',$prize['prizeName'],'</td><td>',$prize['prizeQty'],'</td><td>',$prize['prizeReq'],'</td></tr>
//          ';
//     }
//    echo '      
//    </tbody>
//    </table>';
    section_head('Challenges');

    $categories = db_query_fetch_all('
        SELECT
           id,
           title,
           available_from,
           available_until
        FROM
           categories
        WHERE
           available_from < '.$now.' AND instanceID = \''.$_SESSION["IID"].'\'
        ORDER BY title'
    );
    
    $users3 = db_query_fetch_all('
                SELECT
                   id,
                   team_name
                FROM users
                WHERE
                   instanceID=:IID
                   AND competing = 1',
                array(
                    'IID'=>$_SESSION['IID']
                )
            );

    foreach($categories as $category) {

        echo '
        <table class="team-table table table-striped table-hover">
          <thead>
            <tr>
              <th>',htmlspecialchars($category['title']),'</th>
              <th>Points</th>
              <th>Completion Rate</th>
              <th>First solvers</th>
            </tr>
          </thead>
          <tbody>
         ';

        $challenges = db_query_fetch_all('
            SELECT
               id,
               title,
               points,
               available_from
            FROM challenges
            WHERE
              available_from < '.$now.' AND category=:category
            ORDER BY points ASC',
            array(
                'category'=>$category['id']
            )
        );

        foreach($challenges as $challenge) {

                 $users2 = db_query_fetch_all('
                SELECT
                   u.id,
                   u.team_name
                FROM users AS u
                JOIN submissions AS s ON s.user_id = u.id
                WHERE
                   u.competing = 1 AND
                   s.correct = 1 AND
                   s.challenge=:challenge
                ORDER BY s.added ASC',
                array(
                    'challenge'=>$challenge['id']
                )
            );
            $percentage = number_format((count($users2)/count($users3)) * 100,0);
            echo '
            <tr>
                <td>
                    <a href="challenge?id=',htmlspecialchars($challenge['id']),'">',htmlspecialchars($challenge['title']),'</a>
                </td>

                <td>
                    ',number_format($challenge['points']),'
                </td>

                <td>
                ',count($users2),'/',count($users3),' (',$percentage,'%)','
                </td>
                <td class="team-name">';
            
            
           
            
            $users = db_query_fetch_all('
                SELECT
                   u.id,
                   u.team_name
                FROM users AS u
                JOIN submissions AS s ON s.user_id = u.id
                WHERE
                   u.competing = 1 AND
                   s.correct = 1 AND
                   s.challenge=:challenge
                ORDER BY s.added ASC
                LIMIT 3',
                array(
                    'challenge'=>$challenge['id']
                )
            );

            if (count($users)) {
                $pos = 1;
                foreach($users as $user) {
                    echo get_position_medal($pos++),
                    '<a href="user?id=',htmlspecialchars($user['id']),'">',htmlspecialchars($user['team_name']), '</a><br />';
                }
            }

            else {
                echo '<i>Unsolved</i>';
            }

            echo '
                </td>
            </tr>';
        }
        echo '
        </tbody>
        </table>';
    }

    echo '
        </div> <!-- / span6 -->
    </div> <!-- / row -->
    ';




    cache_end('scores');
}

foot();
