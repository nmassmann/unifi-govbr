<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FirewallLogService
{
    public function __construct(
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {}

    public function sendAuthSuccess(string $username, string $ip): void
    {
        try {
            $paloAddr = $this->params->get('app.firewall.palo_addr');
            $paloPort = (int) $this->params->get('app.firewall.palo_port');

            if (empty($paloAddr) || empty($paloPort)) {
                $this->logger->warning('Configurações de firewall não definidas, pulando envio de log');
                return;
            }

            $message = '<47> Authentication success User:' . $username . ' Source:' . $ip;

            $this->sendSyslogMessage($message, $paloAddr, $paloPort);

            $this->logger->info('Log de autenticação enviado para firewall', [
                'username' => $username,
                'ip' => $ip,
                'address' => $paloAddr
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erro ao enviar log para firewall', [
                'username' => $username,
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendSyslogMessage(string $message, string $address, int $port): void
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === false) {
            throw new \RuntimeException('Falha ao criar socket UDP');
        }

        $result = socket_sendto($socket, $message, strlen($message), 0, $address, $port);
        socket_close($socket);

        if ($result === false) {
            throw new \RuntimeException("Falha ao enviar mensagem para {$address}:{$port}");
        }
    }
}
