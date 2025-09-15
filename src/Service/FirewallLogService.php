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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "udp://{$address}:{$port}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_UDP, true);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false || !empty($error)) {
            throw new \RuntimeException("Falha ao enviar mensagem para {$address}:{$port} - {$error}");
        }
    }
}
