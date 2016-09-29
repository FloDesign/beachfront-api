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
        if ( ! isset($request['property_id'])) {
            
            $args = array(
                'post_type'      => 'booking',
                'posts_per_page' => -1,
            );
            
            $bookings = get_posts($args);
            
            foreach ($bookings as $booking) {
                $booking->startdate = get_field('start_date', $booking->ID);
                $booking->enddate   = get_field('end_date', $booking->ID);
                $booking->villa     = get_field('villa', $booking->ID);
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
            
            foreach ($bookings as $booking) {
                $booking->startdate = get_field('start_date', $booking->ID);
                $booking->enddate   = get_field('end_date', $booking->ID);
                $booking->villa     = get_field('villa', $booking->ID);
            }
            
            return $bookings;
        }
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
        try {
            $args = array(
                'post_type'  => 'booking',
                'meta_query' => array(
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
                            'post_date_gmt' => date("Y-m-d H:i:s")
                        );
                    } else {
                        $data = array(
                            'ID'          => $booking->ID,
                            'post_status' => 'publish',
                            'post_date_gmt' => date("Y-m-d H:i:s")
                        );
                    }
                    die(var_dump(wp_update_post($data, true)));
                }
            }
            
            if ($post_id instanceof WP_Error) {
                return $post_id;
            } else {
                $booking = get_post($post_id);
            }
            
            $booking->startdate = get_field('start_date', $booking->ID);
            $booking->enddate   = get_field('end_date', $booking->ID);
            $booking->villa     = get_field('villa', $booking->ID);
            
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
        if ( ! isset($request['id'])) {
            return wp_delete_post($request['id']);
        }
    }
}