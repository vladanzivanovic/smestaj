<?php
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 5/5/2017
 * Time: 11:16 PM
 */
namespace SiteBundle\EventListeners;

use Monolog\Logger;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Exceptions\ApplicationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApplicationExceptionListener
{
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Listen on Kernel Exception and generate default message for user
     * @param GetResponseForExceptionEvent $event
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $responseData = [
            'success' => false,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace(),
        ];

        $response = new JsonResponse();

        $this->logError($exception);

        if($exception instanceof ApplicationException){
            $responseData['message'] = $exception->getMessage();
            $responseData['trace'] = $exception->getTrace();
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($responseData));
        $event->setResponse($response);
    }

    /**
     * Set error in log file.
     * @param \Exception $exception
     */
    private function logError(\Exception $exception)
    {
        $backtrace = $exception->getTrace();

        $raw = '';
        foreach ($backtrace as $key => $row) {
            if(isset($row['file'])){
                $raw.= $row['file'];
            };
            if(isset($row['line'])){
                $raw.= " (Line: {$row['line']}) ".PHP_EOL;
            };
        };
        $this->logger->error($exception->getMessage() ,array('Trace: ' => $raw));
    }
}