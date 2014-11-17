<?php
/**
 * Include the SimpleExcel library to parse CSV files.
 */
use SimpleExcel\SimpleExcel;
require_once( APP_ROOT . '/lib/SimpleExcel/SimpleExcel.php' );

/**
 * MailChimp Campaign CSV Parser
 *
 * This class contains the functionality parse a MailChimp Campaign CSV export
 * file.
 *
 * @author Ben Marshall <me@benmarshall.me>
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL2
 * @version Release: 1.0.0
 * @link http://www.benmarshall.me/mailchimp-campaign-csv-parser-php-library/
 * @see MailChimp API (http://apidocs.mailchimp.com/api/2.0/)
 * @since Class available since Release 1.0.0
 */

class MailChimp_Campaign_CSV_Parser
{
    /**
     * The parsed array
     *
     * @var array
     * @access public
     */
    var $array = array();

    /**
     * Configuration variables
     *
     * @var array
     * @access public
     */
    var $config = array(
        'lead_value'                     => 0.05,  // The amount each potential lead is worth.
        'round_to'                       => 3,     // The number of decimals to round values to.
        'conversion_rate'                => 0.08,  // The average conversion rate for each lead.
        'mailchimp_campaign_export_file' => false, // Filename of the MailChimp Campaign export file.
        'industry'                       => false, // Industry for the sent campaigns.
    );

