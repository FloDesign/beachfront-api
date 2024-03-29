<?php
require_once('classes/Booking.php');
require_once('classes/Property.php');

use Beachfront\Booking as Booking;
use Beachfront\Property as Property;

class Beachfront_Booking_API extends WP_REST_Controller
{
    
    public function __construct()
    {
        $this->booking  = new Booking();
        $this->property = new Property();
    }
    
    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version   = '1';
        $namespace = 'beachfront/v'.$version;
        $base      = 'bookings';
        register_rest_route(
            $namespace,
            '/'.$base,
            array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_items'),
                ),
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_item'),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/'.$base.'/(?P<id>[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})',
            array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_item'),
                ),
                array(
                    'methods'  => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'update_item'),
                ),
                array(
                    'methods'  => WP_REST_Server::DELETABLE,
                    'callback' => array($this, 'delete_item'),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/'.$base.'/schema',
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_public_item_schema'),
            )
        );
        register_rest_route(
            $namespace,
            '/properties',
            array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_property'),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/properties'.'/(?P<id>[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})',
            array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_property'),
                ),
            )
        );
    }
    
    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request)
    {
        $bookings = $this->booking->getBookings($request);
        $data     = array();
        foreach ($bookings as $item) {
            //$itemdata = $this->prepare_item_for_response($item, $request);
            $data[] = $this->prepare_response_for_collection($item);
        }
        
        return new WP_REST_Response($data, 200);
    }
    
    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request)
    {
        //get parameters from request
        $params  = $request->get_params();
        $booking = $this->booking->getBooking($params);
        //$data    = $this->prepare_item_for_response($booking, $request);
        
        //return a response or error based on some conditional
        if (is_array($booking)) {
            return new WP_REST_Response($booking, 200);
        } else {
            return new WP_Error('cant-find', __('Could not find that booking'));
        }
    }
    
    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_item($request)
    {
        $params = $request->get_params();
        $result = $this->booking->createBooking($params);
        if ($result instanceof Exception) {
            return new WP_Error('cant-create', __($result->getMessage()), array('status' => 500));
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_item($request)
    {
        $data   = $request->get_params();
        $result = $this->booking->updateBooking($request);
        
        if ($result instanceof WP_Error) {
            return $result;
        } else {
            return new WP_REST_Response($result, 200);
        }
    }
    
    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_item($request)
    {
        $params = $request->get_params();
        $deleted = $this->booking->deleteBooking($params);
        if ($deleted instanceof Exception) {
            return new WP_Error('cant-delete', __($deleted->getMessage()), array('status' => 500));
        }
        
        return new WP_REST_Response(true, 200);
        
    }
    
    public function get_property($request)
    {
        $result = $this->property->getProperty($request);
        
        if ($result instanceof WP_Error) {
            return $result;
        } else {
            return new WP_REST_Response($result, 200);
        }
    }
    
    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     *
     * @return mixed
     */
    public function prepare_item_for_response($item, $request)
    {
        return array();
    }
    
    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params()
    {
        return array(
            'page'     => array(
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description'       => 'Maximum number of items to be returned in result set.',
                'type'              => 'integer',
                'default'           => -1,
                'sanitize_callback' => 'absint',
            ),
            'search'   => array(
                'description'       => 'Limit results to those matching a string.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }
}