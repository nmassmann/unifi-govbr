<?php

namespace App\Utils;

class Net{

    public static function getClientIp() {
        $ip = '';
        
        // Verifica o IP compartilhado na internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } 
        // Verifica o IP de proxy reverso
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } 
        // IP padrão (pode ser o IP do proxy e não do cliente)
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Pode retornar múltiplos IPs no X_FORWARDED_FOR
        $ips = explode(',', $ip);
        $ip = trim($ips[0]);
        
        // Valida o IP
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}