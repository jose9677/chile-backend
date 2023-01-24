<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Http\Controllers\OAuthController;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;


/**
 * @see https://github.com/qalbit/laravel-phpmailer-google-oauth
 */

class MailController extends Controller
{

    private $email;
    private $name;
    private $client_id;
    private $client_secret;
    private $token;
    private $provider;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->email            = 'ricardo@juegopegamax.com'; 
        $this->name             = 'Ricardo - Soporte Pegamax';
        $this->client_id        = env('GMAIL_API_CLIENT_ID');
        $this->client_secret    = env('GMAIL_API_CLIENT_SECRET');
        $this->provider         = new Google(
            [
                'clientId'      => $this->client_id,
                'clientSecret'  => $this->client_secret
            ]
        );

    }

    /**
     * Send Email via PHPMailer Library
     */
    public function doSendEmail(Request $request)
    {

        $o = new OAuthController;
        $token = $o->getFullTokens();
        if($token['apigee_token_expired'])
            $token = $o->getNewAccessToken($token['apigee_refresh_token']);

        //$this->token = $request->get('oauth_token');
        $this->token = $token['apigee_refresh_token'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';
            $mail->setOAuth(
                new OAuth(
                    [
                        'provider'          => $this->provider,
                        'clientId'          => $this->client_id,
                        'clientSecret'      => $this->client_secret,
                        'refreshToken'      => $this->token,
                        'userName'          => $this->email
                    ]
                )
            );

            $to = $request->get('email');
            if(!$to)
                abort(500, 'Email is required');

            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Prueba de correo';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $body = 'Hola,<br><br>Si puede leer este mensaje, el sistema de correo de <b>Comunicaciones Pegamax</b> ha logrado enviarlo correctamente a través de los servicios de Gmail Workspace.<br><br>Gracias por su atenci&oacute;n.<br><br>Por favor no responda a esta cuenta de correo ya que no es monitoreada.<br><br><b>Soporte Pegamax</b>';
            $mail->msgHTML($body);
            $mail->AltBody = 'Si puede leer este mensaje, el sistema de correo de Comunicaciones Pegamax ha logrado enviarlo correctamente. Saludos';
            if( $mail->send() ) {
                return redirect()->back()->with('success', 'Correo enviado satisfactoriamente. Revise el buzón indicado.');
            } else {
                return redirect()->back()->with('error', 'Hubo un error enviando el mensaje.');
            }
        } catch(IdentityProviderException $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());            
        } catch(Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}