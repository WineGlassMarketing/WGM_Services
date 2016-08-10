<?php namespace wgm\nexternal\models;

  require_once $_ENV['APP_ROOT'] . "/nexternal/models/abstract_xml_model.php";

  class CustomerQueryRequest extends AbstractXmlModel{


    function __construct($session, $page=1){
      parent::__construct($session, $page);
      $this->_url = "https://www.nexternal.com/shared/xml/customerquery.rest";
      $this->_input = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" .
                        "<CustomerQueryRequest>" .
                           "<Credentials>" .
                            "<AccountName>{$session['account']}</AccountName>" .
                            "<Key>{$session['key']}</Key>" .
                           "</Credentials>" .
                           "<Page>{$page}</Page>" .
                        "</CustomerQueryRequest>";
      $this->_v65_map = [
        'CustomerNumber' => 0,
        'Email' => '',
        'Company' => '',
        'FirstName' => '',
        'LastName' => '',
        'Address' => '',
        'Address2' => '',
        'City' => '',
        'StateCode' => '',
        'ZipCode' => '',
        'CountryCode' => 'US',
        'MainPhone' => '',
        'CreditCardType' => '',
        'CreditCardNumber' => '',
        'CreditCardExpiryMo' => '',
        'CreditCardExpiryYr' => '',
        'ShipEmail' => '',
        'ShipCompany' => '',
        'ShipFirstName' => '',
        'ShipLastName' => '',
        'ShipAddress' => '',
        'ShipAddress2' => '',
        'ShipCity' => '',
        'ShipStateCode' => '',
        'ShipZipCode' => '',
        'ShipMainPhone' => ''
      ];
    }

    private function _parseAddress($v, $n, $ship=FALSE){
      if( $ship ){
        if( $n['@attributes']["Type"]=='Business' ){
          if( array_key_exists('Company', $n) ){
            $v['ShipCompany'] = $n['Company'];
            $v['ShipFirstName'] = $n['Name']['FirstName'];
            $v['ShipLastName'] = $n['Name']['LastName'];
            //$v['ShipCompany'] = $n['Name']['FirstName'] . " " . $n['Name']['LastName'];
          }else{
            // $v['ShipCompany'] = $n['Name']['FirstName'];
            // if( array_key_exists('LastName', $n) ){
            //   $v['ShipCompany'] .= $n['Name']['LastName'];
            // }
            $v['ShipFirstName'] = $n['Name']['FirstName'];
            $v['ShipLastName'] = $n['Name']['LastName'];
          }
        }else{
          if( array_key_exists('Company', $n) ){
            $v['ShipCompany'] = $n['Company'];
          }
          $v['ShipFirstName'] = $n['Name']['FirstName'];
          $v['ShipLastName'] = $n['Name']['LastName'];
        }
        $v['ShipAddress'] = $n['StreetAddress1'];
        if( array_key_exists('StreetAddress2', $n) ){
          $v['ShipAddress2'] = $n['StreetAddress2'];
        }
        $v['ShipCity'] = $n['City'];
        $v['ShipStateCode'] = $n['StateProvCode'];
        $v['ShipZipCode'] = $n['ZipPostalCode'];
        $v['ShipMainPhone'] = $n['PhoneNumber'];
      }else{
        if( $n['@attributes']["Type"]=='Business' ){
          if( array_key_exists('Company', $n) ){
            $v['Company'] = $n['Company'];
            $v['FirstName'] = $n['Name']['FirstName'];
            $v['LastName'] = $n['Name']['LastName'];
            //$v['ShipCompany'] = $n['Name']['FirstName'] . " " . $n['Name']['LastName'];
          }else{
            // $v['Company'] = $n['Name']['FirstName'];
            // if( array_key_exists('LastName', $n) ){
            //   $v['Company'] .= $n['Name']['LastName'];
            // }
            $v['FirstName'] = $n['Name']['FirstName'];
            $v['LastName'] = $n['Name']['LastName'];
          }
        }else{
          if( array_key_exists('Company', $n) ){
            $v['Company'] = $n['Company'];
          }
          $v['FirstName'] = $n['Name']['FirstName'];
          $v['LastName'] = $n['Name']['LastName'];
        }
        $v['Address'] = $n['StreetAddress1'];
        if( array_key_exists('StreetAddress2', $n) ){
          $v['Address2'] = $n['StreetAddress2'];
        }
        $v['City'] = $n['City'];
        $v['StateCode'] = $n['StateProvCode'];
        $v['ZipCode'] = $n['ZipPostalCode'];
        $v['MainPhone'] = $n['PhoneNumber'];
      }

      return $v;
    }

    public function getOutputToV65Array(){
      $o = $this->getOutputToArray();
      $vs = [];
      foreach ($o['Customer'] as $c) {
        $v = $this->_v65_map;
        $v["CustomerNumber"] = $c['CustomerNo'];
        $v['Email'] = $c['Email'];
        $v['ShipEmail'] = $c['Email'];
        // if( array_key_exists('PrimaryBill', $c['Address']) ){
        //   $v = $this->_parseAddress($v, $c['Address']);
        //
        // }elseif( array_key_exists('AdditionalAddresses', $c) ){
        //   foreach($c['AdditionalAddresses'] as $s){
        //     if( array_key_exists('PrimaryBill', $s) ){
        //       $v = $this->_parseAddress($v, $s);
        //       break;
        //     }
        //   }
        // }
        $v = $this->_parseAddress($v, $c['Address']);
        if( !array_key_exists('PrimaryShip', $c['Address']) && array_key_exists('AdditionalAddresses', $c) ){
            foreach($c['AdditionalAddresses'] as $s){
              if( array_key_exists('PrimaryShip', $s) ){
                $v = $this->_parseAddress($v, $s, TRUE);
                break;
              }
            }
            if( empty($v['ShipAddress']) ){
              $v = $this->_parseAddress($v, $c['Address'], TRUE);
            }
        }else{
          $v = $this->_parseAddress($v, $c['Address'], TRUE);
        }
        if( array_key_exists('SavedCreditCards', $c) ){
          foreach($c['SavedCreditCards']['CreditCard'] as $cc){
            if( is_array($cc) && array_key_exists('PreferredCreditCard', $cc) ){
              $v['CreditCardType'] = $cc['CreditCardType'];
              $v['CreditCardNumber'] = $cc['CreditCardNumber'];
              $v['CreditCardExpiryMo'] = $this->_dateSplitter($cc['CreditCardExpDate'])['mo'];
              $v['CreditCardExpiryYr'] = $this->_dateSplitter($cc['CreditCardExpDate'])['yr'];
              break;
            }
          }
        }
        array_push($vs, $v);
      }

      return $vs;
    }


  }

?>