    /**
     * Industry rates info
     *
     * Industry rates gathered from MailChimp. Last updated 11/12/2014.
     *
     * @var array
     * @access public
     * @see http://mailchimp.com/resources/research/email-marketing-benchmarks/
     */
    var $industry = array(
        'Agriculture and Food Services'          => array(
            'open'            => 26.06,
            'click'           => 3.94,
            'soft_bounce'     => 1.0,
            'hard_bounce'     => 0.86,
            'avg_bounce'      => 0.93,
            'abuse'           => 0.04,
            'unsub'           => 0.29
        ),
        'Architecture and Construction'          => array(
            'open'            => 25.38,
            'click'           => 3.86,
            'soft_bounce'     => 2.32,
            'hard_bounce'     => 1.98,
            'avg_bounce'      => 2.15,
            'abuse'           => 0.04,
            'unsub'           => 0.35
        ),
        'Arts and Artists'                       => array(
            'open'            => 27.97,
            'click'           => 3.28,
            'soft_bounce'     => 1.17,
            'hard_bounce'     => 0.99,
            'avg_bounce'      => 1.08,
            'abuse'           => 0.04,
            'unsub'           => 0.29
        ),
        'Beauty and Personal Care'               => array(
            'open'            => 20.72,
            'click'           => 2.82,
            'soft_bounce'     => 0.82,
            'hard_bounce'     => 0.95,
            'avg_bounce'      => 0.89,
            'abuse'           => 0.06,
            'unsub'           => 0.35
        ),
        'Business and Finance'                   => array(
            'open'            => 20.68,
            'click'           => 3.14,
            'soft_bounce'     => 1.18,
            'hard_bounce'     => 1.09,
            'avg_bounce'      => 1.14,
            'abuse'           => 0.04,
            'unsub'           => 0.24
        ),
        'Computers and Electronics'              => array(
            'open'            => 24.65,
            'click'           => 2.83,
            'soft_bounce'     => 1.75,
            'hard_bounce'     => 1.51,
            'avg_bounce'      => 3.26,
            'abuse'           => 0.04,
            'unsub'           => 0.31
        ),
        'Construction'                           => array(
            'open'            => 22.67,
            'click'           => 2.40,
            'soft_bounce'     => 2.82,
            'hard_bounce'     => 2.62,
            'avg_bounce'      => 2.72,
            'abuse'           => 0.06,
            'unsub'           => 0.48
        ),
        'Consulting'                             => array(
            'open'            => 18.78,
            'click'           => 2.57,
            'soft_bounce'     => 1.76,
            'hard_bounce'     => 1.57,
            'avg_bounce'      => 1.67,
            'abuse'           => 0.04,
            'unsub'           => 0.29
        ),
        'Creative Services/Agency'               => array(
            'open'            => 23.65,
            'click'           => 3.36,
            'soft_bounce'     => 1.89,
            'hard_bounce'     => 1.75,
            'avg_bounce'      => 1.82,
            'abuse'           => 0.04,
            'unsub'           => 0.37
        ),
        'Daily Deals/E-Coupons'                  => array(
            'open'            => 13.2,
            'click'           => 1.88,
            'soft_bounce'     => 0.17,
            'hard_bounce'     => 0.15,
            'avg_bounce'      => 0.16,
            'abuse'           => 0.02,
            'unsub'           => 0.09
        ),
        'eCommerce'                              => array(
            'open'            => 17.35,
            'click'           => 3.0,
            'soft_bounce'     => 0.48,
            'hard_bounce'     => 0.43,
            'avg_bounce'      => 0.46,
            'abuse'           => 0.04,
            'unsub'           => 0.02
        ),
        'Education and Training'                 => array(
            'open'            => 22.49,
            'click'           => 3.42,
            'soft_bounce'     => 1.04,
            'hard_bounce'     => 1.01,
            'avg_bounce'      => 1.03,
            'abuse'           => 0.03,
            'unsub'           => 0.21
        ),
        'Entertainment and Events'               => array(
            'open'            => 20.93,
            'click'           => 2.51,
            'soft_bounce'     => 0.88,
            'hard_bounce'     => 0.85,
            'avg_bounce'      => 1.73,
            'abuse'           => 0.04,
            'unsub'           => 0.27
        ),
        'Gambling'                               => array(
            'open'            => 18.72,
            'click'           => 2.04,
            'soft_bounce'     => 1.02,
            'hard_bounce'     => 1.4,
            'avg_bounce'      => 2.42,
            'abuse'           => 0.06,
            'unsub'           => 0.2
        ),
        'Games'                                  => array(
            'open'            => 20.31,
            'click'           => 4.07,
            'soft_bounce'     => 0.87,
            'hard_bounce'     => 1.27,
            'avg_bounce'      => 1.07,
            'abuse'           => 0.06,
            'unsub'           => 0.24
        ),
        'Government'                             => array(
            'open'            => 25.69,
            'click'           => 3.64,
            'soft_bounce'     => 0.83,
            'hard_bounce'     => 0.74,
            'avg_bounce'      => 0.79,
            'abuse'           => 0.03,
            'unsub'           => 0.14
        ),
        'Health and Fitness'                     => array(
            'open'            => 24.27,
            'click'           => 3.64,
            'soft_bounce'     => 0.83,
            'hard_bounce'     => 0.74,
            'avg_bounce'      => 0.79,
            'abuse'           => 0.03,
            'unsub'           => 0.14
        ),
        'Hobbies'                                => array(
            'open'            => 30.71,
            'click'           => 6.65,
            'soft_bounce'     => 0.56,
            'hard_bounce'     => 0.54,
            'avg_bounce'      => 0.55,
            'abuse'           => 0.04,
            'unsub'           => 0.22
        ),
        'Home and Garden'                        => array(
            'open'            => 26.44,
            'click'           => 4.40,
            'soft_bounce'     => 1.04,
            'hard_bounce'     => 0.82,
            'avg_bounce'      => 0.93,
            'abuse'           => 0.06,
            'unsub'           => 0.39
        ),
        'Insurance'                              => array(
            'open'            => 19.71,
            'click'           => 2.37,
            'soft_bounce'     => 1.15,
            'hard_bounce'     => 1.19,
            'avg_bounce'      => 1.17,
            'abuse'           => 0.04,
            'unsub'           => 0.21
        ),
        'Legal'                                  => array(
            'open'            => 21.23,
            'click'           => 3.25,
            'soft_bounce'     => 1.11,
            'hard_bounce'     => 0.99,
            'avg_bounce'      => 1.05,
            'abuse'           => 0.03,
            'unsub'           => 0.19
        ),
        'Manufacturing'                          => array(
            'open'            => 23.78,
            'click'           => 3.14,
            'soft_bounce'     => 2.48,
            'hard_bounce'     => 1.9,
            'avg_bounce'      => 2.19,
            'abuse'           => 0.05,
            'unsub'           => 0.39
        ),
        'Marketing and Advertising'              => array(
            'open'            => 18.81,
            'click'           => 2.44,
            'soft_bounce'     => 1.3,
            'hard_bounce'     => 1.22,
            'avg_bounce'      => 1.26,
            'abuse'           => 0.04,
            'unsub'           => 0.29
        ),
        'Media and Publishing'                   => array(
            'open'            => 22.93,
            'click'           => 5.14,
            'soft_bounce'     => 0.47,
            'hard_bounce'     => 0.33,
            'avg_bounce'      => 0.40,
            'abuse'           => 0.02,
            'unsub'           => 0.12
        ),
        'Medical, Dental, and Healthcare'        => array(
            'open'            => 22.76,
            'click'           => 3.07,
            'soft_bounce'     => 1.25,
            'hard_bounce'     => 1.37,
            'avg_bounce'      => 2.62,
            'abuse'           => 0.06,
            'unsub'           => 0.29
        ),
        'Mobile'                                 => array(
            'open'            => 23.32,
            'click'           => 3.16,
            'soft_bounce'     => 1.23,
            'hard_bounce'     => 1.38,
            'avg_bounce'      => 1.92,
            'abuse'           => 0.05,
            'unsub'           => 0.42
        ),
        'Music and Musicians'                    => array(
            'open'            => 22.49,
            'click'           => 3.03,
            'soft_bounce'     => 1.08,
            'hard_bounce'     => 0.96,
            'avg_bounce'      => 1.02,
            'abuse'           => 0.04,
            'unsub'           => 0.31
        ),
        'Non-Profit'                             => array(
            'open'            => 25.12,
            'click'           => 3.25,
            'soft_bounce'     => 0.79,
            'hard_bounce'     => 0.71,
            'avg_bounce'      => 0.75,
            'abuse'           => 0.03,
            'unsub'           => 0.19
        ),
        'Other'                                  => array(
            'open'            => 22.58,
            'click'           => 3.18,
            'soft_bounce'     => 1.37,
            'hard_bounce'     => 1.25,
            'avg_bounce'      => 1.31,
            'abuse'           => 0.04,
            'unsub'           => 0.28
        ),
        'Pharmaceuticals'                        => array(
            'open'            => 17.79,
            'click'           => 2.62,
            'soft_bounce'     => 1.27,
            'hard_bounce'     => 1.46,
            'avg_bounce'      => 1.37,
            'abuse'           => 0.04,
            'unsub'           => 0.24
        ),
        'Photo and Video'                        => array(
            'open'            => 27.03,
            'click'           => 4.28,
            'soft_bounce'     => 1.25,
            'hard_bounce'     => 1.22,
            'avg_bounce'      => 1.24,
            'abuse'           => 0.05,
            'unsub'           => 0.41
        ),
        'Politics'                               => array(
            'open'            => 22.6,
            'click'           => 2.74,
            'soft_bounce'     => 0.79,
            'hard_bounce'     => 0.78,
            'avg_bounce'      => 0.79,
            'abuse'           => 0.05,
            'unsub'           => 0.23
        ),
        'Professional Services'                  => array(
            'open'            => 21.72,
            'click'           => 3.21,
            'soft_bounce'     => 1.69,
            'hard_bounce'     => 1.51,
            'avg_bounce'      => 1.6,
            'abuse'           => 0.04,
            'unsub'           => 0.34
        ),
        'Public Relations'                        => array(
            'open'            => 19.98,
            'click'           => 2.15,
            'soft_bounce'     => 1.26,
            'hard_bounce'     => 1.20,
            'avg_bounce'      => 1.23,
            'abuse'           => 0.03,
            'unsub'           => 0.25
        ),
        'Real Estate'                            => array(
            'open'            => 22.12,
            'click'           => 2.68,
            'soft_bounce'     => 1.29,
            'hard_bounce'     => 1.33,
            'avg_bounce'      => 1.31,
            'abuse'           => 0.07,
            'unsub'           => 0.34
        ),
        'Recruitment and Staffing'               => array(
            'open'            => 20.77,
            'click'           => 3.17,
            'soft_bounce'     => 1.10,
            'hard_bounce'     => 1.24,
            'avg_bounce'      => 1.17,
            'abuse'           => 0.04,
            'unsub'           => 0.33
        ),
        'Religion'                               => array(
            'open'            => 22.27,
            'click'           => 3.5,
            'soft_bounce'     => 0.34,
            'hard_bounce'     => 0.36,
            'avg_bounce'      => 0.35,
            'abuse'           => 0.03,
            'unsub'           => 0.13
        ),
        'Restaurant'                             => array(
            'open'            => 24.61,
            'click'           => 1.6,
            'soft_bounce'     => 0.52,
            'hard_bounce'     => 0.41,
            'avg_bounce'      => 0.47,
            'abuse'           => 0.04,
            'unsub'           => 0.29
        ),
        'Restaurant and Venue'                   => array(
            'open'            => 22.56,
            'click'           => 1.58,
            'soft_bounce'     => 1.02,
            'hard_bounce'     => 0.92,
            'avg_bounce'      => 0.97,
            'abuse'           => 0.04,
            'unsub'           => 0.38
        ),
        'Retail'                                 => array(
            'open'            => 23.16,
            'click'           => 3.26,
            'soft_bounce'     => 0.66,
            'hard_bounce'     => 0.6,
            'avg_bounce'      => 0.63,
            'abuse'           => 0.04,
            'unsub'           => 0.03
        ),
        'Social Networks and Online Communities' => array(
            'open'            => 21.98,
            'click'           => 3.89,
            'soft_bounce'     => 0.64,
            'hard_bounce'     => 0.62,
            'avg_bounce'      => 0.63,
            'abuse'           => 0.03,
            'unsub'           => 0.24
        ),
        'Software and Web App'                   => array(
            'open'            => 21.86,
            'click'           => 3.26,
            'soft_bounce'     => 1.64,
            'hard_bounce'     => 1.52,
            'avg_bounce'      => 1.58,
            'abuse'           => 0.04,
            'unsub'           => 0.4
        ),
        'Sports'                                 => array(
            'open'            => 26.57,
            'click'           => 3.91,
            'soft_bounce'     => 0.92,
            'hard_bounce'     => 0.87,
            'avg_bounce'      => 0.9,
            'abuse'           => 0.04,
            'unsub'           => 0.28
        ),
        'Telecommunications'                     => array(
            'open'            => 19.77,
            'click'           => 2.38,
            'soft_bounce'     => 1.84,
            'hard_bounce'     => 1.64,
            'avg_bounce'      => 1.74,
            'abuse'           => 0.04,
            'unsub'           => 0.25
        ),
        'Travel and Transportation'              => array(
            'open'            => 20,
            'click'           => 2.77,
            'soft_bounce'     => 1.12,
            'hard_bounce'     => 0.91,
            'avg_bounce'      => 1.02,
            'abuse'           => 0.04,
            'unsub'           => 0.24
        ),
        'Vitamin Supplements'                    => array(
            'open'            => 18.12,
            'click'           => 2.44,
            'soft_bounce'     => 0.62,
            'hard_bounce'     => 0.6,
            'avg_bounce'      => 0.61,
            'abuse'           => 0.06,
            'unsub'           => 0.27
        )
    );

