<?php

namespace App\Utils;

class Govbr
{
    // Função para gerar o Code Verifier
    public static function generateCodeVerifier($length = 128) {
        $randomBytes = random_bytes($length);
        return rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
    }

    // Função para gerar o Code Challenge
    public static function generateCodeChallenge($codeVerifier) {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}