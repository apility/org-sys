<?php

namespace Apility\OrgSys;

use GuzzleHttp\Cookie\CookieJarInterface;

interface CookiePersistanceAdapter
{
  function getCookieJar(): CookieJarInterface;
  function setCookieJar(CookieJarInterface $cookieJar);
}