    /**
     * Temp array to hold values.
     *
     * @var array
     * @access public
     */
    var $temp = array();

    /**
     * Calculates the number of days between two dates
     *
     * @param int $date1 unix timestamp of the beginning date.
     * @param int $date2 unix timestamp of the end date.
     *
     * @return int the number of days between the two dates.
     *
     * @access public
     * @static
     * @since Method available since Release 1.0.0
     *
     */
    static public function get_num_days( $date1, $date2 )
    {
        $timestamp_difference = $date2 - $date1;

        return floor( $timestamp_difference / ( 60 * 60 * 24 ) );
    }

    /**
     * Calculates the percentage of two numbers
     *
     * @param int $num1 first number.
     * @param int $num2 second number.
     *
     * @return int the percentage of the two numbers.
     *
     * @access public
     * @since Method available since Release 1.0.0
     *
     */
    public function get_percent( $num1, $num2 ) {
        return ( $num2 / $num1 ) * 100;
    }

    /**
     * Calculates the average of two numbers
     *
     * @param array $numbers an array of numbers to average.
     *
     * @return int the average of the two numbers.
     *
     * @access public
     * @since Method available since Release 1.0.0
     *
     */
    public function get_average( $numbers = array() ) {
        $average = 0;

        foreach ( $numbers as $key => $value ) {
          $average += $value;
        }

        $average = $average / count( $numbers );

        return round( $average, $this->config['round_to'] );
    }

