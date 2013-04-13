<?php

/**
 * Description: Talks to api to get the response
 *
 * @author: hello@jabran.me
 * @link: http://github.com/jabranr/iplayerion
 * @version: 0.1
 *
 * @package: Search and Browse with BBC iPlayer API
 * 
 */

// Setup the headers for cache control
if ( !headers_sent() )
	header('Cache-Control: private, no-cache');

// Validate the constant to deny direct access
//if ( !defined('AUTHORISED_ACCESS') )
//	header('Location: ../example/');

class IONAPI {
	
	// Define variable
	private $fields,
			$_magic_quotes_gpc,
			$_real_escape_quotes;

	// @override: Overriding default construct magic function
	public function __construct( $endpoint = '', $websiteurl = '' )	{
		$this->_magic_quotes_gpc = get_magic_quotes_gpc();
		$this->_real_escape_quotes = function_exists('mysql_real_escape_string');
		$this->fields = array(
			'websiteurl' => 'http://bbc.co.uk',
			'endpoint' => 'http://www.bbc.co.uk/iplayer/ion/searchextended',
			'search_availability' => 'iplayer',
			'service_type' => 'radio',
			'format' => 'json',
			'perpage' => 10,
			'search_query' => 'chris',
			'query' => '',
			'next_query' => '',
			'total_results' => 0,
			'results_per_page' => 0,
			'total_pages' => 0,
			'current_page'=> 0,
			'thumbnail_width' => 172,
			'thumbnail_height' => 96,
			'media' => array()
		);
	}

	// @override: Overriding default get magic function
	public function __get( $field )	{
		if ( array_key_exists($field, $this->fields) )
			return $this->fields[$field];
	}
	
	// @override: Overriding default set magic function
	public function __set( $field, $value )	{
		if ( array_key_exists( $field, $this->fields ) )	{
			$this->fields[$field] = $value;
		}
	}

	// @return: Sets the query for api call
	public function set_query()	{

		$search_availability = $this->__get('search_availability') ? '/search_availability/' . $this->__get('search_availability') : '';
		$service_type = $this->__get('service_type') ? '/service_type/' . $this->__get('service_type') : '';
		$format = $this->__get('format') ? '/format/' . $this->__get('format') : '';
		$perpage = $this->__get('perpage') ? '/perpage/' . $this->__get('perpage') : '';
		$search_query = $this->__get('search_query') ? '/q/' . $this->__get('search_query') : '';

		$query = $this->endpoint . $search_availability . $service_type . $format . $perpage . $search_query;
		return $this->__set( 'query', $query );
	}

	// @return: Returns decoded data in array format
	public function get_data()	{
		$query = $this->__get('query');
		return $this->talk_to_api( $query );
	}

	// @return: Get data from api and set variables
	private function talk_to_api( $api_query )	{
		$data = file_get_contents( $api_query );
		$data = $this->json_to_array( $data );

		// @return: Set total results
		$this->__set( 'total_results', $data->count  );

		// @return: Set current page
		$this->__set( 'current_page', $data->pagination->page );

		// @return: Set results per page
		$this->set_results_per_page();

		// @return: Set total pages
		$this->__set( 'total_pages', ceil( $this->__get('total_results') / $this->__get('results_per_page') ) );

		// @return: Set media data
		$this->__set( 'media', $data->blocklist  );

	}

	// @return: Set results per page
	private function set_results_per_page( $num = 10 )	{
		if ( $this->__get( 'total_results' ) <= 10 ) 
			return $this->__set( 'results_per_page', $this->__get('total_results') );
		return $this->__set( 'results_per_page', $num );
	}

	// @return: Parse and decode json data
	private function json_to_array( $data )	{
		return json_decode( $data );
	}

	// Sanitize function to clean up data
	private function sanitize( $value = '' )	{
		if ( $this->_real_escape_quotes )	{
			if ($this->_magic_quotes_gpc )	{
				$value = stripslashes( $value );
				$value = mysql_real_escape_string( $value );
			}
		}
		else	{
			if ( !$this->_magic_quotes_gpc )
				$value = addslashes( $value );
		}
		return $value;
	}

	// @override: Overriding default destruct magic function
	public function __destruct()	{
		return true;
	}

}

$bbcapi = new IONAPI();

