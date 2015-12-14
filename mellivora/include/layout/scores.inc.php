<?php

function scoreboard ($scores) {

    echo '
    <table class="team-table table table-striped table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Team</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody>
     ';

    $i = 1;
    foreach($scores as $score) {

        echo '
        <tr>
          <td>',number_format($i++),'</td>
          <td class="team-name">
            <a href="user?id=',htmlspecialchars($score['user_id']),'">
              <span class="team_',htmlspecialchars($score['user_id']),'">
                ',htmlspecialchars($score['team_name']),'
              </span>
            </a>
          </td>
          <td>',number_format($score['score']),'</td>
        </tr>
        ';
    }

    echo '
      </tbody>
    </table>
    ';
}

function get_position_medal ($position, $returnpos = false) {
    switch ($position) {
        case 1:
            return '<img src="'.CONFIG_SITE_URL.'img/award_star_gold_3.png" title="First to solve this challenge!" alt="First to solve this challenge!" />';
        case 2:
            return '<img src="'.CONFIG_SITE_URL.'img/award_star_silver_3.png" title="Second to solve this challenge!" alt="Second to solve this challenge!" />';
        case 3:
            return '<img src="'.CONFIG_SITE_URL.'img/award_star_bronze_3.png" title="Third to solve this challenge!" alt="Third to solve this challenge!" />';
    }

    if ($returnpos) {
        return '#'.$position.', ';
    }
}