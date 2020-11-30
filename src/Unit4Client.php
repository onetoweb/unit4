<?php

namespace Onetoweb\Unit4;

use Onetoweb\Unit4\BaseClient;

/**
 * Unit4 Api Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://sandbox.api.online.unit4.nl/V22/Help
 */
class Unit4Client extends BaseClient
{
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
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-CompanyContactPersonList
     */
    public function getCompanyContactPersonList(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/CompanyContactPersonList");
    }
    
    /**
     * @param string $organizationId
     * 
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-AddressList-organizationId
     */
    public function getAddressList(string $organizationId): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/AddressList/$organizationId");
    }
    
    /**
     * @param array $data = []
     * 
     * @return array|null
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/POST-api-database-Address
     */
    public function createAddress(array $data = [])
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->post("/api/{$this->database}/Address", $data);
        
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
     * @param array $query = []
     *
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-OrderInfoList-OrdersReadyToPrintInvoice
     */
    public function getOrderInfoListOrdersReadyToPrintInvoice(array $query = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/OrderInfoList/OrdersReadyToPrintInvoice", $query);
    }
    
    /**
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-OrderStateNVL
     */
    public function getOrderStateNVL(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/OrderStateNVL");
    }
    
    /**
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-OrderTypeNVL
     */
    public function getOrderTypeNVL(): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/OrderTypeNVL");
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
     * @param array $query = []
     *
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/POST-api-database-ProcessOrderCommand_journalId_fiscalYear_periodNumber_invoiceDate_orderId_journalTransaction
     */
    public function processOrderCommand(array $query = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->post("/api/{$this->database}/ProcessOrderCommand", [], $query);
    }
    
    /**
     * @param array $query = []
     *
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-GetShippingOrderDocumentCommand_orderId_format
     */
    public function getShippingOrderDocumentCommand(array $query = []): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/GetShippingOrderDocumentCommand", $query);
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
     * @param int $fiscalYear
     * @param int $invoiceState
     *
     * @return array|null
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-CustomerInvoiceInfoList-ByFiscalYear-fiscalYear-invoiceState
     */
    public function getCustomerInvoiceInfoListByFiscalYear(int $fiscalYear, int $invoiceState): ?array
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/CustomerInvoiceInfoList/ByFiscalYear/$fiscalYear/$invoiceState");
    }
    
    /**
     * @param string $orderId
     * @param array $query = []
     *
     * @return mixed
     * 
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Documents-Invoice-ByOrderId-orderId_format
     */
    public function getDocumentInvoiceByOrderId(string $orderId, array $query = [])
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Documents/Invoice/ByOrderId/$orderId", $query, false);
    }
    
    /**
     * @param string $invoiceId
     * @param array $query = []
     *
     * @return mixed
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Documents-Invoice-invoiceId_format
     */
    public function getDocumentInvoiceByInvoiceId(string $invoiceId, array $query = [])
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Documents/Invoice/$invoiceId", $query, false);
    }
    
    /**
     * @param string $orderId
     * @param array $query = []
     *
     * @return mixed
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Documents-Order-orderId_format
     */
    public function getDocumentOrder(string $orderId, array $query = [])
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Documents/Order/$orderId", $query, false);
    }
    
    /**
     * @param string $orderId
     * @param array $query = []
     *
     * @return mixed
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Documents-Invoice-ByOrderIdForWeb-orderId_format
     */
    public function getDocumentInvoiceByOrderIdForWeb(string $orderId, array $query = [])
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Documents/Invoice/ByOrderIdForWeb/$orderId", $query, false);
    }
    
    
    
    
    
    
    
    /**
     * @param string $invoiceId
     * @param array $query = []
     *
     * @return mixed
     *
     * @link https://sandbox.api.online.unit4.nl/V22/Help/Api/GET-api-database-Documents-Invoice-ForWeb-invoiceId_format
     */
    public function getDocumentInvoiceForWeb(string $invoiceId, array $query = [])
    {
        $this->checkRequiredDatabase(__FUNCTION__);
        
        return $this->get("/api/{$this->database}/Documents/Invoice/ForWeb/$invoiceId", $query, false);
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