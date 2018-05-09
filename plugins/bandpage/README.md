BandPage PHP SDK
================

Introduction
------------

The BandPage PHP SDK provides a few simple ways to retrieve information about artists using BandPage to manage their social presence. The SDK's API allows you to fetch domain object data (bands, tracks, events, etc.), as well as connections to those domain objects (ie, tracks belonging to a band). The underlying transport mechanism for making HTTP requests is modular, so  if you have your own solution, you won't be forced to use what we provide.

Installation
------------

Copy the SDK library file to a location within your web application's include path, and include it into any scripts that you wish to make API calls from.

    <?php
        include 'bandpage-sdk.php';
    ?>

Common Usage
------------

* Create a new instance of the SDK.

        <?php
            $credential = array(
                'clientId' => 'YOUR_CLIENT_ID',
                'sharedSecret' => 'YOUR_SHARED_SECRET'
            );
    
            $transport = new \BandPage\BandPageAPITransportCurl;

            $bp = \BandPage\BandPageAPI::of($credential, $transport);
        ?>

* Fetch an object based on RMID.

        <?php
            try {
                $band = $bp->get('12339928419799040');
            } catch (BandPageAPIException $e) {
                error_log("There was an error fetching the band: $e");
            }
        ?>
    
        Result: Array form of Band Object**

    ** [Band Object](https://developer.bandpage.com/docs/api_reference/Band_Object)

* Fetch an object's connections, based on RMID and connection type. The object you get back from the getConnection() call will allow you to fetch result pages that are sized to the limit that you specify as a request parameter.

        <?php
            // Fetch My Morning Jacket's tracks, two at a time.
            $requestParams = array('limit' => 2);

            try {
                $tracks = $bp->getConnection('12339928419799040', $requestParams);
                $firstPage = $tracks->getPage(0);
            } catch (BandPageAPIException $e) {
                error_log("Error fetching connections: $e");
            }
        ?>
    
        Result:
            $firstPage == Array (
                [0] => Array form of Track Object**
                [1] => Array form of Track Object**
            )
    ** [Track Object](https://developer.bandpage.com/docs/api_reference/Track_Object)

Customizing your HTTP Transport
===============================

The BandPage PHP SDK allows you to specify your own underlying mechanism to facilitate HTTP communication. This is done by specifying a common interface, "IBandPageAPITransportStrategy". It contains only one defined method, "makeRequest(BandPageAPIRequest $request)". The BandPageAPIRequest object defines getter methods for the standard HTTP request components. From these request details, you can perform the actual request in your own code and return the response as a BandPageAPIResponse object, as in the below example.

    <?
        // Define a custom way to facilitate HTTP communication.
        // Don't copy this one, it's obviously quite bad. ;)
        function my_custom_http_requestor($url, $method, $headers, $params) {
            // Pretend to make a request! HAH!
            return array(
                'code' => 200,
                'headers' => array('Content-type' => 'application/json'),
                'body' => '{"fakeValue":10}'
            );
        }

        // Implement the interface \BandPage\IBandPageAPITransportStrategy.
        class MyTransport implements \BandPage\IBandPageAPITransportStrategy {
            public function makeRequest(\BandPage\BandPageAPIRequest $request) {
                $response = my_custom_http_requestor(
                    $request->getUrl(),     // A string.
                    $request->getMethod(),  // One of: 'GET', 'POST'
                    $request->getHeaders(), // Array of <header name> => <value(s)>
                    $request->getParams()   // Array of <key> => <value(s)>
                );

                return \BandPage\BandPageAPIResponse.of(
                    $response['code'],    // HTTP response code, a number.
                    $response['headers'], // Array of <header name> => <value(s)>
                    $response['body'],    // A string.
                    $request              // The original BandPageAPI request passed into this method.
                );
            }
        }

        // Initialize an instance of the SDK with your shiny new HTTP transport!

        $credential = array(
            'clientId' => 'my client id',
            'sharedSecret' => 'my shared secret'
        );

        $bp = \BandPage\BandPageAPI.of($credential, new MyTransport());
    ?>
