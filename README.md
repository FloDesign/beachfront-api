# Beachfront Bookings API

This plugin adds several endpoints and methods for management of Bookings on beachfrontvillas.tc

#Routes

##Bookings

###wp-json/beachfront/v1/bookings

```
- Method: GET
- Description: Get all bookings
- Body Params: None
```
```
- Method: POST
- Description: Create a new booking
- Body Params:  start_date    (string|required)    YYYY-MM-DD
                end_date      (string|required)    YYYY-MM-DD
                property_id   (uuid|required)      Version 4 UUID
                booking_id    (uuid|required)      Version 4 UUID
                post_status   (string|optional)    One of: 'publish'|'draft'
```

###wp-json/beachfront/v1/bookings/{uuid}

```
- Method: GET
- Description: Get a single booking record based on the uuid in the url
- Body Params: None 
```
```
- Method: POST
- Description: Update a booking
- Body Params:  start_date    (string|optional)    YYYY-MM-DD
                end_date      (string|optional)    YYYY-MM-DD
                property_id   (uuid|optional)      Version 4 UUID
                post_status   (string|optional)    One of: 'publish'|'draft'
```
```
- Method: DELETE
- Description: Delete a booking based on the uuid in the url
- Body Params:  None
```

###wp-json/beachfront/v1/properties/

```
- Method: GET
- Description: Get all properties with their bookings
- Body Params: None
```

###wp-json/beachfront/v1/properties/{uuid}

```
- Method: GET
- Description: Get a single property with its bookings based on uuid in the url
- Body Params: None
```