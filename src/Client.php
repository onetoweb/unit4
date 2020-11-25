<?php

namespace Onetoweb\Unit4;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Onetoweb\Unit4\Exception\{RequestException, DatabaseException};
use Onetoweb\Unit4\Token;
use DateTime;

/**
 * Unit4 Api Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://sandbox.api.online.unit4.nl/V22/Help
 */
class Client
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
     * @var string
     */
    private $database;
    
    /**
     * @var Token
     */
    private $token;
    
    /**
     * @var callable
     */
    private $updateTokenCallback;
    
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
     * @return array|null
     */
    public function get(string $endpoint, array $query = []): ?array
    {
        return $this->request(self::METHOD_GET, $endpoint, [], $query);
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
     * @param string $method = self::METHOD_GET
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @throws RequestException if the server request contains a error response
     * 
     * @return array|null
     */
    public function request(string $method = self::METHOD_GET, string $endpoint, array $data = [], array $query = [], bool $json = true): ?array
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
            
            return json_decode($contents, true);
            
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
    
    /**
     * @param array $query = []
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-ProductInfoList
     */
    public function getProductInfoList(array $query = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/ProductInfoList", $query);
    }
    
    /**
     * @param array $data = []
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/POST-api-database-Product
     */
    public function createProduct(array $data = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->post("/api/{$this->database}/Product", $data);
    }
    
    /**
     * @param string $productId
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Product-productId
     */
    public function getProduct(string $productId): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Product/{$productId}");
    }
    
    /**
     * @param array $query = []
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-CustomerInfoList
     */
    public function getCustomerInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/CustomerInfoList");
    }
    
    /**
     * @param $id
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Customer-customerId
     */
    public function getCustomer($id): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Customer/$id");
    }
    
    /**
     * @param array $data = []
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/POST-api-database-Customer
     */
    public function createCustomer(array $data = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->post("/api/{$this->database}/Customer", $data);
    }
    
    /**
     * @param array $data = []
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/POST-api-database-Order
     */
    public function createOrder(array $data = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->post("/api/{$this->database}/Order", $data);
    }
    
    /**
     * @param array $query = []
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-OrderInfoList-OpenOrders
     */
    public function getOrderInfoListOpenOrders(array $query = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/OrderInfoList/OpenOrders", $query);
    }
    
    /**
     * @param string $orderId
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Order-orderId
     */
    public function getOrder(string $orderId): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Order/{$orderId}");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-OrderLineTypeNVL
     */
    public function getOrderLineTypeNVL(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/OrderLineTypeNVL");
    }
    
    /**
     * @param $id
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/DELETE-api-database-Customer-customerId
     */
    public function deleteCustomer($id): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->delete("/api/{$this->database}/Customer/$id");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-FiscalYearInfoList
     */
    public function getFiscalYearInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/FiscalYearInfoList");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-JournalInfoList 
     */
    public function getJournalInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/JournalInfoList");
    }
    
    /**
     * @param $journalId
     * @param $fiscalYear
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-JournalTransactionInfoList-journalId-fiscalYear
     */
    public function getJournalTransactionInfoList($journalId, $fiscalYear): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/JournalTransactionInfoList/$journalId/$fiscalYear");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-JournalTypeNVL
     */
    public function getJournalTypeNVL(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/JournalTypeNVL");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-MailMessageInfoList
     */
    public function getMailMessageInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/MailMessageInfoList");
    }
    
    /**
     * @param string $configId
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-ReportTemplateConfiguration-configId
     */
    public function getReportTemplateConfiguration(string $configId): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/ReportTemplateConfiguration/$configId");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-ReportTemplateConfigurationList
     */
    public function getReportTemplateConfigurationList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/ReportTemplateConfigurationList");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-MailTemplateList
     */
    public function getMailTemplateList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/MailTemplateList");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-PaymentConditionInfoList
     */
    public function getPaymentConditionInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/PaymentConditionInfoList");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-PeriodInfoList
     */
    public function getPeriodInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/PeriodInfoList");
    }
    
    /**
     * @param array $data = []
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/POST-api-database-CustomerInvoice
     */
    public function createCustomerInvoice(array $data = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->post("/api/{$this->database}/CustomerInvoice", $data);
    }
    
    /**
     * @param string $orderId
     * @param array $query = []
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Documents-Invoice-ByOrderId-orderId_format
     */
    public function getDocumentInvoiceByOrderId(string $orderId, array $query = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Documents/Invoice/ByOrderId/$orderId", $query);
    }
    
    /**
     * @param int $fiscalYear
     *
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-AccountInfoList-fiscalYear
     */
    public function getAccountInfoList(int $fiscalYear): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/AccountInfoList/$fiscalYear");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-AccountCategoryNVL
     */
    public function getAccountCategoryNVL(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/AccountCategoryNVL");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-AccountManagerNVL
     */
    public function getAccountManagerNVL(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/AccountManagerNVL");
    }
    
    /**
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-DocumentTypeInfoList
     */
    public function getDocumentTypeInfoList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/DocumentTypeInfoList");
    }
    
    /**
     * @param int $type
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-DocumentTypeInfo-type
     */
    public function getDocumentTypeInfo(int $type): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/DocumentTypeInfo/$type");
    }
}