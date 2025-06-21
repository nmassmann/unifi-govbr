<?php

namespace App\Controller;

use App\Utils\Govbr;
use App\Utils\Net;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{

    public function __construct(private ParameterBagInterface $params,
                                private LoggerInterface $logger) {}

    public function index(): Response{

        session_start();

        // Verificando se o parâmetro id existe antes de usar
        if (isset($_GET['id'])) {
            $mac = htmlspecialchars($_GET['id']);
            $_SESSION['mac'] = $mac;
        } else {
            $this->logger->error('Erro: Parâmetros wifi não recebidos ');
            return $this->render('error.html.twig', [
               'mensagem' => 'Não foi possível autenticar. Parâmetros wifi não recebidos.'
            ]);
        }

        $_SESSION['ap']  = $_GET['ap']  ?? '';
        $_SESSION['t']   = $_GET['t']   ?? '';
        $_SESSION['url'] = $_GET['url'] ?? '';

        // Gerar o Code Verifier e armazenar na sessão
        $codeVerifier = Govbr::generateCodeVerifier();
        $_SESSION['code_verifier'] = $codeVerifier;

        // Gerar o Code Challenge
        $codeChallenge = Govbr::generateCodeChallenge($codeVerifier);
        $_SESSION['code_challenge'] = $codeChallenge;

        return $this->render('login.html.twig', [
                'url_login' => $this->params->get('app.url_login')
        ]);

    }

}

