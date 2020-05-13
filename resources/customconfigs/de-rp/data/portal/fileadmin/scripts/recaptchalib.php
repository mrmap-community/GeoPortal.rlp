<?php
  
/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 
 * @return string - The HTML to be embedded in the user's form.
 */
function recaptcha_get_html ($pubkey)
{
  if ($pubkey == null || $pubkey == '') {
    die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
  }
  return '
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="'.$pubkey.'"></div>
  ';
}



/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $response
  * @return bool
  */
function recaptcha_check_answer ($privkey)
{
  if ($privkey == null || $privkey == '') {
    die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
  }

  $response = $_POST["g-recaptcha-response"];

  //discard spam submissions
  if ($response == null || strlen($response) == 0) {
          return false;
  }

  $url = 'https://www.google.com/recaptcha/api/siteverify';
  
  $data = array(
    'secret' => $privkey,
    'response' => $response,
    'remoteip' => $_SERVER["REMOTE_ADDR"]
  );
  $options = array(
    'http' => array (
      'method' => 'POST',
      'content' => http_build_query($data),
      'proxy' => 'tcp://{PROXY_IP}:{PROXY_PORT}',
      'request_fulluri' => true,
    )
  );
  $context  = stream_context_create($options);
  $verify = file_get_contents($url, false, $context);
  $captcha_success=json_decode($verify);
  return $captcha_success->success;

}



?>
