<?php namespace wgm\vin65\models;

  require_once $_ENV['APP_ROOT'] . '/vin65/models/abstract_soap_model.php';
  use wgm\vin65\models\AbstractSoapModel as AbstractSoapModel;

  class UpsertOrder extends AbstractSoapModel{

    const SERVICE_WSDL = "https://webservices.vin65.com/v202/orderService.cfc?wsdl";
    const SERVICE_NAME = "OrderService";
    const METHOD_NAME = "UpsertOrder";

    private $_rms_map = [
      "batchid" => 'BatchID',
      "cashierid" => 'CashierID',
      "registernumber" => 'RegisterNumber',
      "storeid" => 'StoreID'
    ];

    private $_tender_map = [
      "amounttendered" => 'AmountTendered',
      "creditcardexpirationmonth" => 'CreditCardExpirationMonth',
      "creditcardexpirationyear" => 'CreditCardExpirationYear',
      "creditcardname" => 'CreditCardName',
      "creditcardnumber" => 'CreditCardNumber',
      "creditcardtype" => 'CreditCardType',
      "giftcardcode" => 'GiftCardCode',
      "giftcardid" => 'GiftCardID',
      "giftcardnumber" => 'GiftCardNumber',
      "giftcardvendor" => 'GiftCardVendor',
      "paymentdate" => 'PaymentDate',
      "paymenttype" => 'PaymentType',
      "pointsredeemed" => 'PointsRedeemed'
    ];

    private $_item_map = [
      "costofgood" => 'CostOfGood',
      "departmentcode" => 'DepartmentCode',
      "price" => 'Price',
      "productname" => 'ProductName',
      "productsku" => 'ProductSKU',
      "quantity" => 'Quantity',
      "salestax" => 'SalesTax',
      "shippingpartner" => 'ShippingPartner',
      "shippingservice" => 'ShippingService',
      "isnontaxable" => 'isNonTaxable'
    ];

    function __construct($session, $version=2){
      $this->_value_map = [
        "altcontactid" => 'AltContactID',
        "altshippingaddressid" => 'AltShippingAddressID',
        "billingaddress" => 'BillingAddress',
        "billingaddress2" => 'BillingAddress2',
        "billingbirthdate" => 'BillingBirthdate',
        "billingcity" => 'BillingCity',
        "billingcompany" => 'BillingCompany',
        "billingemail" => 'BillingEmail', // required regardless if using 'CustomerNumber' for id
        "billingfirstname" => 'BillingFirstName',
        "billinglastname" => 'BillingLastName',
        "billingphone" => 'BillingPhone',
        "billingstatecode" => 'BillingStateCode',
        "billingzipcode" => 'BillingZipCode',
        "contactid" => 'ContactID',
        "creditcardexpirationmonth" => 'CreditCardExpirationMonth',
        "creditcardexpirationyear" => 'CreditCardExpirationYear',
        "creditcardname" => 'CreditCardName',
        "creditcardnumber" => 'CreditCardNumber',
        "creditcardtype" => 'CreditCardType', // Visa, MasterCard, AmericanExpress, Discover
        "customernumber" => 'CustomerNumber', // not included in spec, but used to get ContactID. Do not include if syncing by BillingEmail
        "giftmessage" => 'GiftMessage',
        "handling" => 'Handling',
        "orderdate" => 'OrderDate',
        "orderitems" => 'OrderItems',
        "ordernotes" => 'OrderNotes',
        "ordernumber" => 'OrderNumber',
        "ordertype" => 'OrderType', // AdminPanel, ClubOrder, Facebook, iPad, Mobile, POS, Telemarketing or Website
        "paymenttype" => 'PaymentType', // Cash, Check, CreditCard
        "previousorderid" => 'PreviousOrderID',
        "previousordernumber" => 'PreviousOrderNumber',
        "rms" => 'RMS',
        "salesassociate" => 'SalesAssociate',
        "sendtofulfillment" => 'SendToFulfillment',
        "shipdate" => 'ShipDate',
        "shipping" => 'Shipping',
        "shippingaddress" => 'ShippingAddress',
        "shippingaddress2" => 'ShippingAddress2',
        "shippingaddressid" => 'ShippingAddressID',
        "shippingbirthdate" => 'ShippingBirthdate',
        "shippingcity" => 'ShippingCity',
        "shippingcompany" => 'ShippingCompany',
        "shippingemail" => 'ShippingEmail',
        "shippingfirstname" => 'ShippingFirstName',
        "shippinglastname" => 'ShippingLastName',
        "shippingphone" => 'ShippingPhone',
        "shippingstatecode" => 'ShippingStateCode',
        "shippingstatus" => 'ShippingStatus',
        "shippingzipcode" => 'ShippingZipCode',
        "subtotal" => 'SubTotal',
        "tax" => 'Tax',
        "tenders" => 'Tenders',
        "total" => 'Total',
        "transactiontype" => 'TransactionType', // Order, Refund
        "websitecode" => 'WebsiteCode',
        "ispickup" => 'isPickup'
      ];

      parent::__construct($session, 2);
      $this->_values['orders'] = [];

    }

    public function getValuesID(){
      $ids = [];
      foreach ($this->_values['orders'] as $value) {
        if( isset($value["CustomerNumber"]) && !empty($values["CustomerNumber"]) ){
          array_push($ids, $value["CustomerNumber"]);
        }else{
          array_push($ids, $value["BillingEmail"]);
        }
      }

      if( count($ids) > 0 ) return $explode($ids, ",");

      return parent::getValuesID();

    }

    public function getResultID(){
      if( isset($this->_result->internalKeyCode) ){
        return $this->_result->internalKeyCode;
      }
      return parent::getResultID();
    }

    public function addOrderValues($props, $order=NULL){

      if( $order===NULL ){
        $order = [];
      }
      foreach($props as $key => $value){

        if( array_key_exists( strtolower($key), $this->_value_map) ){
          $order[ $this->_value_map[strtolower($key)] ] = $value;
        }
      }
      return $order;
    }

    public function addOrder($order){
      array_push($this->_values['orders'], $order);
    }

    public function addOrderItemValues($props, $order_item=NULL){
      if( $order_item===NULL ){
        $order_item = [];
      }
      foreach($props as $key => $value){
        if( array_key_exists(strtolower($key), $this->_item_map) ){
          $order_item[ $this->_item_mapp[strtolower($key)] ] = $value;
        }
      }
      return $order_item;
    }

    public function addOrderItem($order, $order_item){
      if( !isset($order['OrderItems']) ){
        $order['OrderItems'] = [];
      }
      array_push($order['OrderItems'], $order_item);
    }

    // public function callService($values=NULL){
    //   parent::callService();
    //   try{
    //     $client = new \SoapClient($_ENV['V65_V2_CONTACT_SERVICE']);
    //     $result = $client->upsertShippingAddress($this->_values);
    //     // print_r($this->_values);
    //     if( is_soap_fault($result) ){
    //       $this->_error = "SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring})";
    //     }elseif(empty($result->results[0]->isSuccessful)){
    //       $this->_error = $result->results[0]->message;
    //     }else{
    //       $this->_result = $result->results[0]->internalKeyCode ;
    //     }
    //   }catch(Exception $e){
    //     $this->_error = $e->message;
    //   }
    // }

  }

?>