    /**
     * Parses a Excel file
     *
     * Uses the SimpleExcel library to parse a excel file.
     *
     * @param string $type      the type of file. Accepts csv.
     * @param string $file_path the path to the file to parse.
     *
     * @return array an array of the parsed Excel file.
     *
     * @access public
     * @since Method available since Release 1.0.0
     */
    public function SimpleExcel( $type = 'csv', $file_path )
    {
        $excel = new SimpleExcel( $type );
        $excel->parser->loadFile( $file_path );
        $data = $excel->parser->getField();

        return $data;
    }

    /**
     * Parses the specified MailChimp Campaign export file.
     *
     * The mailchimp_campaign_export_file config variable must be set to return
     * anything.
     *
     * @param int $limit Number of records to parse.
     *
     * @return array a parsed array.
     *
     * @access public
     * @since Method available since Release 1.0.0
     */
    public function parse_campaign_export( $limit = 1000 )
    {
        // Check to ensure a file has been specified.
        if ( ! $this->config['mailchimp_campaign_export_file'] ) return false;

        $array = $this->SimpleExcel( 'csv', $this->config['mailchimp_campaign_export_file'] );
        $this->parse_campaign_export_array( $array, $limit );
    }

    /**
     * Adds/updates the parsed array.
     *
     * @param array $array the current parsed array.
     * @param string $data_group the main key of the array to alter.
     * @param string $data_point the secondary key of the array to alter.
     * @param string $level the thrid key of the array to alter.
     * @param string $data the data to alter the array with.
     * @param boolean $add determines if the data should be added or replaced. Default is true.
     *
     * @return array a parsed array.
     *
     * @access public
     * @since Method available since Release 1.0.0
     */
    public function add_to_array( $level, $data, $add = true ) {
        if ( ! isset( $this->array[ $this->temp['group'] ][ $this->temp['data_point'] ] ) )
            $this->array[ $this->temp['group'] ][ $this->temp['data_point'] ] = array();

        if ( ! isset( $this->array[ $this->temp['group'] ][ $this->temp['data_point'] ][ $level ] ) )
            $this->array[ $this->temp['group'] ][ $this->temp['data_point'] ][ $level ] = 0;

        if ( $add ) {
            $this->array[ $this->temp['group'] ][ $this->temp['data_point'] ][ $level ] += $data;
        } else {
            $this->array[ $this->temp['group'] ][ $this->temp['data_point'] ][ $level ] = $data;
        }
    }

