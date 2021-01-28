<?php

require 'vendor/autoload.php';

session_start();

use Onetoweb\Unit4\Unit4Client;
use Onetoweb\Unit4\Token;
use Onetoweb\Unit4\Exception\RequestException;

// client parameters
$clientId = 'client_id';
$clientSecret = 'client_secret';
$redirectUrl = 'redirect_url';
$version = 22;
$sandbox = true;

// setup client
$client = new Unit4Client($clientId, $clientSecret, $redirectUrl, $version, $sandbox);

// set database
$database = 'database';
$client->setDatabase($database);

// set token callback to store token
$client->setUpdateTokenCallback(function(Token $token) {
    
    $_SESSION['token'] = [
        'accessToken' => $token->getAccessToken(),
        'refreshToken' => $token->getRefreshToken(),
        'expires' => $token->getExpires(),
    ];
});


// load token or request token to gain access to unit 4
if (!isset($_SESSION['token']) and !isset($_GET['code'])) {
    
    // request permission with authorization link
    echo '<a href="'.$client->getAuthorizationLink().'">Unit4 login</a>';
        
} else if (!isset($_SESSION['token']) and isset($_GET['code'])) {
    
    // request access token
    $client->requestAccessToken($_GET['code']);
    
} else {
    
    // load token from storage
    $token = new Token(
        $_SESSION['token']['accessToken'],
        $_SESSION['token']['refreshToken'],
        $_SESSION['token']['expires']
    );
    
    $client->setToken($token);
    
}

