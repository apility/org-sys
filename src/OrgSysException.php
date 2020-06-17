<?php

namespace Apility\OrgSys;

class OrgSysException extends \Exception
{

  private $statusCode;
  public function __construct(int $statusCode, string $statusMessage)
  {
    $this->statusCode = $statusCode;
    parent::__construct($statusMessage);
  }

  public function getStatusCode(): ?int
  {
    return $this->statusCode;
  }
}
