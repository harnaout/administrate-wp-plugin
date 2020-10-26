<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Administrate\PhpSdk\Oauth\Activator;

/**
 * ActivatorTest
 *
 * @package Administrate\PhpSdk
 * @author Ali Habib <ahh@administrate.co>
 * @author Jad Khater <jck@administrate.co>
 */
final class ActivatorTest extends TestCase
{
    public function testGetWeblinkPortalToken(): void
    {
         $weblinkActivationParams = $this->getWeblinkActivationParams();

         $activate = new Activator($weblinkActivationParams);
         $response = $activate->getWeblinkPortalToken();
         $this->assertObjectHasAttribute('portal_token', $response['body'], 'The returned object does not have portal_token attribute');
         $this->assertTrue($response['body']->portal_token != "");
    }

    public function testHandleAuthorizeCallback(): void
    {
        //Authorzation code
        $authorizationCode = $_GET['authorizationCode'];

        // Core API Params
        $coreApiActivationParams = $this->getCoreApiActivationParams();
        
        $activationObj = new Activator($coreApiActivationParams);
        $response = $activationObj->handleAuthorizeCallback(array( 'code' => $authorizationCode ));

        $this->assertObjectHasAttribute('access_token', $response['body'], 'The returned object does not have access_token attribute');
        $this->assertObjectHasAttribute('refresh_token', $response['body'], 'The returned object does not have refresh_token attribute');
        $this->assertTrue($response['body']->access_token != "");
        $this->assertTrue($response['body']->refresh_token != "");
    }
    public function testRefreshToken(): void
    {
        // Core API Params
        $coreApiActivationParams = $this->getCoreApiActivationParams();
        
        $activationObj = new Activator($coreApiActivationParams);
        $response = $activationObj->refreshTokens($coreApiActivationParams['refreshToken']);

        $this->assertObjectHasAttribute('access_token', $response['body'], 'The returned object does not have access_token attribute');
        $this->assertObjectHasAttribute('refresh_token', $response['body'], 'The returned object does not have refresh_token attribute');
        $this->assertTrue($response['body']->access_token != "");
        $this->assertTrue($response['body']->refresh_token != "");
    }

    public function getCoreApiActivationParams()
    {
        return array(
            'clientId' => $_GET['clientId'],
            'clientSecret' => $_GET['clientSecret'],
            'instance' => $_GET['instance'],
            'oauthServer' => $_GET['coreOauthServer'],
            'apiUri' => $_GET['coreApiUri'],
            'redirectUri' => $_GET['baseURL'] . '/examples/authentication/oauth-callback.php',
            'accessToken' => $_GET['accessToken'],
            'refreshToken' => $_GET['refreshToken'],
        );
    }
    public function getWeblinkActivationParams()
    {
        return array(
            'oauthServer' => $_GET['weblinkOauthServer'],
            'apiUri' => $_GET['weblinkApiUri'],
            'portal' => $_GET['portal'],
            'portalToken' => ''.$_GET['portalToken'].''
        );
    }
}