// make unit 4 request after token is set
if ($client->getToken()) {
    
    try {
        
        // get administration info list
        $administrationInfoList = $client->getAdministrationInfoList();
        
        // get product info list
        $productInfoList = $client->getProductInfoList();
        
        // create product
        $product = $client->createProduct([
            'accountId' => '8020',
            'discountAccountId' => '8020',
            'productId' => '42',
            'pricePer' => 3.14,
            'description' => 'test product',
            'shortName' => 'TEST'
        ]);
        
        // get product
        $productId = 'product_id';
        $product = $client->getProduct($productId);
        
        // get customer info list
        $customerInfoList = $client->getCustomerInfoList();
        
        // create customer
        $customer = $client->createCustomer([
            'name' => 'Customer name',
            'shortName' => 'CN',
        ]);
        
        // get customer
        $customerId = 'customer_id';
        $customer = $client->getCustomer($customerId);
        
        // delete customer
        $customerId = 'customer_id';
        $client->deleteCustomer($customerId);
        
        // create order
        $customerId = 'customer_id';
        $deliveryAddressId = 'delivery_address_id';
        $productId = 'product_id';
        $order = $client->createOrder([
            'customerId' => $customerId,
            'reference' => 'test order',
            'orderDate' => date('d-m-Y'),
            'paymentConditionId' => '1',
            'deliveryAddressId' => $deliveryAddressId,
            'orderLines' => [[
                'productId' => $productId,
                'quantityOrdered' => 1,
            ]],
        ]);
        
        // get shipping order document command
        $orderId = 'order_id';
        $shippingOrderDocument = $client->getShippingOrderDocumentCommand([
            'orderId' => $orderId,
            'format' => 1
        ]);
        
        // get open orders
        $openOrders = $client->getOrderInfoListOpenOrders();
        
        // get orders ready to print invoice
        $ordersReadyToPrintInvoice = $client->getOrderInfoListOrdersReadyToPrintInvoice();
        
        // get order
        $orderId = 'order_id';
        $order = $client->getOrder($orderId);
        
        // get order state NVL
        $orderStates = $client->getOrderStateNVL();
        
        // get order type NVL
        $orderTypes = $client->getOrderTypeNVL();
        
        // get order line types
        $orderLineTypes = $client->getOrderLineTypeNVL();
        
        // process order command
        $journalId = 'V';
        $orderId = 'order_id';
        $fiscalYear = 2020;
        $periodNumber = 12;
        $result = $client->processOrderCommand([
            'journalId' => $journalId,
            'fiscalYear' => $fiscalYear,
            'periodNumber' => 12,
            'invoiceDate' => date('d-m-Y'),
            'orderId' => $orderId,
        ]);
        
        // create address
        $organizationId = 'organization_id';
        $address = $client->createAddress([
            'organizationId' => $organizationId,
            'street1' => 'street 1',
            'telephone' => '0123456789',
            'zipCode' => '1000AA',
            'city' => 'city',
        ]);
        
        // get address list
        $organizationId = 'organization_id';
        $addressList = $client->getAddressList($organizationId);
        
        // get accounts
        $fiscalYear = 2020;
        $accounts = $client->getAccountInfoList($fiscalYear);
        
        // get account managers
        $accountManagers = $client->getAccountManagerNVL();
        
        // get account category
        $accountCategory = $client->getAccountCategoryNVL();
        
        // get period info list
        $periodInfoList = $client->getPeriodInfoList();
        
        // get payment condition info list
        $paymentConditionInfoList = $client->getPaymentConditionInfoList();
        
        // get document types
        $documentTypes = $client->getDocumentTypeInfoList();
        
        // get document type
        $documentType = 'document_type';
        $documentType = $client->getDocumentTypeInfo($documentType);
        
        // get fiscal year info list
        $fiscalYearInfoList = $client->getFiscalYearInfoList();
        
        // get journal info list
        $journalInfoList = $client->getJournalInfoList();
        
        //get journal transaction info list
        $journalId = 'V';
        $fiscalYear = 2020;
        $journalTransactionInfoList = $client->getJournalTransactionInfoList($journalId, $fiscalYear);
        
        // get journal type nvl
        $journalTypeNVL = $client->getJournalTypeNVL();
        
        // create customer invoice
        $customerId = 'customer_id';
        $customerInvoice = $client->createCustomerInvoice([
            'customerId' => $customerId,
            'fiscalYear' => 2020,
            'journalId' => 'V',
            'journalTransaction' => 26,
            'paymentConditionId' => '1',
            'periodNumber' => 12,
            'invoiceDate' => '2020-12-01',
        ]);
        
        // get customer invoice
        $invoiceId = 'invoice_id';
        $invoice = $client->getCustomerInvoice($invoiceId);
        
        // get customer invoice info list by fiscal year
        $fiscalYear = 2020;
        $invoiceState = 1;
        $customerInvoices = $client->getCustomerInvoiceInfoListByFiscalYear($fiscalYear, $invoiceState);
        
        // get company contact person list
        $companyContactPerson = $client->getCompanyContactPersonList();
        
        // get report template configuration list
        $reportTemplateConfigurationList = $client->getReportTemplateConfigurationList();
        
        // get report template configuration
        $configurationId = 'configuration_id';
        $reportTemplateConfiguration = $client->getReportTemplateConfiguration($configurationId);
        
        // get mail message info list
        $mailMessageInfoList = $client->getMailMessageInfoList();
        
        // get mail templates
        $mailTemplates = $client->getMailTemplateList();
        
        // get document invoice by order id
        $orderId = 'order_id';
        $documentInvoice = $client->getDocumentInvoiceByOrderId($orderId, [
            'format' => 1
        ]);
        
        // get document invoice by invoice id
        $documentInvoice = $client->getDocumentInvoiceByInvoiceId($invoiceId, [
            'format' => 1
        ]);
        
        // get document invoice by order id for web
        $orderId = 'order_id';
        $invoice = $client->getDocumentInvoiceByOrderIdForWeb($orderId, [
            'format' => 1,
        ]);
        
        // get document invoice for web
        $invoiceId = 'invoice_id';
        $invoice = $client->getDocumentInvoiceForWeb($orderId, [
            'format' => 1,
        ]);
        
        // get next journal transaction command
        $fiscalYear = 2020;
        $journalId = 'K';
        $journalTransaction = $client->getNextJournalTransactionCommand([
            'fiscalYear' => $fiscalYear,
            'journalId' => $journalId,
        ]);
        
        // create fin trans
        $invoiceId = 'invoice_id';
        $customerId = 'customer_id';
        $periodNumber = 1;
        $finTrans = $client->createFinTrans([
            'journalId' => $journalId,
            'fiscalYear' => $fiscalYear,
            'periodNumber' => $periodNumber,
            'journalTransaction' => $journalTransaction,
            'transactionDate' => date('d-m-Y'),
            'description' => 'description',
            'finTransEntries' => [[
                '$type' => 'UNIT4.Multivers.API.BL.Financial.Edit.CustomerEntryProxy, UNIT4.Multivers.API.Web.WebApi.Model',
                'customerEntryPayments' => [[
                    'amountPaidCur' => 100,
                    'description' => 'invoice '.$invoiceId,
                    'invoiceId' => $invoiceId
                ]],
                'customerId' => $customerId,
                'transactionDate' => date('d-m-Y'),
            ]],
        ]);
        
        // get fin trans
        $fiscalYear = 2020;
        $journalId = 'K';
        $journalTransaction = 'journal_transaction';
        $finTrans = $client->getFinTrans($fiscalYear, $journalId, $journalTransaction);
        
        // get company details
        $companyDetails = $client->getCompanyDetails();
        
        // approve invoice payment command
        $invoiceId = 'invoice_id';
        $approverId = 'approver_id';
        $result = $client->approveInvoicePaymentCommand([
            'invoiceId' => $invoiceId,
            'approverId' => $approverId,
        ]);
        
    } catch (RequestException $requestException) {
        
        // get json error message
        $errors = json_decode($requestException->getMessage());
        
    }
}