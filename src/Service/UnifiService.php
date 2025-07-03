<?php

namespace App\Service;

use UniFi_API\Client;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UnifiService
{

    public function __construct(private ParameterBagInterface $params,
                                private LoggerInterface $logger) {}

    public function connect(): ?Client 
    {
        $client = new Client(
            $this->params->get('app.unifi.unifi_user'),
            $this->params->get('app.unifi.unifi_pass'),
            $this->params->get('app.unifi.controller_url'),
            $this->params->get('app.unifi.site_identifier'),
            $this->params->get('app.unifi.controller_version'),
            false
        );

        if (!$client->login()) {
            $this->logger->error('Falha no login no controlador UniFi');
            return null;
        }

        return $client;
    }
}