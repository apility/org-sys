<?php

namespace Apility\OrgSys;

use Apility\OrgSys\Payloads\dsNewName;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use SearchableName;

class Client
{

  /** @var GuzzleClient */
  private $base_url = "https://cloud.orgsys.com/Orgsys.WebApi/api/";

  /** @var string */
  private $username;

  /** @var string */
  private $password;

  /** @var CookePersistanceAdapter */
  public $cookieJar;
  public $client;


  public function __construct(string $username, string $password, string $integrationClient = "Apility", ?string $base_url = NULL, ?CookiePersistanceAdapter $cookieAdapter = NULL)
  {
    $this->base_url = $base_url ?? $this->base_url;
    $this->cookieJar = $cookieAdapter ?? new CookieAdapter(new CookieJar());
    $this->username = $username;
    $this->password = $password;
    $this->integrationClient = $integrationClient;
  }

  public function __call($method, $args)
  {
    $cookieJar = $this->cookieJar->getCookieJar();
    $saveNewCookieJar = false;


    if ($cookieJar->getCookieByName(".ASPXAUTH")) {
      $this->client = new GuzzleClient([
        'base_uri' => $this->base_url,
        // There is supposted to be a way to only use cookies when already authed.
        // But it does not seem to work as intended so im keeping the code here in case
        // it gets fixed
        'auth' => [$this->username, $this->password],
        'cookies' => $cookieJar
      ]);
    } else {
      $this->client = new GuzzleClient([
        'base_uri' => $this->base_url,
        'auth' => [$this->username, $this->password],
        'cookies' => $cookieJar
      ]);
      $saveNewCookieJar = true;
    }
    $response_c = call_user_func_array([$this->client, $method], $args);

    $response = (string)($response_c->getBody());
    $response_parsed = json_decode($response);

    if (!$response_parsed) {
      $response_parsed = new DOMDocument('1.0', 'utf8');
      $response_parsed->loadXML(str_replace("xmlns=\"Orgsys.Web.ReturnElements\"", "", $response));
    }
    $response_parsed = is_array($response_parsed) ? $response_parsed[0] : $response_parsed;
    if ($saveNewCookieJar) {
      $this->cookieJar->setCookieJar($cookieJar);
    }

    return $response_parsed;
  }

  public function searchNames($searchFields)
  {
    if ($searchFields instanceof SearchableName) {
      $searchFields = $searchFields->orgSysSearchKeys();
    }
    return $this->post('SearchName', ['json' => $searchFields]);
  }

  public function nameExists(array $searchFields)
  {
    $data = $this->post("SearchName", ['json' => $searchFields]);
    return $data->Status === 1;
  }

  /**
   * Create a Name entry in OrgSys
   */
  public function createName(array $name)
  {
    return $this->put("Name", [
      'query' => $name,
      'headers' => [
        'accepts' => 'application/json'
      ]
    ]);
  }

  public function createPayment($customer, $amount, $extra = [])
  {
    return $this->put('Payment', [
      'query' => array_merge([
        'nameId' => $customer->NameId,
        'amount' => $amount / 100,
      ])
    ]);
  }

  public function updateName(array $name)
  {
    return $this->post("Name", [
      'query' => $name,
    ]);
  }
}
