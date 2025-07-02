<?php

namespace App\Controller;

use App\Utils\Govbr;
use App\Utils\Net;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{

    public function __construct(private ParameterBagInterface $params,
                                private LoggerInterface $logger) {}

    public function index(): Response {


        session_start();

        if (! isset($_SESSION['code_verifier'])) {
            $this->logger->error('Erro: Code Verifier não encontrado na sessão ' . $e->getMessage());
            return $this->render('error.html.twig', [
                'mensagem' => 'Erro: Code Verifier não encontrado na sessão.'
            ]);
        }

        // Paramentos de configuração fornecidos pelo GOV.BR

        // URL do provedor de auth
        $URL_PROVIDER=$this->params->get('app.govbr.url_provider');

        // URL dos serviços
        $URL_SERVICE=$this->params->get('app.govbr.url_service');

        // URL de retorno depois de autenticar
        $REDIRECT_URI=$this->params->get('app.govbr.redirect_uri');

        // Escopos autorizados 
        $SCOPES=$this->params->get('app.govbr.scopes');

        // ID do cliente fornecido pelo GOV.BR
        $CLIENT_ID=$this->params->get('app.govbr.client_id'); 

         // Chave secreta fornecida pelo GOV.BR
        $SECRET=$this->params->get('app.govbr.secret');

        // Identificacao de qual MAC ADDRESS esta solicitando acesso via gov.br
        $STATE=$_SESSION['mac'];
        $CODE_CHALLENGE=$_SESSION['code_challenge'];

        $url = $URL_PROVIDER."/authorize?response_type=code&client_id=$CLIENT_ID&scope=$SCOPES&redirect_uri=$REDIRECT_URI&state=$STATE&code_challenge=$CODE_CHALLENGE&code_challenge_method=S256";

        $this->logger->info('Verificação de segurança executada');

        $client = HttpClient::create([
            'headers' => ['User-Agent' => 'Unifi-govbr/1.0'],
        ]);

        //$response = $client->request('GET', $url);
        header('Location: '.$url, true, 302);
        exit;

    }
}



