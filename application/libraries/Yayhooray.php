<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Yayhooray
{
  public $logged_in = FALSE;

  private $cookie_jar = '/var/www/yayhooray.net/cookiejar.txt';
  private $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';
  private $page_data = array();
  public $meta = array();

  function __construct() {}

  public function login($username, $password)
  {
    $fields = array(
      'username' => urlencode($username),
      'password' => urlencode($password),
      'action' => 'login'
    );

    $fields_string = '';

    foreach($fields as $key=>$value) {
      $fields_string .= $key.'='.$value.'&';
    }
    rtrim($fields_string, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.yayhooray.com/");
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

    $result = curl_exec($ch);

    $this->logged_in = TRUE;
    $this->meta['uid'] = $this->get_uid();

    curl_close($ch);
  }

  public function send_message($to, $subject, $message)
  {
    if ($this->logged_in === FALSE) {
      return FALSE;
    }

    $fields = array(
      'action' => 'sendmessage',
      'uid' => urlencode($this->meta['uid']),
      'to' => urlencode($to),
      'subject' => urlencode($subject),
      'message' => urlencode($message)
    );

    $fields_string = '';

    foreach($fields as $key=>$value) {
      $fields_string .= $key.'='.$value.'&';
    }
    rtrim($fields_string,'&');

    // take the specified action on the user
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.yayhooray.com/message.php");
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

    // sending!
    $result = curl_exec($ch);

    // close the curl handle
    curl_close($ch);

    return TRUE;
  }

  /*
    |--------------------------------------------------------------------------
    | get_uid()
    |--------------------------------------------------------------------------
    | @returns	boolean
    | @access	public
    |--------------------------------------------------------------------------
  */
  public function get_uid()
  {
    if (!$this->logged_in) {
      return FALSE;
    }

    // new page request, for the buddies (getting the secret code)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.yayhooray.com/message");
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // get the page source
    $data = curl_exec($ch);

    // look through the lines for the secret code
    foreach(explode("\n", $data) as $line) {
      if (strpos($line, 'name="uid"')) {
        preg_match('/([0-9a-f]{32})/', $line, $matches);
        return $matches[0];
      }
    }

    // clear up some memory
    unset($lines);
  }

}