<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
		/**if ($exception instanceof QueryException) {
        
			return response()->json([
							'status' => '500',
							//'message' => 'Data Gagal di Import. Format masih ada yang salah. Silahkan diperiksa kembali.'
							'message' => 'Data Gagal di Import. '.$exception->getMessage()
						])->setStatusCode(500);
		}		
		if ($exception instanceof NotFoundHttpException) {
        
			return response()->json([
							'status' => '404',
							'message' => 'Data Tidak Ditemukan. '.$exception->getMessage()
						])->setStatusCode(404);
		}*/

        return parent::render($request, $exception);
		
    }
}
