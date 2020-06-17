<?php

namespace Apility\OrgSys;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;

class CookieAdapter implements CookiePersistanceAdapter
{
  private $cookieJar;

  public function __construct(CookieJarInterface $cookieJar)
  {
    $this->cookieJar = $cookieJar;
  }

  public function getCookieJar(): CookieJarInterface
  {
    return $this->cookieJar ?? new CookieJar();
  }

  public function setCookieJar(CookieJarInterface $cookieJar)
  {
    $this->cookieJar = $cookieJar;
  }
}
