<?php


namespace Beachfront;


class Booking
{
    
    /**
     * Properties
     */
    
    private $booking_id;
    
    
    /**
     * Return a single booking based on ID from query string
     * @param $params
     *
     * @return WP_Query
     */
    public function getBooking($params)
    {
        $args = array(
            'post_type' => 'booking',
            'meta_query' => array(
                'key' => 'booking_id',
                'compare' => '==',
                'value' => $params['id']
            )
        );
        
        $bookings = get_posts($args);
        
        return $bookings;
        
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
        if ( ! isset($request['property_id'])) {
            
            $args = array(
                'post_type'      => 'booking',
                'posts_per_page' => -1,
            );
            
            $bookings = get_posts($args);
            
            foreach($bookings as $booking){
                $meta = get_post_meta($booking->ID);
                $booking['meta'] = $meta;
            }
            
            return $bookings;
        } else {
            $villa_args = array(
                'post_type'  => 'villa',
                'meta_query' => array(
                    'key'     => 'property_id',
                    'compare' => '==',
                    'value'   => $request['property_id'],
                ),
            );
            
            $villa = get_posts($villa_args);
            
            $booking_args = array(
                'post_type'  => 'booking',
                'meta_query' => array(
                    'key'        => 'villa',
                    'comparison' => '==',
                    'value'      => $villa->id,
                ),
            );
            
            $bookings = get_posts($booking_args);
    
            foreach($bookings as $booking){
                $meta = get_post_meta($booking->ID);
                $booking['meta'] = $meta;
            }
            
            return $bookings;
        }
    }
    
    /**
     * Create booking from array of data
     * @param $request
     *
     * @return bool
     */
    public function createBooking($request)
    {
        $result = wp_insert_post($request);
        
        if (is_integer($result)) {
            return get_post($result);
        } else {
            return false;
        }
        
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
        $args = array(
            'post_type'      => 'booking',
            'id'             => $request['booking_id'],
            'posts_per_page' => 1,
        );
        
        $booking = get_posts($args);
    
        if(isset($data['booking'])){
            $booking = wp_update_post($data['booking']);
        }
    
        if(isset($data['booking_meta'])){
            foreach($data['booking_meta'] as $key => $value){
                update_post_meta($booking->ID, $key, $value);
            }
        }
        
        return $booking;
    }
    
    /**
     * Deletes a single booking record
     * @param $request
     *
     * @return mixed
     */
    public function deleteBooking($request)
    {
        if ( ! isset($request['booking_id'])) {
            return wp_delete_post($request['booking_id']);
        }
    }
}