<?php

namespace App\Exceptions;

use Exception;

class NotEnoughExercisesException extends Exception
{
    const HTTP_RESPONSE_CODE = 450;
}
