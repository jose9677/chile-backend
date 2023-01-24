<?php 

namespace App\Helpers;

use App\Models\Config;
use App\Models\google_tokens;
//use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use DateInterval;
use DateTime;

class OauthHelper 
{

    private $client_id;
    private $client_secret;
    private $redirect_uri;

    private $provider;
    private $google_options;
    
    
    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->client_id = env('GMAIL_API_CLIENT_ID');
        $this->client_secret = env('GMAIL_API_CLIENT_SECRET');
        $this->redirect_uri = route('token.success');
        $this->google_options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        $params = [
            'clientId'      => $this->client_id,
            'clientSecret'  => $this->client_secret,
            'redirectUri'   => $this->redirect_uri,
            'accessType'    => 'offline'
        ];
        $this->provider = new Google($params);
    }

    public function getClientID()
    {   
        return $this->client_id;
    }

    public function getClientSecret()
    {
        return $this->client_secret;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getOptions()
    {
        return $this->google_options;
    }


    public static function getFullTokens()
    {
        $config = google_tokens::find(1)->toArray();
        
        $config['apigee_token_expired'] = (time() > $config['apigee_token_expires']);
        $config['apigee_refresh_token_expired'] = (time() > $config['apigee_refresh_token_expires']);

        return $config;
    }


    /**
     * check if access token is expired
     */
    public function accessTokenIsExpired()
    {
        $ct = self::getFullTokens();
        return $ct['apigee_token_expired'];
    }

    /**
     * check if refresh token is expired
     */
    public function refreshTokenIsExpired()
    {
        $ct = self::getFullTokens();
        return $ct['apigee_refresh_token_expired'];
    }


    private function refreshRefreshToken(){

    }

    public function generateAccessToken($code)
    {
        $tokenObj = $this->provider->getAccessToken(
            'authorization_code',
            [
                'code' => $code
            ]
            );

        if($tokenObj){
            $this->saveTokens($tokenObj->getToken(), $tokenObj->getExpires(), $tokenObj->getRefreshToken());
            return $tokenObj;
        }else
            return false;
    }

    public function getNewAccessToken($refresh_token)
    {
        
        $tokenObj = $this->provider->getAccessToken(
            'refresh_token',
            [
                'refresh_token' => $refresh_token
            ]
            );
    
        if($tokenObj){
            $this->saveTokens($tokenObj->getToken(), $tokenObj->getExpires());
            $this->updateRefreshTokenTimestamp($tokenObj->getExpires());
            return $tokenObj;
        }else
            return false;
    
    }

    private function saveTokens($token, $token_expires, $refresh_token = null)
    {
        if($token){
            google_tokens::where('id', 1)
            ->update(['apigee_token' => $token, 'apigee_token_expires' => $token_expires]);                     
        }
        if($refresh_token){
            $dt = new DateTime();
            $dt->setTimestamp($token_expires);
            $dt->add(new DateInterval('P199D'));
            
            google_tokens::where('id', 1)
            ->update(['apigee_refresh_token' => $refresh_token, 
            'apigee_refresh_token_expires' => $dt->getTimestamp()]);
        }
    }

    /**
     * As Google's refresh tokens lasts until user changes password the
     * only condition to not be void by server is to be used in the 200 days
     * from last refresh, so each time we get a new access token, the refresh token
     * restarts its 200 days time to expire.
     */
    private function updateRefreshTokenTimestamp($token_expires)
    {
        $dt = new DateTime();
        $dt->setTimestamp($token_expires);
        $dt->add(new DateInterval('P199D'));
        google_tokens::where('id', 1)->update(['apigee_refresh_token_expires' => $dt->getTimestamp()]);                 
    }

}