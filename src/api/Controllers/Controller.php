<?php namespace Api\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    /** @var JsonResponse */
    protected $response;

    /** @var Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request) : void
    {
        $this->request = $request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response) : void
    {
        $this->response = $response;
    }

    /**
     * @param string $success
     * @param mixed $result
     * @param array $additionalAttributes
     * @param int $code
     * @return JsonResponse
     */
    protected function jsonResponse($success, $result, array $additionalAttributes = [], $code = 200) : JsonResponse
    {
        return $this->response
            ->setData(array_merge([
                'success' => $success,
                'result' => $result
            ], $additionalAttributes))
            ->setStatusCode($code);
    }
}
