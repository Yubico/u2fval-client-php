<?php

/* Copyright (c) 2014 Yubico AB
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above
 *     copyright notice, this list of conditions and the following
 *     disclaimer in the documentation and/or other materials provided
 *     with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace U2fVal;

class Client {
  private $endpoint;
  private $auth;

  public function __construct($endpoint, $auth) {
    if(substr($endpoint, -1) != '/') {
      $endpoint .= '/';
    }
    $this->endpoint = $endpoint;
    $this->auth = $auth;
  }

  public static function withApiToken($endpoint, $apiToken) {
    return new Client($endpoint, new ApiTokenAuth($apiToken));
  }

  public static function withHttpAuth($endpoint, $username, $password, $type=CURLAUTH_DIGEST) {
    return new Client($endpoint, new HttpAuth($username, $password, $type));
  }

  public static function withNoAuth($endpoint) {
    return new Client($endpoint, new NoAuth());
  }

  private static function filtered($path, $filter) {
    if($filter !== NULL) {
      $path .= '?filter=' . $filter;
    }
    return $path;
  }

  private static function add_props($data, $props) {
    if(is_string($data)) {
      $data = json_decode($data, true);
    }
    if($props !== NULL) {
      $data['properties'] = $props;
    }
    return $data;
  }

  private function curl_begin($path, & $headers) {
    $ch = curl_init($this->endpoint . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $this->auth->authenticate($ch, $headers);
    return $ch;
  }

  private function curl_complete($ch, & $headers) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    if($res === false) {
      curl_close($ch);
      return array('errorCode' => -1, 'errorMessage' => 'Server unreachable');
    }
    $res = json_decode($res, true);

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($status >= 400) {
      $res = array('errorCode' => $status);
      if($status == 401) {
        $res['errorMessage'] = 'Invalid credentials';
      } else if($status == 404) {
        $res['errorMessage'] = 'Resource not found';
      } else {
        $res['errorMessage'] = 'Server responded: ' . $status;
      }
    }
    return $res;
  }

  private function curl_send($path, $data=null) {
    if(!function_exists('curl_init')) {
      return array('errorCode' => -1, 'errorMessage' => 'cURL not installed');
    }

    $headers = array();
    $ch = $this->curl_begin($path, $headers);
    if($data) {
      if(!is_string($data)) {
        $data = json_encode($data);
      }
      curl_setopt($ch, CURLOPT_POST, 1);
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Content-Length: ' . strlen($data);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    return $this->curl_complete($ch, $headers);
  }

  private function curl_delete($path) {
    if(!function_exists('curl_init')) {
      return array('errorCode' => -1, 'errorMessage' => 'cURL not installed');
    }

    $headers = array();
    $ch = $this->curl_begin($path, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    return $this->curl_complete($ch, $headers);
  }

  public function test_connection() {
    $resp = $this->curl_send('');
    if(!is_error($resp) && ($resp === NULL || !isset($resp['trustedFacets']))) {
      $resp = array(
        'errorCode' => -1,
        'errorMessage' => 'Invalid response from server'
      );
    }
    if(is_error($resp)) {
      return $resp;
    }

    return true;
  }

  public function list_devices($username, $filter=NULL) {
    return $this->curl_send(self::filtered($username . '/', $filter));
  }

  public function register_begin($username) {
    $resp = $this->curl_send($username . '/register');
    if(!is_error($resp)) {
      return json_encode($resp);
    }
    return $resp;
  }

  public function register_complete($username, $registerResponse, $properties=NULL, $filter=NULL) {
    $path = self::filtered($username . '/register', $filter);
    $registerData = array('registerResponse' => json_decode($registerResponse, true));
    return $this->curl_send($path, self::add_props($registerData, $properties));
  }

  public function unregister($username, $handle) {
    return $this->curl_delete($username . "/" . $handle);
  }

  public function auth_begin($username) {
    $resp = $this->curl_send($username . '/authenticate');
    if(!is_error($resp)) {
      return json_encode($resp);
    }
    return $resp;
  }

  public function auth_complete($username, $authenticateResponse, $properties=NULL, $filter=NULL) {
    $path = self::filtered($username . '/authenticate', $filter);
    $authData = array('authenticateResponse' => json_decode($authenticateResponse, true));
    return $this->curl_send($path, self::add_props($authData, $properties));
  }
}

function is_error($data) {
  if(is_string($data)) {
    $data = json_decode($data, true);
  }
  return isset($data['errorCode']);
}

class NoAuth {
  public function authenticate($ch, & $headers) {}
}

class ApiTokenAuth {
  public function __construct($apiToken) {
    $this->apiToken = $apiToken;
  }

  public function authenticate($ch, & $headers) {
    $headers[] = 'Authorization: Bearer ' . $this->apiToken;
  }
}

class HttpAuth {
  public function __construct($username, $password, $type=CURLAUTH_DIGEST) {
    $this->username = $username;
    $this->password = $password;
    $this->authtype = $authtype;
  }

  public function authenticate($ch, & $headers) {
    curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
    curl_setopt($ch, CURLOPT_HTTPAUTH, $this->authtype);
  }
}

?>
