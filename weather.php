<?php
$weather = yahooWeather();
echo "<pre>" . print_r($weather,true) . "</pre>";
function buildBaseString($baseURI, $method, $params) {
    $r = array();
    ksort($params);
    foreach($params as $key => $value) {
        $r[] = "$key=" . rawurlencode($value);
    }
    return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
}
function buildAuthorizationHeader($oauth) {
    $r = 'Authorization: OAuth ';
    $values = array();
    foreach($oauth as $key=>$value) {
        $values[] = "$key=\"" . rawurlencode($value) . "\"";
    }
    $r .= implode(', ', $values);
    return $r;
}

function yahooWeather(){
	$url = 'https://weather-ydn-yql.media.yahoo.com/forecastrss';
	$app_id = 'g1PWqA7k';
	$consumer_key = 'dj0yJmk9V2cxY0o5OFN6V0RPJmQ9WVdrOVp6RlFWM0ZCTjJzbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD02NQ--';
	$consumer_secret = '69190101c7bc296a068103dcdc517730dea453f2';
	$query = array(
	    'woeid' => '2296407',
	    'format' => 'json',
	);
	$oauth = array(
	    'oauth_consumer_key' => $consumer_key,
	    'oauth_nonce' => uniqid(mt_rand(1, 1000)),
	    'oauth_signature_method' => 'HMAC-SHA1',
	    'oauth_timestamp' => time(),
	    'oauth_version' => '1.0'
	);
	$base_info = buildBaseString($url, 'GET', array_merge($query, $oauth));
	$composite_key = rawurlencode($consumer_secret) . '&';
	$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
	$oauth['oauth_signature'] = $oauth_signature;
	$header = array(
	    buildAuthorizationHeader($oauth),
	    'Yahoo-App-Id: ' . $app_id
	);
	$options = array(
	    CURLOPT_HTTPHEADER => $header,
	    CURLOPT_HEADER => false,
	    CURLOPT_URL => $url . '?' . http_build_query($query),
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_SSL_VERIFYPEER => false
	);
	$ch = curl_init();
	curl_setopt_array($ch, $options);
	$response = curl_exec($ch);
	curl_close($ch);
	// print_r($response);
	$return_data = json_decode($response);
	return $return_data->forecasts[0];
	//echo "<pre>" . print_r($return_data->forecasts[0],true) . "</pre>";
}