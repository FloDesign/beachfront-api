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
            $property->property_id = get_field('property_id', $property->ID);
            
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
                $villa = get_field('villa', $booking->ID);
                
                if ($villa[0]->ID == $property->ID) {
                    $property->bookings = $bookings;
                    $booking->booking_id = get_field('booking_id', $booking->ID);
                }
            }
        }
        
        return $properties;
    }
}