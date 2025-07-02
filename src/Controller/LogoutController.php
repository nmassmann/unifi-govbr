<?php

namespace App\Controller;

use App\Utils\Govbr;
use App\Utils\Net;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Twig\Environment;

use UniFi_API\Client;

class LogoutController extends AbstractController
{

    public function __construct(private ParameterBagInterface $params,
                                private LoggerInterface $logger) {}

    public function index(): Response {

        session_start();

        if (!isset($_SESSION['code_verifier']) && !isset($_SESSION['mac'])) {
            $this->logger->error('Erro: Code Verifier e/ou mac não encontrado na sessão ' . $e->getMessage());
            return $this->render('error.html.twig', [
                'mensagem' => 'Erro: Code Verifier e/ou mac não encontrado na sessão.'
            ]);
        }

        try {
            $site_id = $this->params->get('app.unifi.site_identifier');

            $unifi_connection = new Client(
                    $this->params->get('app.unifi.unifi_user'),
                    $this->params->get('app.unifi.unifi_pass'),
                    $this->params->get('app.unifi.controller_url'),
                    $site_id,
                    $this->params->get('app.unifi.controller_version'),
                    false
            );

            $loginresults = $unifi_connection->login();

            if (!$loginresults) {

                $this->logger->error('Falha no login no controlador UniFi');
                return [
                        'success' => false,
                        'message' => 'Falha no login no controlador UniFi'
                ];

            }

            $unauthorize = $unifi_connection->unauthorize_guest($_SESSION['mac']);

            if ($unauthorize) {

                session_destroy();   

                $this->logger->info('Cliente desconectado com sucesso.');
                    return $this->render('logout.html.twig');

            }else{
                $this->logger->error('Não foi possível desautorizar o usuario na controladora unifi: ');
                    return [
                    'success' => false,
                    'message' => 'Não foi possível desautorizar o usuario na controladora unifi:  '
                    ]; 

            }

        }catch (Exception $e) {

            $this->logger->error('General Exception in logout: ' . $e->getMessage());
            return [
                    'success' => false,
                    'message' => 'General Exception in logout: ' . $e->getMessage()
            ]; 

        }
    }
}



