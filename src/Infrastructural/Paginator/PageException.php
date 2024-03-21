<?php

namespace App\Infrastructural\Paginator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PageException extends BadRequestHttpException
{
}
