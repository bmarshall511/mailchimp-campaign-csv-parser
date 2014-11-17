MailChimp Campaign CSV Parser
=============================

##Introduction

The MailChimp Campaign CSV Parser is a PHP library to take a downloaded MailChimp Campaign CSV export and parse it in a easy-to-use array. Includes industry rates to compare statistics with.

##Usage

It's pretty straightforward to start using the library. All you need to get started is the a MailChimp Campaign report that can be downloaded in your MailChimp account under 'Campaigns'.

    /**
     * Include the MailChimp Report Generator class.
     */
    require_once( 'lib/MailChimp_Campaign_CSV_Parser.class.php' );

    // Initialize the MailChimp Report Generator class.
    $MC = new MailChimp_Campaign_CSV_Parser;

    // Set the name of the CSV file to use.
    $MC->config['mailchimp_campaign_export_file'] = 'downloaded-csv-file.csv';

    // Select the industry that relates to the email campaigns.
    $MC->config['industry'] = 'Media and Publishing';

    // Get and parse the first 500 campaigns.
    $MC->parse_campaign_export( 500 );
    $data = $MC->array;
