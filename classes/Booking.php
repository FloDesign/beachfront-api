<?php


namespace Beachfront;


class Booking
{
    /**
     * Return a single booking based on ID from query string
     *
     * @param $params
     *
     * @return WP_Query
     */
    public function getBooking($params)
    {
        $args = array(
            'post_type'  => 'booking',
            'meta_query' => array(
                array(
                    'key'     => 'booking_id',
                    'compare' => '==',
                    'value'   => $params['id'],
                ),
            ),
        );
        
        $booking = get_posts($args);
        
        foreach ($booking as $post) {
            $post->startdate = get_field('start_date', $post->ID);
            $post->enddate   = get_field('end_date', $post->ID);
            $post->villa     = get_field('villa', $post->ID);
        }
        
        return $booking;
        
    }
    
    /**
     * Retrieves either all bookings or bookings for a single villa based on property_id
     *
     * @param $request
     *
     * @return string
     */
    public function getBookings($request)
    {
        $args = array(
            'post_type'      => 'booking',
            'posts_per_page' => -1,
            'post_status'    => array(
                'publish',
                'pending',
                'draft',
                'auto-draft',
                'future',
                'private',
                'inherit',
                'trash',
            ),
        );
        
        $bookings = get_posts($args);
        
        foreach ($bookings as $booking) {
            $booking->startdate  = get_field('start_date', $booking->ID);
            $booking->enddate    = get_field('end_date', $booking->ID);
            $booking->villa      = get_field('villa', $booking->ID);
            $booking->booking_id = get_field('booking_id', $booking->ID);
        }
        
        return $bookings;
        
        
    }
    
    /**
     * Create booking from array of data
     *
     * @param $request
     *
     * @return bool
     */
    public function createBooking($request)
    {
        if(!array_key_exists('start_date', $request)){
            return new \Exception('Requires a start_date');
        }
    
        if(!array_key_exists('end_date', $request)){
            return new \Exception('Requires an end_date');
        }
    
        if(!array_key_exists('property_id', $request)){
            return new \Exception('Requires a property_id');
        }
    
        if(!array_key_exists('booking_id', $request)){
            return new \Exception('Requires a booking_id');
        }
        
        if(array_key_exists('post_status', $request)){
            $status = $request['post_status'];
        } else {
            $status = 'draft';
        }
        
        $post = array(
            'post_content' => '',
            'post_title' => $request['booking_id'],
            'post_excerpt' => '',
            'post_type' => 'booking',
            'post_status' => $status
        );
        
        $result = wp_insert_post($post, true);
        
        if(is_int($result)){
            $property_args = array(
                'post_type' => 'villa',
                'meta_query' => array(
                    array(
                        'key' => 'property_id',
                        'compare' => '==',
                        'value' => $request['property_id']
                    )
                )
            );
            
            $properties = get_posts($property_args);
            
            update_field('start_date', $request['start_date'], $result);
            update_field('end_date', $request['end_date'], $result);
            update_field('villa', array($properties[0]->ID), $result);
            update_field('booking_id', $request['booking_id'], $result);
        } else {
            return $result;
        }
        
        $booking = get_post($result);
        
        $booking->villa = $properties[0];
        $booking->booking_id = get_field('booking_id', $booking);
        return $booking;
    }
    
    /**
     * Updates a single booking based on a booking UUID
     *
     * @param $request
     *
     * @return WP_Query
     */
    public function updateBooking($request)
    {
        try {
            $args = array(
                'post_type'   => 'booking',
                'post_status' => array(
                    'publish',
                    'pending',
                    'draft',
                    'auto-draft',
                    'future',
                    'private',
                    'inherit',
                    'trash',
                ),
                'meta_query'  => array(
                    array(
                        'key'     => 'booking_id',
                        'compare' => '==',
                        'value'   => $request['id'],
                    ),
                ),
            );
            
            $bookings = get_posts($args);
            
            $booking = $bookings[0];
            
            if ($booking) {
                if (isset($request['start_date'])) {
                    update_post_meta($booking->ID, 'start_date', $request['start_date']);
                }
                if (isset($request['end_date'])) {
                    update_post_meta($booking->ID, 'end_date', $request['end_date']);
                }
                if (isset($request['property_id'])) {
                    $villa_args = array(
                        'post_type'  => 'villa',
                        'meta_query' => array(
                            'key'     => 'property_id',
                            'compare' => '==',
                            'value'   => $request['property_id'],
                        ),
                    );
                    
                    $villa = get_posts($villa_args);
                    
                    update_field('villa', $villa, $booking->ID);
                }
                if (isset($request['show_booking'])) {
                    if ($request['show_booking'] == 'true') {
                        $data = array(
                            'ID'          => $booking->ID,
                            'post_status' => 'publish',
                        );
                    } else {
                        $data = array(
                            'ID'          => $booking->ID,
                            'post_status' => 'draft',
                        );
                    }
                    $post_id = wp_update_post($data, true);
                }
            }
            
            if ($post_id instanceof WP_Error) {
                return $post_id;
            } else {
                $booking = get_post($post_id);
            }
            
            $booking->startdate  = get_field('start_date', $booking->ID);
            $booking->enddate    = get_field('end_date', $booking->ID);
            $booking->villa      = get_field('villa', $booking->ID);
            $booking->booking_id = get_field('booking_id', $booking->ID);
            
            return $booking;
        } catch (Exception $e) {
            return new WP_Error('cant-update', __('Could not update the booking'), array('status' => 500));
        }
        
    }
    
    /**
     * Deletes a single booking record
     *
     * @param $request
     *
     * @return mixed
     */
    public function deleteBooking($request)
    {
        $args = array(
            'post_type'   => 'booking',
            'post_status' => array(
                'publish',
                'pending',
                'draft',
                'auto-draft',
                'future',
                'private',
                'inherit',
                'trash',
            ),
            'meta_query'  => array(
                array(
                    'key'     => 'booking_id',
                    'compare' => '==',
                    'value'   => $request['id'],
                ),
            ),
        );
        
        $booking = get_posts($args);
        
        if(!empty($booking)){
            foreach($booking as $post){
                wp_delete_post($post->ID);
            }
            return true;
        } else {
            return new \Exception('Could not find a booking with that ID');
        }
    }
}