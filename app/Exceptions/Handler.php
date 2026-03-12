<?php
namespace App\Exceptions;

use App\Services\ErrorLoggerService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Логируем все необработанные исключения
        $errorLog = ErrorLoggerService::log($e);

        return parent::render($request, $e);
    }
}
