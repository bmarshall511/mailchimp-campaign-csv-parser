<?php

/**
 * Define the root directory of the app.
 */
if( ! defined( 'APP_ROOT' ) ) {
    define( 'APP_ROOT', dirname( __FILE__ ) );
}

// Get config options.
$industry = isset( $_REQUEST['industry'] ) ? $_REQUEST['industry'] : 'Media and Publishing';
$limit = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : 500;

/**
 * Include the MailChimp Report Generator class.
 */
require_once( 'lib/MailChimp_Campaign_CSV_Parser.class.php' );

// Initialize the MailChimp Report Generator class.
$MC = new MailChimp_Campaign_CSV_Parser;

// Get data from the MailChimp Report Generator class.
$MC->config['mailchimp_campaign_export_file'] = 'reports/Nov_11_2014.csv';

/**
 * Select the industry that relates to the email campaigns
 *
 * Available options:
 *
 * - Agriculture and Food Services
 * - Architecture and Construction
 * - Arts and Artists
 * - Beauty and Personal Care
 * - Business and Finance
 * - Computers and Electronics
 * - Construction
 * - Consulting
 * - Creative Services/Agency
 * - Daily Deals/E-Coupons
 * - eCommerce
 * - Education and Training
 * - Entertainment and Events
 * - Gambling
 * - Games
 * - Government
 * - Health and Fitness
 * - Hobbies
 * - Home and Garden
 * - Insurance
 * - Legal
 * - Manufacturing
 * - Marketing and Advertising
 * - Media and Publishing
 * - Medical, Dental, and Healthcare
 * - Mobile
 * - Music and Musicians
 * - Non-Profit
 * - Other
 * - Pharmaceuticals
 * - Photo and Video
 * - Politics
 * - Professional Services
 */
$MC->config['industry'] = $industry;

// Get and parse the first 500 campaigns.
$MC->parse_campaign_export( $limit );
$data = $MC->array;
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MailChimp Campaign CSV Parser Library Demo | Ben Marshall</title>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="assets/build/css/normalize.css">
<link rel="stylesheet" href="assets/build/css/style.css">
<?php if ( isset( $_REQUEST['print'] ) && $_REQUEST['print'] ): ?>
<style>
.wrapper {
    max-width: 820px;
}
</style>
<?php endif; ?>
</head>
<body class="color-8">
<?php if ( ! isset( $_REQUEST['print'] ) ): ?>
<div class="settings" id="settings">
    <h5>Configuration Settings</h5>

    <p><label>Number of Campaigns</label>
    <input type="number" value="<?php echo $limit; ?>" id="limit"></p>

    <p><label>Select Industry</label>
    <select id="industry">
        <?php foreach( $MC->industry as $k => $a ): ?>
            <option value="<?php echo $k; ?>"<?php if( $MC->config['industry'] == $k ): ?> selected="selected"<?php endif; ?>><?php echo $k; ?></option>
        <?php endforeach; ?>
    </select></p>

    <p><input type="submit" value="Update" id="update"></p>