    /**
     * Adds/updates the parsed array.
     *
     * @param array $array the current parsed array.
     * @param string $group the main key of the array to alter.
     * @param string $data_point the secondary key of the array to alter.
     * @param string $values data to alter the array with.
     *
     * @return array a parsed array.
     *
     * @access public
     * @since Method available since Release 1.0.0
     */
    public function add_to_group_array( $group, $data_point, $values ) {
        $this->temp['group'] = $group;
        $this->temp['data_point'] = $data_point;

        $this->add_to_array( 'campaigns', 1 );
        $this->add_to_array( 'recipients', $values['recipients'] );
        $this->add_to_array( 'successful_deliveries', $values['successful_deliveries'] );
        $this->add_to_array( 'soft_bounces', $values['soft_bounces'] );
        $this->add_to_array( 'hard_bounces', $values['hard_bounces'] );
        $this->add_to_array( 'total_bounces', $values['total_bounces'] );
        $this->add_to_array( 'times_forwarded', $values['times_forwarded'] );
        $this->add_to_array( 'unique_opens', $values['unique_opens'] );
        $this->add_to_array( 'opens', $values['opens'] );
        $this->add_to_array( 'unique_clicks', $values['unique_clicks'] );
        $this->add_to_array( 'clicks', $values['clicks'] );
        $this->add_to_array( 'total_unsubscribes', $values['unsubscribes'] );
        $this->add_to_array( 'total_abuse_compliants', $values['abuse_complaints'] );
        $this->add_to_array( 'trash_spam', $values['trash_spam'] );

        $this->add_to_array(
            'bounce_rate',
            $this->get_average( array(
                $values['bounce_rate'],
                $this->array['summary']['bounce_rate']
            )), false
        );

        $this->add_to_array(
            'trash_spam_rate',
            $this->get_average( array(
                $values['trash_spam_rate'],
                $this->array['summary']['trash_spam_rate']
            )), false
        );

        $this->add_to_array(
            'abuse_complaint_rate',
            $this->get_average( array(
                $values['abuse_complaint_rate'],
                $this->array['summary']['abuse_complaint_rate']
            )), false
        );

        $this->add_to_array(
          'unique_open_rate',
          $this->get_average( array(
              $values['open_rate'],
              $this->array['summary']['unique_open_rate']
          )), false
        );

        $this->add_to_array(
            'unique_click_rate',
            $this->get_average( array(
                $values['click_rate'],
                $this->array['summary']['unique_click_rate'] ) ), false );

        $this->add_to_array( 'unsubscribe_rate', $this->get_average( array(
            $values['unsubscribe_rate'],
            $this->array['summary']['unsubscribe_rate'] ) ), false );

        if ( $group === 'by_campaign' ) {
          $this->add_to_array( 'title', $values['title'], false );
          $this->add_to_array( 'subject', $values['subject'], false );
        }
    }

