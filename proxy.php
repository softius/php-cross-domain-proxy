<?php

/**
 * AJAX Cross Domain (PHP) Proxy 0.8
 *    by Iacovos Constantinou (http://www.iacons.net)
 *
 * Released under CC-GNU GPL
 */

/**
 * Enables or disables filtering for cross domain requests.
 * Recommended value: true
 */
define('CSAJAX_FILTERS', true);

/**
 * If set to true, $valid_requests should hold only domains i.e. a.example.com, b.example.com, usethisdomain.com
 * If set to false, $valid_requests should hold the whole URL ( without the parameters ) i.e. http://example.com/this/is/long/url/
 * Recommended value: false (for security reasons - do not forget that anyone can access your proxy)
 */
define('CSAJAX_FILTER_DOMAIN', false);

/**
 * Set debugging to true to receive additional messages - really helpful on development
 */
define('CSAJAX_DEBUG', false);

/**
 * A set of valid cross domain requests
 */
$valid_requests = array(
    // 'example.com'
);

/**
 * Set extra multiple options for cURL
 * Could be used to define CURLOPT_SSL_VERIFYPEER & CURLOPT_SSL_VERIFYHOST for HTTPS
 * Also to overwrite any other options without changing the code
 * See http://php.net/manual/en/function.curl-setopt-array.php
 */
$curl_options = array(
    // CURLOPT_SSL_VERIFYPEER => false,
    // CURLOPT_SSL_VERIFYHOST => 2,
);

/* * * STOP EDITING HERE UNLESS YOU KNOW WHAT YOU ARE DOING * * */

/* How curl can fail */
$curl_errno_text = array(
    1  => "CURLE_UNSUPPORTED_PROTOCOL",
    2  => "CURLE_FAILED_INIT",
    3  => "CURLE_URL_MALFORMAT",
    4  => "CURLE_URL_MALFORMAT_USER",
    5  => "CURLE_COULDNT_RESOLVE_PROXY",
    6  => "CURLE_COULDNT_RESOLVE_HOST",
    7  => "CURLE_COULDNT_CONNECT",
    8  => "CURLE_FTP_WEIRD_SERVER_REPLY",
    9  => "CURLE_FTP_ACCESS_DENIED",
    10 => "CURLE_FTP_USER_PASSWORD_INCORRECT",
    11 => "CURLE_FTP_WEIRD_PASS_REPLY",
    12 => "CURLE_FTP_WEIRD_USER_REPLY",
    13 => "CURLE_FTP_WEIRD_PASV_REPLY",
    14 => "CURLE_FTP_WEIRD_227_FORMAT",
    15 => "CURLE_FTP_CANT_GET_HOST",
    16 => "CURLE_FTP_CANT_RECONNECT",
    17 => "CURLE_FTP_COULDNT_SET_BINARY",
    18 => "CURLE_FTP_PARTIAL_FILE or CURLE_PARTIAL_FILE",
    19 => "CURLE_FTP_COULDNT_RETR_FILE",
    20 => "CURLE_FTP_WRITE_ERROR",
    21 => "CURLE_FTP_QUOTE_ERROR",
    22 => "CURLE_HTTP_NOT_FOUND or CURLE_HTTP_RETURNED_ERROR",
    23 => "CURLE_WRITE_ERROR",
    24 => "CURLE_MALFORMAT_USER",
    25 => "CURLE_FTP_COULDNT_STOR_FILE",
    26 => "CURLE_READ_ERROR",
    27 => "CURLE_OUT_OF_MEMORY",
    28 => "CURLE_OPERATION_TIMEDOUT or CURLE_OPERATION_TIMEOUTED",
    29 => "CURLE_FTP_COULDNT_SET_ASCII",
    30 => "CURLE_FTP_PORT_FAILED",
    31 => "CURLE_FTP_COULDNT_USE_REST",
    32 => "CURLE_FTP_COULDNT_GET_SIZE",
    33 => "CURLE_HTTP_RANGE_ERROR",
    34 => "CURLE_HTTP_POST_ERROR",
    35 => "CURLE_SSL_CONNECT_ERROR",
    36 => "CURLE_BAD_DOWNLOAD_RESUME or CURLE_FTP_BAD_DOWNLOAD_RESUME",
    37 => "CURLE_FILE_COULDNT_READ_FILE",
    38 => "CURLE_LDAP_CANNOT_BIND",
    39 => "CURLE_LDAP_SEARCH_FAILED",
    40 => "CURLE_LIBRARY_NOT_FOUND",
    41 => "CURLE_FUNCTION_NOT_FOUND",
    42 => "CURLE_ABORTED_BY_CALLBACK",
    43 => "CURLE_BAD_FUNCTION_ARGUMENT",
    44 => "CURLE_BAD_CALLING_ORDER",
    45 => "CURLE_HTTP_PORT_FAILED",
    46 => "CURLE_BAD_PASSWORD_ENTERED",
    47 => "CURLE_TOO_MANY_REDIRECTS",
    48 => "CURLE_UNKNOWN_TELNET_OPTION",
    49 => "CURLE_TELNET_OPTION_SYNTAX",
    50 => "CURLE_OBSOLETE",
    51 => "CURLE_SSL_PEER_CERTIFICATE",
    52 => "CURLE_GOT_NOTHING",
    53 => "CURLE_SSL_ENGINE_NOTFOUND",
    54 => "CURLE_SSL_ENGINE_SETFAILED",
    55 => "CURLE_SEND_ERROR",
    56 => "CURLE_RECV_ERROR",
    57 => "CURLE_SHARE_IN_USE",
    58 => "CURLE_SSL_CERTPROBLEM",
    59 => "CURLE_SSL_CIPHER",
    60 => "CURLE_SSL_CACERT",
    61 => "CURLE_BAD_CONTENT_ENCODING",
    62 => "CURLE_LDAP_INVALID_URL",
    63 => "CURLE_FILESIZE_EXCEEDED",
    64 => "CURLE_FTP_SSL_FAILED",
    79 => "CURLE_SSH"
);

