<?php

namespace NickMous\Binsta\Internals\Repositories;

use NickMous\Binsta\Internals\Entities\Entity;

abstract class BaseRepository
{
    abstract public function getEntityByParameter(string $parameterValue): Entity;
}
