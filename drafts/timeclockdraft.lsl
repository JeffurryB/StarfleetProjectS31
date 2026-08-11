// ==========================================================
// CONFIGURATION v0.0.1a
// ==========================================================
string URL = "http://yourserver.com"; // Replace with your actual PHP URL
string SECRET_KEY = "my_secure_handshake_key";     // Must match the $secret_key in your PHP file

key http_request_id;

default
{
    touch_start(integer total_number)
    {
        // Get the key and name of the avatar who clicked the object
        key user_id = llDetectedKey(0);
        string user_name = llDetectedName(0);
        
        // Construct the POST data query string
        string body = "user_id=" + (string)user_id + 
                      "&user_name=" + llEscapeURL(user_name) + 
                      "&secret=" + SECRET_KEY;
        
        // Send the HTTP POST request to the PHP backend
        http_request_id = llHTTPRequest(URL, [
            HTTP_METHOD, "POST",
            HTTP_MIMETYPE, "application/x-www-form-urlencoded"
        ], body);
        
        // Let the user know the object is working on it
        llRegionSayTo(user_id, 0, "Processing time clock status, please wait...");
    }
    
    http_response(key request_id, integer status, list metadata, string body)
    {
        // Ensure the incoming server response matches the request we sent
        if (request_id == http_request_id)
        {
            if (status == 200)
            {
                // Print the success/welcome/goodbye message directly from your PHP script
                llOwnerSay("Server response: " + body);
            }
            else
            {
                // Something went wrong on the server side or with the URL connection
                llOwnerSay("Error connecting to server. Status code: " + (string)status);
            }
        }
    }
}