// identify request headers
$request_headers = array( );
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0  ||  strpos($key, 'CONTENT_') === 0) {
        $headername = str_replace('_', ' ', str_replace('HTTP_', '', $key));
        $headername = str_replace(' ', '-', ucwords(strtolower($headername)));
        if (!in_array($headername, array('Host', 'X-Proxy-Url'))) {
            $request_headers[] = "$headername: $value";
        }
    }
}

// identify request method, url and params
$request_method = $_SERVER['REQUEST_METHOD'];
if ('GET' == $request_method) {
    $request_params = $_GET;
} elseif ('POST' == $request_method) {
    $request_params = $_POST;
    if (empty($request_params)) {
        $data = file_get_contents('php://input');
        if (!empty($data)) {
            $request_params = $data;
        }
    }
} elseif ('PUT' == $request_method || 'DELETE' == $request_method) {
    $request_params = file_get_contents('php://input');
} else {
    $request_params = null;
}

// Get URL from `csurl` in GET or POST data, before falling back to X-Proxy-URL header.
if (isset($_REQUEST['csurl'])) {
    $request_url = urldecode($_REQUEST['csurl']);
} elseif (isset($_SERVER['HTTP_X_PROXY_URL'])) {
    $request_url = urldecode($_SERVER['HTTP_X_PROXY_URL']);
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    header('Status: 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    exit;
}

$p_request_url = parse_url($request_url);

// csurl may exist in GET request methods
if (is_array($request_params) && array_key_exists('csurl', $request_params)) {
    unset($request_params['csurl']);
}

// ignore requests for proxy :)
if (preg_match('!' . $_SERVER['SCRIPT_NAME'] . '!', $request_url) ||
    empty($request_url) || count($p_request_url) == 1) {
    csajax_debug_message('Invalid request - make sure that csurl variable is not empty');
    exit;
}

$accessGranted = true;

// check against valid requests
if (CSAJAX_FILTERS) {
    $parsed = $p_request_url;
    if (CSAJAX_FILTER_DOMAIN) {
        if (!in_array($parsed['host'], $valid_requests)) {
            $response_headers = "HTTP/1.1 403 Forbidden" . "\r\n" . "Status: 403 Forbidden";
            $response_content = "<h1>403 Forbidden</h1>" .
            "<p>This proxy does not allow access to<br /></p>" .
            "<ul><li><a href='" . $request_url . "'>" . $request_url . "</a></em></li></ul>" .
            "<h2>Reason</h2>" .
            "Whitelist does not contain hostname" .
            "<ul><li><strong>" . $parsed['host'] . "</strong></li></ul>";
            "<ul><li><a href='" . $request_url . "'>" . $request_url . "</a></li></ul>";
            $accessGranted = false;
        }
    } else {
        $check_url = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
        $check_url .= isset($parsed['user']) ? $parsed['user'] .
                      ($parsed['pass'] ? ':' . $parsed['pass'] : '') . '@' : '';
        $check_url .= isset($parsed['host']) ? $parsed['host'] : '';
        $check_url .= isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $check_url .= isset($parsed['path']) ? $parsed['path'] : '';
        if (!in_array($check_url, $valid_requests)) {
            $response_headers = "HTTP/1.1 403 Forbidden" . "\r\n" . "Status: 403 Forbidden";
            $response_content = "<h1>403 Forbidden</h1>" .
            "<p>This proxy does not allow access to<br /></p>" .
            "<ul><li><a href='" . $request_url . "'>" . $request_url . "</a></em></li></ul>" .
            "<h2>Reason</h2>" .
            "Whitelist does not contain URL" .
            "<ul><li><a href='" . $request_url . "'>" . $request_url . "</a></li></ul>";
            $accessGranted = false;
        }
    }
}

if ($accessGranted) {
// append query string for GET requests
    if ($request_method == 'GET' && count($request_params) > 0 &&
       (!array_key_exists('query', $p_request_url) || empty($p_request_url['query']))) {
        $request_url .= '?' . http_build_query($request_params);
    }

    // let the request begin
    $ch = curl_init($request_url);
    array_push($request_headers, 'Expect:'); // Don't use 100-Expect
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	 // return response
    curl_setopt($ch, CURLOPT_HEADER, true);	   // enabled response headers
    // add data for POST, PUT or DELETE requests
    if ('POST' == $request_method) {
        $post_data = is_array($request_params) ? http_build_query($request_params) : $request_params;
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $post_data);
        $max = count($request_headers);
        for ($i = 0; $i < $max; $i++) {
            if (preg_match('/Content-Length:/', $request_headers[$i])) {
                $request_headers[$i] = 'Content-Length: ' . strlen($post_data); // Set corrected Content-Length
            }
        }
    } elseif ('PUT' == $request_method || 'DELETE' == $request_method) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
    }

    // Set multiple options for curl according to configuration
    if (is_array($curl_options) && 0 <= count($curl_options)) {
        curl_setopt_array($ch, $curl_options);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

    // retrieve response (headers and content) and errors
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

    curl_close($ch);

    if($curl_errno) {
        // generate response content and headers if error are encountered
        $response_content = "" . $curl_error . "\r\n" . $curl_errno_text[$curl_errno];
        $response_headers = "HTTP/1.1 503 Service Unavailable" . "\r\n" . "Status: 503 Service Unavailable";
    } else {
        // split response to header and content
        list($response_headers, $response_content) = preg_split('/(\r\n){2}/', $response, 2);
    }
}

// (re-)send the headers
$response_headers = preg_split('/(\r\n){1}/', $response_headers);
foreach ($response_headers as $key => $response_header) {
    // Rewrite the `Location` header, so clients will also use the proxy for redirects.
    if (preg_match('/^Location:/', $response_header)) {
        list($header, $value) = preg_split('/: /', $response_header, 2);
        $response_header = 'Location: ' . $_SERVER['REQUEST_URI'] . '?csurl=' . $value;
    }
    if (!preg_match('/^(Transfer-Encoding):/', $response_header)) {
        header($response_header, false);
    }
}

// finally, output the content
print($response_content);

function csajax_debug_message($message)
{
    if (true == CSAJAX_DEBUG) {
        print $message . PHP_EOL;
    }
}
