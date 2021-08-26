<?php

namespace Onetoweb\Unit4;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Onetoweb\Unit4\Exception\{RequestException, DatabaseException};
use Onetoweb\Unit4\Token;
use DateTime;

/**
 * Unit4 Api Base Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://sandbox.api.online.unit4.nl/V22/Help
 */
class BaseClient
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    
    /**
     * @var string
     */
    private $clientId;
    
    /**
     * @var string
     */
    private $clientSecret;
    
    /**
     * @var string
     */
    private $redirectUrl;
    
    /**
     * @var int
     */
    private $version;
    
    /**
     * @var bool
     */
    private $sandbox;
    
    /**
     * @var string
     */
    private $baseUrl;
    
    /**
     * @var Token
     */
    private $token;
    
    /**
     * @var callable
     */
    private $updateTokenCallback;
    
    /**
     * @var string
     */
    protected $database;
    
    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param int $version
     * @param bool $sandbox = false
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUrl, int $version = 21, bool $sandbox = false)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->version = $version;
        $this->sandbox = $sandbox;
        
        // build base uri
        if ($this->sandbox) {
            $this->baseUrl = 'https://sandbox.api.online.unit4.nl';
        } else {
            $this->baseUrl = 'https://api.online.unit4.nl';
        }
        
        $this->baseUrl .= '/v'.$this->version;
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
     * @param callable $updateTokenCallback
     */
    public function setUpdateTokenCallback(callable $updateTokenCallback): void
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
     *
     * @return mixed
     */
    public function get(string $endpoint, array $query = [], bool $decode = true)
    {
        return $this->request(self::METHOD_GET, $endpoint, [], $query, true, $decode);
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
        return $this->request(self::METHOD_POST, $endpoint, $data, $query, $json);
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
        return $this->request(self::METHOD_PUT, $endpoint, $data, $query, $json);
    }
    
    /**
     * @param string $endpoint
     * @param array $query = []
     * 
     * @return array|null
     */
    public function delete(string $endpoint, array $query = []): ?array
    {
        return $this->request(self::METHOD_DELETE, $endpoint, [], $query);
    }
    
    /**
     * @param string $method
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @throws RequestException if the server request contains a error response
     * 
     * @return mixed
     */
    public function request(string $method, string $endpoint, array $data = [], array $query = [], bool $json = true, bool $decode = true)
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
            if (in_array($method, [self::METHOD_POST, self::METHOD_PUT])) {
                
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
                'verify' => false,
            ]);
            
            // build guzzle request
            $result = $guzzleClient->request($method, $this->baseUrl . $endpoint, $options);
            
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
     */
    protected function checkRequiredDatabase(string $function = null)
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