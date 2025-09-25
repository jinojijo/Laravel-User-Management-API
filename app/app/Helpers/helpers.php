<?php

if (! function_exists('normalizeEmail')) {
    function normalizeEmail(string $email): string
    {
        $email = mb_strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "email not valid";
        }
        
        [$local, $domain] = explode('@', $email, 2);

        if (in_array($domain, ['gmail.com', 'googlemail.com'])) {
            $local = str_replace('.', '', $local);
            $local = preg_replace('/\+.*$/', '', $local);
        }

        return $local.'@'.$domain;
    }
}