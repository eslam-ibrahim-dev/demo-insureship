<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\CoreConstants;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

class QuickbooksService
{
    protected $config;
    protected $dataService;


    public function __construct()
    {
        $this->config = config('quickbooks');
        $this->dataService = DataService::Configure($this->config);
    }

    public function getAuthUrl()
    {
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        return $OAuth2LoginHelper->getAuthorizationCodeURL();
    }

    public function handleAuthCallback(string $code, string $realmId)
    {
        $helper = $this->dataService->getOAuth2LoginHelper();
        $accessToken = $helper->exchangeAuthorizationCodeForToken($code, $realmId);
        $this->dataService->updateOAuth2Token($accessToken);

        $this->saveTokensToDB($accessToken);

        return $accessToken;
    }

    protected function saveTokensToDB($token)
    {
        DB::table('osis_qbo_authentication')->updateOrInsert(
            ['realm_id' => $token->getRealmID()],
            [
                'access_token'    => $token->getAccessToken(),
                'access_expires'  => $token->getAccessTokenExpiresAt()->format('Y-m-d H:i:s'),
                'refresh_token'   => $token->getRefreshToken(),
                'refresh_expires' => $token->getRefreshTokenExpiresAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function getDataService()
    {
        return $this->dataService;
    }
}
