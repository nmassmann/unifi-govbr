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

        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        // Verificando se o parâmetro id existe antes de usar
        if (null !== $request->query->get('id')) {
            $mac = htmlspecialchars($request->query->get('id'));
            $session->set('mac', $mac);
        } else {
            $this->logger->critical('Erro: Parâmetros wifi não recebidos. Acesse via controladora wifi. ');
            return $this->render('unifi/error.html.twig', [
               'mensagem' => 'Não foi possível autenticar. Parâmetros wifi não recebidos. Acesse via controladora wifi.'
            ]);
        }

        $session->set('ap', $request->query->get('ap') ?? '');
        $session->set('t', $request->query->get('t') ?? '');
        $session->set('url', $request->query->get('url') ?? '');

        // Gerar o Code Verifier e armazenar na sessão
        $codeVerifier = Govbr::generateCodeVerifier();
        $session->set('code_verifier', $codeVerifier);

        // Gerar o Code Challenge
        $codeChallenge = Govbr::generateCodeChallenge($codeVerifier);
        $session->set('code_challenge', $codeChallenge);

        return $this->render('unifi/login.html.twig', [
                'url_login' => $this->params->get('app.url_login')
        ]);

    }

}

