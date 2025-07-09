<?php

namespace App\Controller;

use App\Utils\Govbr;
use App\Utils\Net;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{

    public function __construct(private ParameterBagInterface $params,
                                private LoggerInterface $logger) {}

    public function index(Request $request): Response{

        session_start();

        // Verificando se o parâmetro id existe antes de usar
        if (null !== $request->query->get('id')) {
            $mac = htmlspecialchars($request->query->get('id'));
            $_SESSION['mac'] = $mac;
        } else {
            $this->logger->critical('Erro: Parâmetros wifi não recebidos ');
            return $this->render('error.html.twig', [
               'mensagem' => 'Não foi possível autenticar. Parâmetros wifi não recebidos.'
            ]);
        }

        $_SESSION['ap']  = $request->query->get('ap')  ?? '';
        $_SESSION['t']   = $request->query->get('t')  ?? '';
        $_SESSION['url'] = $request->query->get('url') ?? '';

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