    /**
     * Sorts an array.
     *
     * @param array $array the current parsed array.
     * @param string $key the key of the array to sort from.
     *
     * @return array the sorted array.
     *
     * @access public
     * @since Method available since Release 1.0.0
     */
    public function sort_array( $array, $key ) {
      $ary = array();
      foreach ( $array as $k => $a ) {
        $sort[ $k ] = $a[ $key ];
      }
      array_multisort( $sort, SORT_DESC, $array );

      return $array;
    }

    /**
     * Parses the parsed CSV array.
     *
     * @param array $raw   MailChimp Export parsed CSV array.
     * @param int   $limit number of records to parse.
     *
     * @return array a parsed array.
     *
     * @access public
     * @since Method available since Release 1.0.0
     */
    public function parse_campaign_export_array( $raw, $limit )
    {
        // Create the array
        $this->array = array(
            'summary'    => array(
                'abuse_complaint_rate'                  => 0,
                'abuse_complaints'                      => 0,
                'bounce_rate'                           => 0,
                'campaigns'                             => 0,
                'clicks'                                => 0,
                'end_date'                              => false,
                'forwarded_opens'                       => 0,
                'hard_bounces'                          => 0,
                'highest_abuse_complaint_rate'          => array(),
                'highest_click_rate'                    => array(),
                'highest_open_rate'                     => array(),
                'industry'                              => $this->industry[ $this->config['industry'] ],
                'lowest_abuse_complaint_rate'           => array(),
                'lowest_click_rate'                     => array(),
                'lowest_open_rate'                      => array(),
                'opens'                                 => 0,
                'recipients'                            => 0,
                'soft_bounces'                          => 0,
                'start_date'                            => false,
                'successful_deliveries'                 => 0,
                'times_forwarded'                       => 0,
                'total_bounces'                         => 0,
                'trash_spam'                            => 0,
                'trash_spam_rate'                       => 0,
                'unique_click_rate'                     => 0,
                'unique_clicks'                         => 0,
                'unique_open_rate'                      => 0,
                'unique_opens'                          => 0,
                'unsubscribe_rate'                      => 0,
                'unsubscribes'                          => 0,
                'raw'                                   => $raw,
            ),
            'by_date'     => array(),
            'by_campaign' => array(),
            'by_subject'  => array(),
            'by_weekday'  => array(),
        );

        // Build the array
        $cnt = 0;
        foreach ( $raw as $key => $ary ) {
          // Ignore the first record to remove table headers
          if ( ! $key ) continue;

          $cnt++;
          if ( $cnt > $limit ) break;

          // Column data points
          $values = array(
              'title'                 => $ary[0],
              'subject'               => $ary[1],
              'datetime'              => $ary[3],
              'timestamp'             => strtotime( $ary[3] ),
              'date'                  => date( 'Y-m-d', strtotime( $ary[3] ) ),
              'date_timestamp'        => strtotime( date( 'Y-m-d', strtotime( $ary[3] ) ) ),
              'weekday'               => $ary[4],
              'recipients'            => $ary[5],
              'successful_deliveries' => $ary[6],
              'soft_bounces'          => $ary[7],
              'hard_bounces'          => $ary[8],
              'total_bounces'         => $ary[9],
              'times_forwarded'       => $ary[10],
              'forwarded_opens'       => $ary[11],
              'unique_opens'          => $ary[12],
              'open_rate'             => str_replace( "%", "", $ary[13] ),
              'opens'                 => $ary[14],
              'unique_clicks'         => $ary[15],
              'click_rate'            => str_replace( "%", "", $ary[16] ),
              'clicks'                => $ary[17],
              'unsubscribes'          => $ary[18],
              'abuse_complaints'      => $ary[19],
              'unique_id'             => $ary[22],
              'trash_spam'            => $ary[5] - $ary[9] - $ary[14],
              'bounce_rate'           => $this->get_percent( $ary[5], $ary[9] ),
              'trash_spam_rate'       => $this->get_percent( $ary[5], ( $ary[5] - $ary[9] - $ary[14] ) ),
              'unsubscribe_rate'      => $this->get_percent( $ary[5], $ary[18] ),
              'abuse_complaint_rate'  => $this->get_percent( $ary[5], $ary[19] ),
          );

          // Summary
          $this->array['summary']['campaigns']++;
          $this->array['summary']['clicks']                += $values['clicks'];
          $this->array['summary']['recipients']            += $values['recipients'];
          $this->array['summary']['successful_deliveries'] += $values['successful_deliveries'];
          $this->array['summary']['soft_bounces']          += $values['soft_bounces'];
          $this->array['summary']['unique_clicks']         += $values['unique_clicks'];
          $this->array['summary']['trash_spam']            += $values['trash_spam'];
          $this->array['summary']['total_bounces']         += $values['total_bounces'];
          $this->array['summary']['opens']                 += $values['opens'];
          $this->array['summary']['unique_opens']          += $values['unique_opens'];
          $this->array['summary']['unsubscribes']          += $values['unsubscribes'];
          $this->array['summary']['abuse_complaints']      += $values['abuse_complaints'];
          $this->array['summary']['forwarded_opens']       += $values['forwarded_opens'];
          $this->array['summary']['hard_bounces']          += $values['hard_bounces'];

          // Average bounce rate
          $this->array['summary']['bounce_rate'] = ( ! $this->array['summary']['bounce_rate'] ) ?
                                                   $this->get_average( array( $values['bounce_rate'], $this->array['summary']['bounce_rate'] ) ) :
                                                   $this->array['summary']['bounce_rate'];

          // Average trash/spam rate
          $this->array['summary']['trash_spam_rate'] = ( ! $this->array['summary']['trash_spam_rate'] ) ?
                                                       $this->get_average( array( $values['trash_spam_rate'], $this->array['summary']['trash_spam_rate'] ) ) :
                                                       $this->array['summary']['trash_spam_rate'];

          // Average abuse complaint rate
          $this->array['summary']['abuse_complaint_rate'] = ( ! $this->array['summary']['abuse_complaint_rate'] ) ?
                                                            $this->get_average( array( $values['abuse_complaint_rate'], $this->array['summary']['abuse_complaint_rate'] ) ) :
                                                            $this->array['summary']['abuse_complaint_rate'];

          // Average unique open rate
          $this->array['summary']['unique_open_rate'] = ( ! $this->array['summary']['unique_open_rate'] ) ?
                                                        $this->get_average( array( $values['open_rate'], $this->array['summary']['unique_open_rate'] ) ) :
                                                        $this->array['summary']['unique_open_rate'];

          // Average unique click rate
          $this->array['summary']['unique_click_rate'] = ( ! $this->array['summary']['unique_click_rate'] ) ?
                                                         $this->get_average( array( $values['click_rate'], $this->array['summary']['unique_click_rate'] ) ) :
                                                         $this->array['summary']['unique_click_rate'];

          // Average unsubscribe rate
          $this->array['summary']['unsubscribe_rate'] = ( ! $this->array['summary']['unsubscribe_rate'] ) ?
                                                        $this->get_average( array( $values['unsubscribe_rate'], $this->array['summary']['unsubscribe_rate'] ) ) :
                                                        $this->array['summary']['unsubscribe_rate'];

          // By date
          $this->add_to_group_array( 'by_date', $values['date_timestamp'], $values );

          // By weekday
          $this->add_to_group_array( 'by_weekday', $values['weekday'], $values );

          // By subject
          $this->add_to_group_array( 'by_subject', $values['subject'], $values );

          // By campaign
          $this->add_to_group_array( 'by_campaign', $values['title'], $values );
        }

        // Sort by_date array
        ksort ( $this->array['by_date'] );

        // Sort array to find highest/lowest campaigns

        // Highest/lowest campaign open rate
        $this->array['by_campaign']                  = $this->sort_array( $this->array['by_campaign'], 'unique_open_rate' );
        $this->array['summary']['highest_open_rate'] = reset ( $this->array['by_campaign'] );
        $this->array['summary']['lowest_open_rate']  = end ( $this->array['by_campaign'] );

        // Highest/lowest campaign click rate
        $this->array['by_campaign']                   = $this->sort_array( $this->array['by_campaign'], 'unique_click_rate' );
        $this->array['summary']['highest_click_rate'] = reset ( $this->array['by_campaign'] );
        $this->array['summary']['lowest_click_rate']  = end ( $this->array['by_campaign'] );

        // Highest/lowest abuse complaint rate
        $this->array['by_campaign']                             = $this->sort_array( $this->array['by_campaign'], 'abuse_complaint_rate' );
        $this->array['summary']['highest_abuse_complaint_rate'] = reset ( $this->array['by_campaign'] );
        $this->array['summary']['lowest_abuse_complaint_rate']  = end ( $this->array['by_campaign'] );

        // Highest/lowest unsubscribe rate
        $this->array['by_campaign']                         = $this->sort_array( $this->array['by_campaign'], 'unsubscribe_rate' );
        $this->array['summary']['highest_unsubscribe_rate'] = reset ( $this->array['by_campaign'] );
        $this->array['summary']['lowest_unsubscribe_rate']  = end ( $this->array['by_campaign'] );

        // Highest/lowest spam/trash rate
        $this->array['by_campaign']                         = $this->sort_array( $this->array['by_campaign'], 'trash_spam_rate' );
        $this->array['summary']['highest_trash_spam_rate']  = reset ( $this->array['by_campaign'] );
        $this->array['summary']['lowest_trash_spam_rate']   = end ( $this->array['by_campaign'] );

        // Add additional data to the array
        $this->array['summary']['start_date'] = key ( $this->array['by_date'] );
        end ( $this->array['by_date'] );
        $this->array['summary']['end_date'] = key ( $this->array['by_date'] );
        reset ( $this->array['by_date'] );

        $this->array['summary']['num_days'] = $this->get_num_days( $this->array['summary']['start_date'], $this->array['summary']['end_date'] );
    }
}
