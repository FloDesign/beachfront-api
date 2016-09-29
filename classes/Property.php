<?php


namespace Beachfront;


class Property
{
    public function getProperty($params)
    {
        if (isset($params['id'])) {
            $args = array(
                'post_type'      => 'villa',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'property_id',
                        'compare' => '==',
                        'value'   => $params['id'],
                    ),
                ),
            );
        } else {
            $args = array(
                'post_type'      => 'villa',
                'posts_per_page' => -1,
            );
        }
        
        $properties = get_posts($args);
        
        foreach ($properties as $property) {
            $booking_args = array(
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
            );
            
            $bookings = get_posts($booking_args);
            
            foreach ($bookings as $booking) {
                die(var_dump(get_field('villa', $booking->ID)));
                $villa = get_field('villa', $booking->ID);
                
                if ($villa->ID == $property->ID) {
                    $property->bookings = $bookings;
                }
            }
        }
        
        return $properties;
    }
}