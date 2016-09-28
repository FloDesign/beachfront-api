<?php
require_once('classes/Booking.php');

use Beachfront\Booking as Booking;

class Beachfront_Booking_API extends WP_REST_Controller
{
    
    public function __construct()
    {
        $this->booking = new Booking();
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
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_items'),
                    'permission_callback' => array($this, 'get_items_permissions_check'),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'create_item'),
                    'permission_callback' => array($this, 'create_item_permissions_check'),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/'.$base.'/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_item'),
                    'permission_callback' => array($this, 'get_item_permissions_check'),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array($this, 'update_item'),
                    'permission_callback' => array($this, 'update_item_permissions_check'),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array($this, 'delete_item'),
                    'permission_callback' => array($this, 'delete_item_permissions_check'),
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
            $itemdata = $this->prepare_item_for_response($item, $request);
            $data[]   = $this->prepare_response_for_collection($itemdata);
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
        $data    = $this->prepare_item_for_response($booking, $request);
        
        //return a response or error based on some conditional
        if (is_array($booking)) {
            return new WP_REST_Response($data, 200);
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
        
        $item = $this->prepare_item_for_database($request);
        
            $data = $this->booking->createBooking($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        
        return new WP_Error('cant-create', __('Could not create a booking'), array('status' => 500));
        
        
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
        $item = $this->prepare_item_for_database($request);
        
            $data = $this->booking->updateBooking($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        
        return new WP_Error('cant-update', __('Could not update the booking'), array('status' => 500));
        
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
        $item = $this->prepare_item_for_database($request);
        
            $deleted = $this->booking->deleteBooking($item);
            if ($deleted) {
                return new WP_REST_Response(true, 200);
            }
        
        return new WP_Error('cant-delete', __('Could not delete the booking'), array('status' => 500));
    }
    
    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function get_items_permissions_check($request)
    {
        //return true; <--use to make readable by all
        return current_user_can('read_post');
    }
    
    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function get_item_permissions_check($request)
    {
        return $this->get_items_permissions_check($request);
    }
    
    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function create_item_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }
    
    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function update_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }
    
    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|bool
     */
    public function delete_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }
    
    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     *
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database($request)
    {
        return array();
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