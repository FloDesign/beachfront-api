<?php


namespace Beachfront;


class Property
{
    public function getProperty($params)
    {
        if (isset($params['id'])) {
            $args = array(
                'post_type'  => 'villa',
                'posts_per_page' => -1
                'meta_query' => array(
                    array(
                        'key'     => 'property_id',
                        'compare' => '==',
                        'value'   => $params['id'],
                    ),
                ),
            );
        } else {
            $args = array(
                'post_type' => 'villa',
                'posts_per_page' => -1
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
                'meta_query'  => array(
                    array(
                        'key'     => 'villa',
                        'compare' => '==',
                        'value'   => $property->ID,
                    ),
                ),
            );
            
            $bookings = get_posts($booking_args);
            $property->bookings = $bookings;
        }
        
        return $properties;
    }
}