</div>
<?php endif; ?>
<div class="wrapper">
  <header>
    <h1>MailChimp Campaign CSV Parser Library Demo</h1>
    <h2><?php echo date( 'F j, Y', $data['summary']['start_date'] ); ?> - <?php echo date( 'F j, Y', $data['summary']['end_date'] ); ?> (<?php echo number_format( $data['summary']['num_days'], 0 ); ?> days)</h2>
  </header>
  <section>
    <h2>Summary</h2>
    <div class="half">
      <p>In the past <?php echo number_format( $data['summary']['num_days'], 0 ); ?> days starting <?php echo date( 'D., F j, Y', $data['summary']['start_date'] ); ?>, <?php echo number_format( $data['summary']['total_campaigns'], 0 ); ?> campaigns have been sent totaling <?php echo number_format( $data['summary']['total_recipients'], 0 ); ?> recipients. That's an average of <?php echo number_format( $data['summary']['total_recipients'] / $data['summary']['total_campaigns'], 0 ); ?> emails per campaign and <?php echo number_format( $data['summary']['total_recipients'] / $data['summary']['num_days'], 0 ); ?> recipients per day.</p>
      <p>On average, <?php echo $data['summary']['avg_bounce_rate']; ?>% emails were bounced back, <?php echo $data['summary']['avg_trash_spam_rate']; ?>% were trashed or marked spam, <?php echo $data['summary']['avg_abuse_complaint_rate']; ?>% reported an abuse complaint and <?php echo $data['summary']['avg_unique_open_rate']; ?>% were opened with <?php echo $data['summary']['avg_unique_click_rate']; ?>% of those resulting in a click.</p>
      <p>With a total of <?php echo number_format( $data['summary']['total_unique_clicks']); ?> unique clicks, each click (lead) estimated at $<?php echo $MC->config['lead_value']; ?> and an average <?php echo $MC->config['conversion_rate'] * 100; ?>% conversion rate, these campaigns could potentially earn $<?php echo number_format(($data['summary']['total_unique_clicks'] * $MC->config['conversion_rate']) * $MC->config['lead_value'], 2) ?> in sales.</p>
    </div>
    <div class="half">
      <ul>
        <li>Total Campaigns <div><?php echo number_format( $data['summary']['total_campaigns'], 0 ); ?></div>
        <li>Total Recipients <div><?php echo number_format( $data['summary']['total_recipients'], 0 ); ?></div>
        <li>Trash / Spam <div> <?php echo number_format( $data['summary']['total_trash_spam'], 0 ); ?> (<?php echo round( $data['summary']['avg_trash_spam_rate'], 2 ); ?>%)</div>
        <li>
          <?php if ( $data['summary']['industry']['avg_bounce'] > $data['summary']['avg_bounce_rate'] ): ?>
            <div class="ball bg-2"></div>
          <?php else: ?>
            <div class="ball bg-4"></div>
          <?php endif; ?>
          Bounces <div> <?php echo number_format( $data['summary']['total_bounces'], 0 ); ?> (<?php echo $data['summary']['avg_bounce_rate']; ?>%)</div>
        <li>
          <?php if ( $data['summary']['industry']['open'] < $data['summary']['avg_unique_open_rate'] ): ?>
            <div class="ball bg-2"></div>
          <?php else: ?>
            <div class="ball bg-4"></div>
          <?php endif; ?>
          Unique Opens <div><?php echo number_format( $data['summary']['unique_opens'], 0 ); ?> (<?php if ( $data['summary']['industry']['open'] > $data['summary']['avg_unique_open_rate'] ): ?><span class="color-4"><?php endif; ?><?php echo $data['summary']['avg_unique_open_rate']; ?>%<?php if ( $data['summary']['industry']['open'] > $data['summary']['avg_unique_open_rate'] ): ?></span><?php endif; ?>)</div>
        <li>
          <?php if ( $data['summary']['industry']['click'] < $data['summary']['avg_unique_click_rate'] ): ?>
            <div class="ball bg-2"></div>
          <?php else: ?>
            <div class="ball bg-4"></div>
          <?php endif; ?>
          Unique Clicks <div><?php echo number_format( $data['summary']['total_unique_clicks'], 0 ); ?> (<?php if ( $data['summary']['industry']['click'] > $data['summary']['avg_unique_click_rate'] ): ?><span class="color-4"><?php endif; ?><?php echo $data['summary']['avg_unique_click_rate']; ?>%<?php if ( $data['summary']['industry']['click'] > $data['summary']['avg_unique_click_rate'] ): ?></span><?php endif; ?>)</div>
        <li>
          <?php if ( $data['summary']['industry']['unsub'] > $data['summary']['avg_unsubscribe_rate'] ): ?>
            <div class="ball bg-2"></div>
          <?php else: ?>
            <div class="ball bg-4"></div>
          <?php endif; ?>
          Total Unsubscribes <div><?php echo number_format( $data['summary']['unsubscribes'], 0 ); ?> (<?php if ( $data['summary']['industry']['unsub'] < $data['summary']['avg_unsubscribe_rate'] ): ?><span class="color-4"><?php endif; ?><?php echo $data['summary']['avg_unsubscribe_rate']; ?>%<?php if ( $data['summary']['industry']['unsub'] < $data['summary']['avg_unsubscribe_rate'] ): ?></span><?php endif; ?>)</div>
        <li>
          <?php if ( $data['summary']['industry']['abuse'] > $data['summary']['avg_abuse_complaint_rate'] ): ?>
            <div class="ball bg-2"></div>
          <?php else: ?>
            <div class="ball bg-4"></div>
          <?php endif; ?>
          Total Abuse Compliants <div><?php echo number_format( $data['summary']['abuse_complaints'], 0 ); ?> (<?php if ( $data['summary']['industry']['abuse'] < $data['summary']['avg_abuse_complaint_rate'] ): ?><span class="color-4"><?php endif; ?><?php echo $data['summary']['avg_abuse_complaint_rate']; ?>%<?php if ( $data['summary']['industry']['abuse'] < $data['summary']['avg_abuse_complaint_rate'] ): ?></span><?php endif; ?>)</div>
      </ul>
    </div>
  </section>
  <section>
    <div class="third">
      <div class="block bg-1 small height">
        <h4>Best Open Rate</h4>
        <h5><?php echo $data['summary']['highest_open_rate']['title']; ?> (<?php echo $data['summary']['highest_open_rate']['avg_unique_open_rate']; ?>%)</h5>
        <p><?php echo $data['summary']['highest_open_rate']['subject']; ?></p>
      </div>
    </div>
    <div class="third">
      <div class="block bg-2 small height">
        <h4>Best Click Rate</h4>
        <h5><?php echo $data['summary']['highest_click_rate']['title']; ?> (<?php echo $data['summary']['highest_click_rate']['avg_unique_click_rate']; ?>%)</h5>
        <p><?php echo $data['summary']['highest_click_rate']['subject']; ?></p>
      </div>
    </div>
    <div class="third">
      <div class="block bg-3 small height">
        <h4>Lowest Open Rate</h4>
        <h5><?php echo $data['summary']['lowest_open_rate']['title']; ?> (<?php echo $data['summary']['lowest_open_rate']['avg_unique_open_rate']; ?>%)</h5>
        <p><?php echo $data['summary']['lowest_open_rate']['subject']; ?></p>
      </div>
    </div>
    <div class="third">
      <div class="block bg-4 small height">
        <h4>Highest Abuse Complaints</h4>
        <h5><?php echo $data['summary']['highest_abuse_complaint_rate']['title']; ?> (<?php echo $data['summary']['highest_abuse_complaint_rate']['avg_abuse_complaint_rate']; ?>%)</h5>
        <p><?php echo $data['summary']['highest_abuse_complaint_rate']['subject']; ?></p>
      </div>
    </div>
    <div class="third">
      <div class="block bg-5 small height">
        <h4>Highest Unsubscribe Rate</h4>
        <h5><?php echo $data['summary']['highest_unsubscribe_rate']['title']; ?> (<?php echo $data['summary']['highest_unsubscribe_rate']['avg_unsubscribe_rate']; ?>%)</h5>
        <p><?php echo $data['summary']['highest_unsubscribe_rate']['subject']; ?></p>
      </div>
    </div>
    <div class="third">
      <div class="block bg-6 small height">
        <h4>Highest Spam / Trash Rate</h4>
        <h5><?php echo $data['summary']['highest_trash_spam_rate']['title']; ?> (<?php echo round($data['summary']['highest_trash_spam_rate']['avg_trash_spam_rate'], 2); ?>%)</h5>
        <p><?php echo $data['summary']['highest_trash_spam_rate']['subject']; ?></p>
      </div>
    </div>
  </section>
  <section>
    <div class="block bg-7">
      <h4>Campaign Sales Forecast</h4>
      <p><select id="cf">
        <?php $t = false; foreach( $data['by_campaign'] as $title => $ary ): if ( ! $t ) $t = $title; ?>
          <option value="<?php echo $title; ?>" data-clicks="<?php echo $ary['total_unique_clicks']; ?>"><?php echo $title; ?></option>
        <?php endforeach; ?>
      </select></p>
      <table>
        <thead>
          <tr>
            <th>Unique Clicks</th>
            <th>Potential Sales</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span id="uc"><?php echo $data['by_campaign'][ $t ]['total_unique_clicks']; ?></span></td>
            <td>$<span id="s"><?php echo number_format( $MC->config['lead_value'] * ($data['by_campaign'][ $t ]['total_unique_clicks'] * $MC->config['conversion_rate'] ), 2 ); ?></span></td>
          </tr>
        </tbody>
      </table>
      <p class="small">Sales calculated by each click valued at $<?php echo $MC->config['lead_value']; ?> and a <?php echo $MC->config['conversion_rate'] * 100 ?>% average conversion rate.</p>
    </div>
  </section>
  <section>
    <h2>Recipients, Unique Opens &amp; Spam / Trash</h2>
    <div class="graph">
      <div class="legend small">
        <ul>
          <li><div class="bg-1"></div> Recipients
          <li><div class="bg-3"></div> Unique Opens
          <li><div class="bg-4"></div> Spam / Trash
        </ul>
      </div>
      <canvas id="chart-3"></canvas>
    </div>
  </section>
  <section>
    <h2><?php echo $MC->config['industry'] ?> Industry Rates vs. Campaign Rates</h2>
    <table>
      <thead>
        <tr>
          <th>Statistic</th>
          <th class="right">Industry</th>
          <th class="right">Campaigns</th>
          <th></th>
          <th class="right" width="71">Difference</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Open Rate</strong></td>
          <td class="right"><?php echo $data['summary']['industry']['open'] ?>%</td>
          <td class="right"><?php echo $data['summary']['avg_unique_open_rate']; ?>%</td>
          <td class="right">
            <?php if ( $data['summary']['industry']['open'] < $data['summary']['avg_unique_open_rate'] ): ?>
              <div class="ball bg-2"></div>
            <?php else: ?>
              <div class="ball bg-4"></div>
            <?php endif; ?>
          </td>
          <td class="right <?php
          $diff = $data['summary']['avg_unique_open_rate'] - $data['summary']['industry']['open'];
          if ( $diff > 0 ):
            echo "color-2";
          else:
            echo "color-4";
          endif;
          ?>"><?php echo $diff; ?>%</td>
        </tr>
        <tr>
          <td><strong>Click Rate</strong></td>
          <td class="right"><?php echo $data['summary']['industry']['click'] ?>%</td>
          <td class="right"><?php echo $data['summary']['avg_unique_click_rate']; ?>%</td>
          <td class="right">
            <?php if ( $data['summary']['industry']['click'] < $data['summary']['avg_unique_click_rate'] ): ?>
              <div class="ball bg-2"></div>
            <?php else: ?>
              <div class="ball bg-4"></div>
            <?php endif; ?>
          </td>
          <td class="right <?php
          $diff = $data['summary']['avg_unique_click_rate'] - $data['summary']['industry']['click'];
          if ( $diff > 0 ):
            echo "color-2";
          else:
            echo "color-4";
          endif;
          ?>"><?php echo $diff; ?>%</td>
        </tr>
        <tr>
          <td><strong>Bounce Rate</strong></td>
          <td class="right"><?php echo $data['summary']['industry']['avg_bounce'] ?>%</td>
          <td class="right"><?php echo $data['summary']['avg_bounce_rate']; ?>%</td>
          <td class="right">
            <?php if ( $data['summary']['industry']['avg_bounce'] > $data['summary']['avg_bounce_rate'] ): ?>
              <div class="ball bg-2"></div>
            <?php else: ?>
              <div class="ball bg-4"></div>
            <?php endif; ?>
          </td>
          <td class="right <?php
          $diff = $data['summary']['avg_bounce_rate'] - $data['summary']['industry']['avg_bounce'];
          if ( $diff < 0 ):
            echo "color-2";
          else:
            echo "color-4";
          endif;
          ?>"><?php echo $diff; ?>%</td>
        </tr>
        <tr>
          <td><strong>Abuse Compliant Rate</strong></td>
          <td class="right"><?php echo $data['summary']['industry']['abuse'] ?>%</td>
          <td class="right"><?php echo $data['summary']['avg_abuse_complaint_rate']; ?>%</td>
          <td class="right">
            <?php if ( $data['summary']['industry']['abuse'] > $data['summary']['avg_abuse_complaint_rate'] ): ?>
              <div class="ball bg-2"></div>
            <?php else: ?>
              <div class="ball bg-4"></div>
            <?php endif; ?>
          </td>
          <td class="right <?php
          $diff = $data['summary']['avg_abuse_complaint_rate'] - $data['summary']['industry']['abuse'];
          if ( $diff < 0 ):
            echo "color-2";
          else:
            echo "color-4";
          endif;
          ?>"><?php echo $diff; ?>%</td>
        </tr>
        <tr>
          <td><strong>Unsubscribe Rate</strong></td>
          <td class="right"><?php echo $data['summary']['industry']['unsub'] ?>%</td>
          <td class="right"><?php echo $data['summary']['avg_unsubscribe_rate']; ?>%</td>
          <td class="right">
            <?php if ( $data['summary']['industry']['unsub'] > $data['summary']['avg_unsubscribe_rate'] ): ?>
              <div class="ball bg-2"></div>
            <?php else: ?>
              <div class="ball bg-4"></div>
            <?php endif; ?>
          </td>
          <td class="right <?php
          $diff = $data['summary']['avg_unsubscribe_rate'] - $data['summary']['industry']['unsub'];
          if ( $diff < 0 ):
            echo "color-2";
          else:
            echo "color-4";
          endif;
          ?>"><?php echo $diff; ?>%</td>
        </tr>
      </tbody>
    </table>
  </section>
  <section>
    <h2>Bounces, Unsubscribes &amp; Abuse Compliants</h2>
    <div class="graph">
      <div class="legend small">
        <ul>
          <li><div class="bg-1"></div> Bounces
          <li><div class="bg-3"></div> Unsubscribes
          <li><div class="bg-4"></div> Abuse Compliants
        </ul>
      </div>
      <canvas id="chart-1"></canvas>
    </div>
  </section>
  <section>
    <h2>Open &amp; Click Rate by Weekday</h2>
    <div class="two-third">
      <div class="graph">
        <div class="legend small">
          <ul>
            <li><div class="bg-1"></div> Unique Open Rate
            <li><div class="bg-3"></div> Unique Click Rate
          </ul>
        </div>
        <canvas id="chart-2"></canvas>
      </div>
    </div>
    <div class="third third--space">
      <?php
      $data['by_weekday']            = $MC->sort_array( $data['by_weekday'], 'avg_unique_open_rate' );
      $highest_weekday_open_rate_day = key( $data['by_weekday'] );
      $highest_weekday_open_rate     = $data['by_weekday'][ $highest_weekday_open_rate_day ]['avg_unique_open_rate'];

      $data['by_weekday']            = $MC->sort_array( $data['by_weekday'], 'avg_unique_click_rate' );
      $highest_unique_click_rate_day = key( $data['by_weekday'] );
      $highest_unique_click_rate     = $data['by_weekday'][ $highest_weekday_open_rate_day ]['avg_unique_click_rate'];
      ?>
      <p>Based on the past <?php echo number_format( $data['summary']['total_campaigns'], 0 ); ?> campaigns sent, <?php echo $highest_weekday_open_rate_day; ?> has the highest open rate at <?php echo $highest_weekday_open_rate; ?>% <?php if( $highest_weekday_open_rate == $highest_unique_click_rate_day ): ?> and click rate at <?php echo $data['by_weekday'][ $highest_weekday_open_rate ]['avg_unique_click_rate']; ?>%.<?php else: ?> and <?php echo $highest_unique_click_rate_day; ?> has the highest click rate at <?php echo $data['by_weekday'][ $highest_unique_click_rate_day ]['avg_unique_click_rate']; ?>%.<?php endif; ?></p>
    </div>
  </section>
  <section>
    <div class="half">
      <h2>Highest Open Rate by Subject</h2>
      <ol>
        <?php
        $data['by_subject'] = $MC->sort_array( $data['by_subject'], 'avg_unique_open_rate' );
        $cnt = 0;
        foreach( $data['by_subject'] as $subject => $ary ): $cnt++; if ( $cnt > 10 ) break; ?>
          <li><b><?php echo $subject; ?></b> (<?php echo $ary['avg_unique_open_rate']; ?>%)
        <?php endforeach; ?>
      </ol>
    </div>
    <div class="half">
      <h2>Highest Open Rate by Campaign</h2>
      <ol>
        <?php
        $data['by_campaign'] = $MC->sort_array( $data['by_campaign'], 'avg_unique_open_rate' );
        $cnt = 0;
        foreach( $data['by_campaign'] as $title => $ary ): $cnt++; if ( $cnt > 10 ) break; ?>
          <li><b><?php echo $title; ?></b> (<?php echo $ary['avg_unique_open_rate']; ?>%)
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
</div>
<script src="assets/build/js/jquery-2.1.1.min.js"></script>
<script src="assets/build/js/Chart.min.js"></script>
<script>
$( function() {
    var color1     = "rgba(44, 154, 183, 0.2)",
        color1_alt = "rgba(44, 154, 183, 1)",
        color3     = "rgba(254, 190, 18, 0.2)",
        color3_alt = "rgba(254, 190, 18, 1)",
        color4     = "rgba(219, 58, 27, 0.2)",
        color4_alt = "rgba(219, 58, 27, 1)";

    $( "#cf" ).change( function() {
        var val = $( "option:selected", $( this ) ),
            clicks = val.data( "clicks" ),
            v = (clicks * <?php echo $MC->config['conversion_rate']; ?>) * <?php echo $MC->config['lead_value']; ?>;

        $( "#uc" ).text( clicks );
        $( "#s" ).text( v.toFixed(2) );
    });

    $( "#update" ).click( function( e ) {
        e.preventDefault();

        var limit = $( "#limit" ).val(),
            industry = $( "#industry" ).val();

        window.location.href = "?limit=" + limit + "&industry=" + industry;
    });

    <?php if ( isset( $_REQUEST['print'] ) && $_REQUEST['print'] ): ?>
      setTimeout(function() {
        window.print();
      }, 1000);
    <?php endif; ?>

    var chart_1 = new Chart( document.getElementById( "chart-1" ).getContext( "2d" ) ).Line({
        labels: [
            <?php foreach( $data['by_date'] as $time => $ary ): ?>
                "<?php echo date( 'M j', $time ); ?>",
            <?php endforeach; ?>
        ],
        datasets: [
            {
                label: "Total Bounces",
                fillColor: color1,
                strokeColor: color1_alt,
                pointColor: color1_alt,
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: color1_alt,
                data: [
                    <?php foreach( $data['by_date'] as $time => $ary ): ?>
                    <?php echo $ary['total_bounces']; ?>,
                    <?php endforeach; ?>
                ]
            },
            {
                label: "Total Unsubscribes",
                fillColor: color3,
                strokeColor: color3_alt,
                pointColor: color3_alt,
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: color3_alt,
                data: [
                    <?php foreach( $data['by_date'] as $time => $ary ): ?>
                    <?php echo $ary['total_unsubscribes']; ?>,
                    <?php endforeach; ?>
                ]
            },
            {
                label: "Total Abuse Compliants",
                fillColor: color4,
                strokeColor: color4_alt,
                pointColor: color4_alt,
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: color4_alt,
                data: [
                    <?php foreach( $data['by_date'] as $time => $ary ): ?>
                    <?php echo $ary['total_abuse_compliants']; ?>,
                    <?php endforeach; ?>
                ]
            }
        ]
    }, {
        responsive: true
    });

    var chart_2 = new Chart( document.getElementById( "chart-2" ).getContext( "2d" ) ).Bar({
        labels: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
        datasets: [
            {
                label: "Open Rate",
                fillColor: color1,
                strokeColor: color1_alt,
                highlightFill: color1_alt,
                highlightStroke: color1_alt,
                data: [
                    <?php if ( isset( $data['by_weekday']['Monday'] ) ) echo $data['by_weekday']['Monday']['avg_unique_open_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Tuesday'] ) ) echo $data['by_weekday']['Tuesday']['avg_unique_open_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Wednesday'] ) ) echo $data['by_weekday']['Wednesday']['avg_unique_open_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Thursday'] ) ) echo $data['by_weekday']['Thursday']['avg_unique_open_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Friday'] ) ) echo $data['by_weekday']['Friday']['avg_unique_open_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Saturday'] ) ) echo $data['by_weekday']['Saturday']['avg_unique_open_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Sunday'] ) ) echo $data['by_weekday']['Sunday']['avg_unique_open_rate'] . ','; ?>
                ]
            },
            {
                label: "Click Rate",
                fillColor: color3,
                strokeColor: color3_alt,
                highlightFill: color3_alt,
                highlightStroke: color3_alt,
                data: [
                    <?php if ( isset( $data['by_weekday']['Monday'] ) ) echo $data['by_weekday']['Monday']['avg_unique_click_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Tuesday'] ) ) echo $data['by_weekday']['Tuesday']['avg_unique_click_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Wednesday'] ) ) echo $data['by_weekday']['Wednesday']['avg_unique_click_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Thursday'] ) ) echo $data['by_weekday']['Thursday']['avg_unique_click_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Friday'] ) ) echo $data['by_weekday']['Friday']['avg_unique_click_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Saturday'] ) ) echo $data['by_weekday']['Saturday']['avg_unique_click_rate'] . ','; ?>
                    <?php if ( isset( $data['by_weekday']['Sunday'] ) ) echo $data['by_weekday']['Sunday']['avg_unique_click_rate'] . ','; ?>
                ]
            },
        ]
    }, {
        responsive: true
    });

    var chart_3 = new Chart( document.getElementById( "chart-3" ).getContext( "2d" ) ).Line({
        labels: [
            <?php foreach( $data['by_date'] as $time => $ary ): ?>
                "<?php echo date( 'M j', $time ); ?>",
            <?php endforeach; ?>
        ],
        datasets: [
            {
                label: "Total Recipients",
                fillColor: color1,
                strokeColor: color1_alt,
                pointColor: color1_alt,
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: color1_alt,
                data: [
                    <?php foreach( $data['by_date'] as $time => $ary ): ?>
                    <?php echo $ary['total_recipients']; ?>,
                    <?php endforeach; ?>
                ]
            },
            {
                label: "Unique Opens",
                fillColor: color3,
                strokeColor: color3_alt,
                pointColor: color3_alt,
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: color3_alt,
                data: [
                    <?php foreach( $data['by_date'] as $time => $ary ): ?>
                    <?php echo $ary['total_unique_opens']; ?>,
                    <?php endforeach; ?>
                ]
            },
            {
                label: "Spam / Trash",
                fillColor: color4,
                strokeColor: color4_alt,
                pointColor: color4_alt,
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: color4_alt,
                data: [
                    <?php foreach( $data['by_date'] as $time => $ary ): ?>
                    <?php echo $ary['total_trash_spam']; ?>,
                    <?php endforeach; ?>
                ]
            }
        ]
    }, {
      responsive: true
    });
});
</script>
</body>
</html>

