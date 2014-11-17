<?php

/**
 * Define the root directory of the app.
 */
if( ! defined( 'APP_ROOT' ) ) {
    define( 'APP_ROOT', dirname( __FILE__ ) );
}

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
 * - Public Relations
 * - Real Estate
 * - Recruitment and Staffing
 */
$MC->config['industry'] = 'Media and Publishing';

// Get and parse the first 500 campaigns.
$MC->parse_campaign_export( 500 );
$data = $MC->array;

