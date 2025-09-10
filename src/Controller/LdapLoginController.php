<?php

namespace App\Controller;

use App\Service\LdapAuthService;
use App\Service\UnifiService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LdapLoginController extends AbstractController
{
    public function __construct(
        private LdapAuthService $ldap,
        private UnifiService $unifi,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {}

    public function form(): Response
    {
        session_start();
        if (!isset($_SESSION['mac'])) {
            $this->logger->critical('Parâmetros wifi não recebidos (sessão sem MAC).');
            return $this->render('unifi/error.html.twig', [
                'mensagem' => 'Não foi possível autenticar. Acesse via controladora wifi.'
            ]);
        }

        return $this->render('unifi/ldap.html.twig');
    }

    public function authenticate(Request $request): Response
    {
        session_start();

        $username = trim((string) $request->request->get('username', ''));
        $password = (string) $request->request->get('password', '');

        if ($username === '' || $password === '') {
            return $this->render('unifi/ldap.html.twig', [
                'error' => 'Informe usuário e senha.'
            ]);
        }

        $result = $this->ldap->authenticate($username, $password);
        if (!$result['success']) {
            return $this->render('unifi/ldap.html.twig', [
                'error' => $result['message'] ?? 'Falha na autenticação LDAP.'
            ]);
        }

        $mac = $_SESSION['mac'] ?? '';
        $ap = $_SESSION['ap'] ?? null;

        try {
            $duration = (int) $this->params->get('app.unifi.auth_min');
            $auth = $this->unifi->authorizeGuest($mac, $duration, $ap ?: null);
            if (!$auth) {
                $this->logger->error('Falha ao autorizar convidado no UniFi para MAC ' . $mac);
                return $this->render('unifi/error.html.twig', [
                    'mensagem' => 'Não foi possível concluir a autenticação na rede.'
                ]);
            }

            $clientInfo = $this->unifi->statClient($mac);
            $userId = $clientInfo[0]->_id ?? null;
            if ($userId) {
                $this->unifi->setStationNote($userId, 'LDAP:' . ($result['display_name'] ?? $username));
            }

            return $this->render('unifi/success.html.twig', [
                'url_logout' => $this->params->get('app.url_logout'),
                'url_provider' => $this->params->get('app.govbr.url_provider')
            ]);
        } finally {
            $this->unifi->disconnect();
        }
    }
}


