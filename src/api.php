<?php

/**
 * Description: Talks to api to get the response
 *
 * @author: hello@jabran.me
 * @link: http://github.com/jabranr/iplayerion
 * @version: 0.3
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
			'query' => '',
			'current_query' => '',
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

	// @return: Set value for search_availability
	public function get_search_availability()	{
		return $this->__get( 'search_availability' );
	}

	// @return: Set value for service type
	public function get_service_type()	{
		return $this->__get( 'service_type' );
	}

	// @return: Set value for perpage
	public function get_perpage()	{
		return $this->__get( 'perpage' );
	}

	// @return: Set value for format
	public function get_format()	{
		return $this->__get( 'format' );
	}

	// @return: Sets the query for api call
	public function get_query()	{
		return $this->__get( 'query' );
	}

	// @return: Sets the query for api call
	public function get_current_query()	{
		return $this->__get( 'current_query' );
	}

	// @return: Sets the query for api call
	public function get_next_query()	{
		return $this->__get( 'next_query' );
	}

	// @return: Set value for search_availability
	public function search_availability( $search_availability )	{
		return $this->__set( 'search_availability', $this->sanitize( $search_availability ) );
	}

	// @return: Set value for service type
	public function service_type( $service_type )	{
		return $this->__set( 'service_type', $this->sanitize( $service_type ) );
	}

	// @return: Set value for perpage
	public function perpage( $perpage )	{
		return $this->__set( 'perpage', $this->sanitize( $perpage ) );
	}

	// @return: Set value for format
	public function format( $format )	{
		return $this->__set( 'format', $this->sanitize( $format ) );
	}

	// @return: Sets the query for api call
	public function query( $query )	{
		return $this->__set( 'query', $this->sanitize( $query ) );
	}

	// @return: Setup the current and next query structure
	private function setup_queries()	{
		$structure = $this->endpoint;
		$structure .= $this->get_search_availability() ? '/search_availability/' . $this->get_search_availability() : '';
		$structure .= $this->get_service_type() ? '/service_type/' . $this->get_service_type() : '';
		$structure .= $this->get_format() ? '/format/' . $this->get_format() : '';
		$structure .= $this->get_perpage() ? '/perpage/' . $this->get_perpage() : '';
		$structure .= $this->get_query() ? '/q/' . $this->get_query() : '';

		return $this->__set( 'current_query', $structure );
	}

	// @return: Execute the query and return data object
	public function get_data()	{
		$this->setup_queries();
		$query = $this->get_current_query();
		$this->talk_to_api( $query );
		return $this;
	}

	// @return: Get data from api and set variables
	private function talk_to_api( $query )	{

		$data = array();

		// @return: Get results from API server
		if ( $query )
			$data = file_get_contents( $query );

		// @return: If data received then parse and decode received data
		if ( $data )
			$data = $this->json_to_array( $data );
		else
			return false;

		if ( $data->count ) 	{
			// @return: Set total results
			$this->__set( 'total_results', $data->count  );

			// @return: Set current page
			$this->__set( 'current_page', $data->pagination->page );

			// @return: Set results per page
			$this->set_results_per_page();

			// @return: Set total pages
			$this->__set( 'total_pages', ceil( $this->__get('total_results') / $this->get_perpage() ));

			// @return: Set media data
			return $this->__set( 'media', $data->blocklist  );
		}
		return false;
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

	public function get_image_uri( $url, $id )	{
		$width = $this->__get('thumbnail_width');
		$height = $this->__get('thumbnail_height');
		$ext = '.jpg';
		$image_url = sprintf("%s%s_%d_%d%s", $url, $id, $width, $height, $ext);
		return $image_url;
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