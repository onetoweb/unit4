<?php

namespace Onetoweb\Unit4;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Onetoweb\Unit4\Exception\{RequestException, DatabaseException};
use Onetoweb\Unit4\Token;
use Onetoweb\Unit4\Config\Method;
use DateTime;
use Closure;

/**
 * Unit4 Api Base Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://api.multivers.nl/V22/Help
 */
class BaseClient
{
    /**
     * @var string|null
     */
    private ?string $baseUrl = null;
    
    /**
     * @var Token|null
     */
    private ?Token $token = null;
    
    /**
     * @var Closure|null
     */
    private ?Closure $updateTokenCallback = null;
    
    /**
     * @var string|null
     */
    protected ?string $database = null;
    
    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param int $version
     * @param bool $sandbox = false
     */
    public function __construct(
        
        #[\SensitiveParameter]
        private string $clientId,
        
        #[\SensitiveParameter]
        private string $clientSecret,
        
        #[\SensitiveParameter]
        private string $redirectUrl,
        
        private int $version = 21,
        private bool $sandbox = false
    ) {
        // build base uri
        if ($this->sandbox) {
            $this->baseUrl = 'https://sandbox-api.multivers.nl';
        } else {
            $this->baseUrl = 'https://api.multivers.nl';
        }
        
        $this->baseUrl .= '/V'.$this->version;
    }
    
    /**
     * @param string $database
     * 
     * @return void
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }
    
    /**
     * @param $state = null
     * 
     * @return string
     */
    public function getAuthorizationLink($state = null): string
    {
        $query = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => 'http://UNIT4.Multivers.API/Web/WebApi/*',
            'response_type' => 'code',
        ];
        
        if ($state !== null) {
            $query['state'] = $state;
        }
        
        return $this->baseUrl.'/OAuth/Authorize?' . http_build_query($query);
    }
    
    /**
     * @param string $code
     * 
     * @return void
     */
    public function requestAccessToken(string $code): void
    {
        $token = $this->post('/OAuth/Token', [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code'
        ], [], false);
        
        $this->updateToken($token);
    }
    
    /**
     * @return void
     */
    private function requestRefreshToken(): void
    {
        $token = $this->post('/OAuth/Token', [
            'refresh_token' => $this->getToken()->getRefreshToken(),
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'refresh_token'
        ], [], false);
        
        $this->updateToken($token);
    }
    
    /**
     * @param Closure $updateTokenCallback
     */
    public function setUpdateTokenCallback(Closure $updateTokenCallback): void
    {
        $this->updateTokenCallback = $updateTokenCallback;
    }
    
    /**
     * @param array $tokenArray
     * 
     * @return void
     */
    private function updateToken(array $tokenArray): void
    {
        // get expires
        $expires = new DateTime();
        $expires->setTimestamp(time() + $tokenArray['expires_in']);
        
        $token = new Token($tokenArray['access_token'], $tokenArray['refresh_token'], $expires);
        
        $this->setToken($token);
        
        // token update callback
        ($this->updateTokenCallback)($this->getToken());
    }
    
    /**
     * @param Token $token
     * 
     * @return void
     */
    public function setToken(Token $token): void
    {
        $this->token = $token;
    }
    
    /**
     * @return Token
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }
    
    /**
     * @param string $endpoint
     * @param array $query = []
     * @param bool $decode = true
     * 
     * @return mixed
     */
    public function get(string $endpoint, array $query = [], bool $decode = true): mixed
    {
        return $this->request(Method::GET, $endpoint, [], $query, true, $decode);
    }
    
    /**
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @return array|null
     */
    public function post(string $endpoint, array $data = [], array $query = [], bool $json = true): ?array
    {
        return $this->request(Method::POST, $endpoint, $data, $query, $json);
    }
    
    /**
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @return array|null
     */
    public function put(string $endpoint, array $data = [], array $query = [], bool $json = true): ?array
    {
        return $this->request(Method::PUT, $endpoint, $data, $query, $json);
    }
    
    /**
     * @param string $endpoint
     * @param array $query = []
     * 
     * @return array|null
     */
    public function delete(string $endpoint, array $query = []): ?array
    {
        return $this->request(Method::DELETE, $endpoint, [], $query);
    }
    
    /**
     * @param Method $method
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @throws RequestException if the server request contains a error response
     * 
     * @return mixed
     */
    public function request(Method $method, string $endpoint, array $data = [], array $query = [], bool $json = true, bool $decode = true): mixed
    {
        // build request haders
        $headers = [
            'Cache-Control' => 'no-cache',
            'Connection' => 'close',
            'Accept' => 'application/json',
        ];
        
        if ($this->getToken() !== null and $endpoint !== '/OAuth/Token') {
            
            if ($this->getToken()->isExpired()) {
                
                $this->requestRefreshToken();
                
            }
            
            // add bearer token authorization header
            $headers['Authorization'] = "Bearer {$this->getToken()->getAccessToken()}";
            
        }
        
        try {
            
            //  add headers to request options
            $options[RequestOptions::HEADERS] = $headers;
            
            // add post data body
            if (in_array($method, [Method::POST, Method::PUT])) {
                
                if ($json) {
                    $options[RequestOptions::JSON] = $data;
                } else {
                    $options[RequestOptions::FORM_PARAMS] = $data;
                }
                
            }
            
            // build query 
            if (count($query) > 0) {
                $endpoint .= '?' . http_build_query($query);
            }
            
            // build guzzle client
            $guzzleClient = new GuzzleClient([
                RequestOptions::VERIFY => false,
                
            ]);
            
            // build guzzle request
            $result = $guzzleClient->request($method->value, $this->baseUrl . $endpoint, $options);
            
            // get contents
            $contents = $result->getBody()->getContents();
            
            // return data
            if ($decode) {
                return json_decode($contents, true);
            } else {
                return $contents;
            }
            
        } catch (GuzzleRequestException|ClientException $guzzleException) {
            
            if ($guzzleException->hasResponse()) {
                
                $contents = $guzzleException->getResponse()->getBody()->getContents();
                
                // check if contents contains json string
                json_decode($contents);
                if (json_last_error() === JSON_ERROR_NONE) {
                    
                    // return error response as exception message
                    throw new RequestException($contents, $guzzleException->getCode(), $guzzleException);
                    
                }
            }
            
            throw $guzzleException;
        }
        
        return null;
    }
    
    /**
     * @param string $function
     * 
     * @throws DatabaseException if no database is set 
     * 
     * @return void
     */
    protected function checkRequiredDatabase(string $function = null): void
    {
        if ($this->database === null) {
            
            if ($function !== null) {
                throw new DatabaseException("Client::$function requires a database to be set, use Client::setDatabase");
            } else {
                throw new DatabaseException();
            }
        }
    }
}
