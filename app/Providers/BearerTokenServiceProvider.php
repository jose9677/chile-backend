<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class BearerTokenServiceProvider extends ServiceProvider
{
    public static function getUserByBearerToken($token)
    {
        if (! str_contains($token, '.')) {
            return false;
        }

        list($header, $payload, $signature) = explode('.', $token);
        $jsonToken = base64_decode($payload);
        $arrayToken = json_decode($jsonToken, true);

        $names = DB::table('users')->where('rut', $arrayToken['rut'])->value('names');
        $lastname = DB::table('users')->where('rut', $arrayToken['rut'])->value('lastname');
        $full_name = $names.' '.$lastname;

        return $full_name;
    }
}
