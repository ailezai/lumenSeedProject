<?php

namespace AiLeZai\Lumen\Framework\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use AiLeZai\Common\Lib\Cat\CAT;
use AiLeZai\Common\Lib\Log\LOG;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiHandler extends ExceptionHandler
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
        \AiLeZai\Lumen\Framework\Exceptions\ValidationException::class,
        \AiLeZai\Lumen\Framework\Exceptions\NotFoundHttpException::class,
        ApiException::class,
        DataNotFoundException::class,
        ServerApiException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        try {
            $this->reportExceptionToCAT($e);
        } catch (\Exception $exception) {
            LOG::error(sprintf('%s~%s %s:%d',
                $exception->getCode(),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine())
            );
        }
    }

    /**
     * report an exception to cat
     *
     * @param Exception $e
     *
     * @return void
     */
    protected function reportExceptionToCAT(Exception $e)
    {
        $currentTime = (new \DateTime())->format('Ymd H:i:s.u');
        $runTime = round((microtime(true) - LUMEN_START_TIME) * 1000);
        $currentIp = empty($_SERVER['REMOTE_ADDR']) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];

        $transaction = CAT::fillDataTemplate(
            'trace_id:' . LOG::getTraceId(),
            get_class($e),
            '',
            $e->getMessage(),
            'error',
            $currentTime,
            $runTime
        );

        $data = CAT::fillMsgDataTemplate(
            $transaction,
            [],
            ['X-Trace-Id' => LOG::getTraceId()],
            $currentIp,
            env('APP_NAME', 'lumen_seed_project')
        );
        CAT::buildMsgData($data);

        CAT::sendMsg();
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e)
    {
        // 本地调试，打开报错
        if (env('APP_ENV', 'pdt') === 'loc') {
            return parent::render($request, $e);
        }

        // 将 NotFoundHttpException 调整成自定义的 NotFoundHttpException
        if ($e instanceof NotFoundHttpException) {
            $e = new \AiLeZai\Lumen\Framework\Exceptions\NotFoundHttpException();
        }

        $response = $this->renderExceptionResponse($request, $e);

        // 记录错误日志
        try {
            LOG::error(build_req_resp_log($request, $response, [], $e));
        } catch (\Exception $exception) {
            LOG::error(sprintf('%s~%s %s:%d',
                    $exception->getCode(),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine())
            );
        }

        return $response;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function renderExceptionResponse($request, Exception $e)
    {
        return $this->apiExceptionResponse($e);
    }

    /**
     * @param Exception $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function ajaxExceptionResponse(Exception $e)
    {
        if (method_exists($e, 'ajaxExceptionResponse')) {
            $response = $e->ajaxExceptionResponse();
            if (!empty($response) && is_object($response) &&
                ($response instanceof Response || $response instanceof JsonResponse)) {
                return $response;
            }
        }

        return ajax_response()->ajaxFailureResponse('未知错误');
    }

    /**
     * @param Exception $e
     *
     * @return \Illuminate\Http\Response
     */
    protected function adminExceptionResponse(Exception $e)
    {
        if (method_exists($e, 'adminExceptionResponse')) {
            $response = $e->adminExceptionResponse();
            if (!empty($response) && is_object($response) &&
                ($response instanceof Response || $response instanceof JsonResponse)) {
                return $response;
            }
        }

        $view = view('errors.500')
            ->with('msg', $e->getMessage());

        return response($view, 500);
    }

    /**
     * @param Exception $e
     *
     * @return \Illuminate\Http\Response
     */
    protected function apiExceptionResponse(Exception $e)
    {
        if (method_exists($e, 'apiExceptionResponse')) {
            $response = $e->apiExceptionResponse();
            if (!empty($response) && is_object($response) &&
                ($response instanceof Response || $response instanceof JsonResponse)) {
                return $response;
            }
        }

        return api_response()->errorResponse($e);
    }
}