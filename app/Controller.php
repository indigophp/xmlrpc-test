<?php

/*
 * This file is part of the Indigo XML-RPC Test package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Orno\Di\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Main controller
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Controller
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Index action displaying the main site
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $response->setContent(file_get_contents(__DIR__.'/views/index.html'));

        return $response;
    }

    /**
     * Lists the methods of an endpoint if it supports system methods
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function listMethods(Request $request, Response $response, array $args)
    {
        $client = $this->getClient($request);
        $methods = [];

        try {
            $methods = $client->call('system.listMethods');

            $methods = array_map(function($method) {
                return [
                    'name'     => $method,
                    'longName' => $method,
                ];
            }, $methods);
        } catch (\Exception $e) {
            // let this one pass
        }

        $response->setContent(json_encode($methods));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns info about a selected method if the server supports it
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function getMethodInfo(Request $request, Response $response, array $args)
    {
        $client = $this->getClient($request);

        $description = $client->call('system.methodHelp', $args);
        $signature = $client->call('system.methodSignature', $args);
        $returnType = array_shift($signature);

        $response->setContent(json_encode([
            'description' => $description,
            'signature'   => $signature,
            'returnType'  => $returnType,
        ]));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function call(Request $request, Response $response, array $args)
    {
        $client = $this->getClient($request);

        $callResponse = $client->call($args['method'], json_decode($request->get('params', '{}'), true));

        $callResponse = ['response' => $callResponse];

        $response->setContent(json_encode($callResponse));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Creates a client
     *
     * @param Request $request
     *
     * @return \fXmlRpc\Client
     */
    protected function getClient(Request $request)
    {
        $client = $this->container->get('xmlrpc');

        $client->setUri($request->get('uri'));

        return $client;
    }
}
