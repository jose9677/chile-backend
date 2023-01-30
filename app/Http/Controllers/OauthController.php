<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use App\Models\Config;
use App\Helpers\OauthHelper;


class OauthController extends Controller
{
    

    public function index()
    {
        return view('index-oauth');
    }

    /**
     * Generate url to retreive token
     */
    public function doGenerateToken()
    {

        // Create Google Provider
        /*$o = new OauthHelper;
        $provider = $o->getProvider();*/

        $provider = OauthHelper::getProvider();

        $redirect_uri = $provider->getAuthorizationUrl(OauthHelper::getOptions());
        return redirect($redirect_uri);
    }

    /**
     * refresh the access token 
     */
    public function doRefreshAccessToken()
    {
        try{

            $o = new OauthHelper;
            $ct = $o->getFullTokens();
            $ret = $o->getNewAccessToken($ct['apigee_refresh_token']);
                
            return redirect('email/oauth')->with('success-renew', 'El token ha sido renovado satisfactoriamente');

        } catch(IdentityProviderException $e) {
            return redirect('email/oauth')->with('error', 'Error: ' . $e->getMessage());
        } catch(Exception $e) {
            return redirect('email/oauth')->with('error', 'Error: ' . $e->getMessage());
        }

    }

    /**
     * Retreive Token 
     */
    public function doSuccessToken(Request $request)
    {
        $code = $request->get('code');
        $o = new OauthHelper;
        $provider = $o->getProvider();

        try {
            // Generate Token From Code 
            $tokenObj = $o->generateAccessToken($code); 
            $token = $tokenObj->getToken();
            $refresh_token = $tokenObj->getRefreshToken();
                
            if( 
                ($refresh_token != null && !empty($refresh_token)) || 
                ($token != null && !empty($token)) ){
                //session()->flash('success', 'El token ha sido obtenido satisfactoriamente');
                return redirect('email/oauth')->with('success', 'El token ha sido obtenido satisfactoriamente');
            } else {
                return redirect('email/oauth')->with('error', 'El token no pudo ser obtenido. Revise el inicio de sesi&oacute;n en la cuenta de Google');
            }

        } catch(IdentityProviderException $e) {
            return redirect('email/oauth')->with('error', 'Error: ' . $e->getMessage());
        } catch(Exception $e) {
            return redirect('email/oauth')->with('error', 'Error: ' . $e->getMessage());
        }
    }   
}