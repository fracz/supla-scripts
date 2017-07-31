<?php

namespace suplascripts\controllers;

use Assert\InvalidArgumentException;
use Slim\Http\Request;
use Slim\Http\Response;
use suplascripts\controllers\exceptions\ApiException;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\controllers\exceptions\Http404Exception;
use suplascripts\models\HasApp;
use suplascripts\models\User;
use Throwable;

abstract class BaseController
{
    use HasApp;

    protected function response($content = null): Response
    {
        return $this->getApp()->response->withJson($content);
    }

    protected function request(): Request
    {
        return $this->getApp()->request;
    }

    /**
     * @return User
     */
    protected function getCurrentUser()
    {
        return $this->getApp()->getCurrentUser();
    }

    protected function ensureAuthenticated()
    {
        if (!$this->getCurrentUser()) {
            throw new Http403Exception();
        }
    }

    protected function ensureExists($object, $errorMessage = 'Element not found')
    {
        if (!$object) {
            throw new Http404Exception($errorMessage);
        }
        return $object;
    }

    protected function beforeAction()
    {
    }

    public function __call($methodName, $args)
    {
        if (count($args) == 3) { // request, response, args
            $action = $methodName . 'Action';
            try {
                $this->beforeAction();
                $response = call_user_func_array([&$this, $action], [$args[2]]);
            } catch (Throwable $e) {
                $response = $this->exceptionToResponse($e);
            }
            return $response;
        }
        throw new \BadMethodCallException("There is no method $methodName.");
    }

    private function exceptionToResponse(Throwable $e)
    {
        if ($e instanceof ApiException) {
            $this->getApp()->logger->warning('Action execution failed.', ['message' => $e->getMessage()]);
            return $this->response(['message' => $e->getMessage(), 'data' => $e->getData()])->withStatus($e->getCode());
        } else if ($e instanceof InvalidArgumentException) {
            $this->getApp()->logger->info('Validation failed.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->response([
                'message' => $e->getMessage(),
                'reason' => $e->getPropertyPath(),
            ])->withStatus(422);
        } else {
            error_log($e);
            $this->getApp()->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->response([
                'status' => 500,
                'message' => $e->getMessage(),
            ])->withStatus(500);
        }
    }
}
