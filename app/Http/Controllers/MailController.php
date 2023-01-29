<?php

namespace App\Http\Controllers;

use App\Helpers\OauthHelper;
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
    private $mailer;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->email            = 'josedarkrai@gmail.com'; 
        $this->name             = 'la reina';
        $this->client_id        = env('GMAIL_API_CLIENT_ID');
        $this->client_secret    = env('GMAIL_API_CLIENT_SECRET');
        $this->provider         = new Google(
            [
                'clientId'      => $this->client_id,
                'clientSecret'  => $this->client_secret
            ]
        );
        $this->mailer = new PHPMailer(true);

        $o = new OauthHelper;
        $token = $o->getFullTokens();
        if($token['apigee_token_expired']){
            $otoken = $o->getNewAccessToken($token['apigee_refresh_token']);
            $token = $otoken->getToken();
        }else {
            //$this->token = $request->get('oauth_token');
            $token = $token['apigee_refresh_token'];
        }

        $this->mailer->isSMTP();
        $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->Port = 465;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->SMTPAuth = true;
        $this->mailer->AuthType = 'XOAUTH2';
        $this->mailer->setOAuth(
            new OAuth(
                [
                    'provider'          => $this->provider,
                    'clientId'          => $this->client_id,
                    'clientSecret'      => $this->client_secret,
                    'refreshToken'      => $token,
                    'userName'          => $this->email
                ]
            )
        );
        
    }

    /**
     * Send Email via PHPMailer Library
     */
    public function doSendEmail(Request $request)
    {
            $mail = $this->mailer;
            $to = $request->get('email');
            if(!$to)
                abort(500, 'Email is required');

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Prueba de correo';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $body = view('emails.asignarVisitador', ['names' => 'Ejemplo', 'lastname' => 'Example', 'id' => '99'])->render();
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

    public function register($to, $params)
    {
            $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Clave de un solo uso - Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $body = view('emails.registro', ['otp' => $params['otp']])->render();
            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function passwordEmployee($to, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Registro de Empleado - Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.claveEmpleado', ['rut' => $params['rut'], 
            'api_token' => $params['api_token'], 'app_url' => $params['app_url']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function newOTP($new_otp, $to, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Clave de un solo uso '.$new_otp.' - Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.reenviarOtp', ['otp' => $params['otp']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }       
    }

    public function registerNeighbor($to)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Registro Exitoso - Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.registroVecinoExitoso')->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }           
    }

    public function reprogramRequestDate($to, $id, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Petición de Reprogramación - Solicitud Nro .'.$id.'- Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.reprogramarCita', ['names' => $params['names'], 
            'lastname' => $params['lastname'], 'id' => $params['id']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }   
    }

    public function visitDate($to, $id, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Cita Asignada - Solicitud Nro. '.$id;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.asignarSolicitud', ['id' => $params['id'], 
            'names' => $params['names'], 'lastname' => $params['lastname'], 
            'visit_date' => $params['visit_date']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }          
    }

    public function confirmVisit($to, $id, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Cita confirmada - Solicitud Nro.'.$id.'- Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.confirmarCita', ['names' => $params['names'], 
            'lastname' => $params['lastname'], 'id' => $params['id']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }       
    }

    public function addVisitor($to, $id, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Se te ha asignado la solicitud Nro.'.$id.'- Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.asignarVisitador', ['names' => $params['names'], 
            'lastname' => $params['lastname'], 'id' => $params['id']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }         
    }

    public function rejectRequest($to, $id, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Su solicitud Nro. '.$id.' ha sido Rechazada';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.rechazarSolicitud', ['id' => $params['id']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }    
    }

    public function approve_Request($to, $id, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Su solicitud Nro. '.$id.' ha sido Rechazada';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.aprobarSolicitud', ['id' => $params['id']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }       
    }

    public function validate_User($to, $params)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = "Recuperar Contraseña - Salud a Tu Alcance";
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.validar', [ 'api_token' => $params['api_token'],
            'app_url' => $params['app_url']])->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function newPassword($to)
    {
        $mail = $this->mailer;

        try{
            $mail->setFrom($this->email, $this->name);
            $mail->addAddress($to);
            $mail->Subject = 'Cambio de Contraseña Exitoso - Salud a Tu Alcance';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $body = view('emails.cambioClave')->render();

            $mail->msgHTML($body);
            if( $mail->send() ) {
                return true;
            } else {
                return false;
            }
        } catch(IdentityProviderException $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
}