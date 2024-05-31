<?php

namespace App\Services;

use DateTimeImmutable;


///////////////////// SERVICE INACTIF ////////////////////////

/**
 * Service de génération de tokens pour l'inscription/validation ou l'oubli de mot de passe
 * Penser à ajouter dans le .env une ligne JWT_SECRET='blablabla' (ce qu'on veut de compliqué) ainsi
 * que modifier le fichier config/services.yaml pour ajouter sous parameters, et indenté le couple clé-valeur suivant :
 *                                                                                  app.jwtsecret: '%env(JWT_SECRET)%'
 */
class JWTService
{
    // On génère le token
    public function generate(
        array  $header,
        array  $payload,
        string $secret,
        int    $validity = 10800
    ): string
    {
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $expiration = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $expiration;
        }
        //On encode en base64
        $base64header = base64_encode(json_encode($header));
        $base64payload = base64_encode(json_encode($payload));
        //On nettoie les valeurs encodées (retrait des +, / et =)
        $base64header = str_replace(['+', '/', '='], ['-', '_', ''], $base64header);
        $base64payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64payload);
        // On génère la signature
        $secret = base64_decode($secret);
        $signature = hash_hmac('sha256', $base64header . $base64payload, $secret, true);
        $base64signature = base64_encode($signature);
        $base64signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64signature);
        // On crée le token
        $jwt = $base64header . '.' . $base64payload . '.' . $base64signature;
        return $jwt;
    }
    // On vérifie la forme valide du token
    public function isValid(string $token): bool
    {
        return preg_match(
                '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token) === 1;
    }

    // On récupère le payload
    public function getPayload(string $token): array
    {
        //On démonte le token
        $array = explode('.', $token);
        // On décode le payload
        $payload = json_decode(base64_decode($array[1]), true);
        return $payload;
    }

    // On récupère le Header
    public function getHeader(string $token): array
    {
        //On démonte le token
        $array = explode('.', $token);
        // On décode le payload
        $header = json_decode(base64_decode($array[0]), true);
        return $header;
    }

    // On vérifie l'expiration du token
    public function isExpired(string $token): bool
    {
        $payload = $this->getpayload($token);
        $now = new DateTimeImmutable();
        return $payload['exp'] < $now->getTimestamp();
    }

    // On vérifie la signature du token
    public function checkSignature(string $token, string $secret)
    {
        // On récupère Header et Payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // On régénère un token
        $verifToken = $this->generate($header, $payload, $secret, 0);
        return $verifToken === $token;
    }
}