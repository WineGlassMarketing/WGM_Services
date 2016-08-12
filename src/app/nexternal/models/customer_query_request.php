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
        'Cell' => '',
        'Username' => '',
        'Password' => '',
        'NameOnCard' => '',
        'CreditCardType' => '',
        'CreditCardNumber' => '',
        'CreditCardExpiryMo' => '',
        'CreditCardExpiryYr' => '',
        'ClubName' => '',
        'SignupDate' => '',
        'OnHoldStartDate' => '',
        'OnHoldUntilDate' => '',
        'CancelDate' => '',
        'IsGift' => '',
        'GiftMessage' => '',
        'ClubNotes' => '',
        'ShipEmail' => '',
        'ShipCompany' => '',
        'ShipFirstName' => '',
        'ShipLastName' => '',
        'ShipAddress' => '',
        'ShipAddress2' => '',
        'ShipCity' => '',
        'ShipStateCode' => '',
        'ShipZipCode' => '',
        'ShipMainPhone' => '',
        'IsPickupAtWinery' => '',
        'PickupLocationCode' => ''
      ];
    }

    private function _parseV65Address($v, $n, $ship=FALSE){
      if( $ship ){
        if( array_key_exists('Company', $n) ){
          $v['ShipCompany'] = $n['Company'];
        }else{
          $v['ShipCompany'] = '';
        }
        $v['ShipFirstName'] = $n['Name']['FirstName'];
        $v['ShipLastName'] = $n['Name']['LastName'];

        $v['ShipAddress'] = $n['StreetAddress1'];
        if( array_key_exists('StreetAddress2', $n) ){
          $v['ShipAddress2'] = $n['StreetAddress2'];
        }else{
          $v['ShipAddress2'] = '';
        }
        $v['ShipCity'] = $n['City'];
        $v['ShipStateCode'] = $n['StateProvCode'];
        $v['ShipZipCode'] = $n['ZipPostalCode'];
        $v['ShipMainPhone'] = $n['PhoneNumber'];
      }else{
        if( array_key_exists('Company', $n) ){
          $v['Company'] = $n['Company'];
        }else{
          $v['Company'] = '';
        }
        $v['FirstName'] = $n['Name']['FirstName'];
        $v['LastName'] = $n['Name']['LastName'];

        $v['Address'] = $n['StreetAddress1'];
        if( array_key_exists('StreetAddress2', $n) ){
          $v['Address2'] = $n['StreetAddress2'];
        }else{
          $v['Address2'] = '';
        }
        $v['City'] = $n['City'];
        $v['StateCode'] = $n['StateProvCode'];
        $v['ZipCode'] = $n['ZipPostalCode'];
        $v['CountryCode'] = $n['CountryCode'];
        $v['MainPhone'] = $n['PhoneNumber'];
      }

      return $v;
    }

    public function getOutputToV65Array(){
      $o = $this->getOutputToArray();
      $vs = [];
      // print_r($o);
      // exit;
      foreach ($o['Customer'] as $c) {

        // CC & CLUB MEMBERS ONLY (used for Bar Z Wines)
        if( !array_key_exists('SavedCreditCards', $c) ){
          if( $c['CustomerType']=='Wholesale' || $c['CustomerType']=='Consumer' ){
            continue;
          }
        }

        $v = $this->_v65_map;

        // BASIC INFO
        $v["CustomerNumber"] = $c['CustomerNo'];
        $v['Email'] = $c['Email'];
        $v['ShipEmail'] = $c['Email'];

        // ADDRESSES (includes names)
        $v = $this->_parseV65Address($v, $c['Address']);
        $v = $this->_parseV65Address($v, $c['Address'], TRUE);

        if( array_key_exists('AdditionalAddresses', $c) ){
            $addrs = $c['AdditionalAddresses'];
            // if( !array_key_exists('StreetAddress1', $addrs['Address']) ){
            //   $addrs = $addrs['Address'];
            // }
            foreach($addrs as $s){
              if( array_key_exists('PrimaryShip', $s) ){
                $v = $this->_parseV65Address($v, $s, TRUE);
              }
              if( array_key_exists('PrimaryBill', $s) ){
                $v = $this->_parseV65Address($v, $s);
              }
            }
        }

        // CCs
        if( array_key_exists('SavedCreditCards', $c) ){
          $ccs = $c['SavedCreditCards'];
          if( !array_key_exists('CreditCardType', $ccs["CreditCard"]) ){
            $ccs = $c['SavedCreditCards']['CreditCard'];
          }
          foreach($ccs as $cc){
            if( array_key_exists('PreferredCreditCard', $cc) ){
              $v['CreditCardType'] = $cc['CreditCardType'];
              $v['CreditCardNumber'] = $cc['CreditCardNumber'];
              $v['CreditCardExpiryMo'] = $this->_dateSplitter($cc['CreditCardExpDate'])['mo'];
              $v['CreditCardExpiryYr'] = $this->_dateSplitter($cc['CreditCardExpDate'])['yr'];
              break;
            }
          }
        }


        // CLUB INFO
        $v['ClubName'] = $c['CustomerType'];
        $v['SignupDate'] = $c['Created']['DateTime']['Date'];
        if( !array_key_exists('Active', $c) ){
          $v['CancelDate'] = $c['LastUpd']['DateTime']['Date'];
        }
        if( array_key_exists('CreatedByNote', $c['CreatedBy']) ){
          $v['ClubNotes'] = $c['CreatedBy']['CreatedByNote'];
        }

        array_push($vs, $v);
      }

      return $vs;
    }


  }

?>
