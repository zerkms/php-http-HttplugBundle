<?php

namespace Http\HttplugBundle\Collector;

use Http\Client\Common\FlexibleHttpClient;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\HttplugBundle\ClientFactory\ClientFactory;

/**
 * The ProfileClientFactory decorates any ClientFactory and returns the created client decorated by a ProfileClient.
 *
 * @author Fabien Bourigault <bourigaultfabien@gmail.com>
 *
 * @internal
 */
class ProfileClientFactory implements ClientFactory
{
    /**
     * @var ClientFactory|callable
     */
    private $factory;

    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @param ClientFactory|callable $factory
     * @param Collector              $collector
     * @param Formatter              $formatter
     */
    public function __construct($factory, Collector $collector, Formatter $formatter)
    {
        if (!$factory instanceof ClientFactory && !is_callable($factory)) {
            throw new \RuntimeException(sprintf('First argument to ProfileClientFactory::__construct must be a "%s" or a callable.', ClientFactory::class));
        }
        $this->factory = $factory;
        $this->collector = $collector;
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function createClient(array $config = [])
    {
        $client = is_callable($this->factory) ? call_user_func($this->factory, $config) : $this->factory->createClient($config);

        if (!($client instanceof HttpClient && $client instanceof HttpAsyncClient)) {
            $client = new FlexibleHttpClient($client);
        }

        return new ProfileClient($client, $this->collector, $this->formatter);
    }
}