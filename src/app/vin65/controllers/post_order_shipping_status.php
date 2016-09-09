<?php namespace wgm\vin65\controllers;

  require_once $_ENV['APP_ROOT'] . "/models/csv.php";
  require_once $_ENV['APP_ROOT'] . "/models/service_input.php";
  require_once $_ENV['APP_ROOT'] . "/models/service_input_form.php";
  require_once $_ENV['APP_ROOT'] . "/vin65/controllers/abstract_soap_controller.php";
  require_once $_ENV['APP_ROOT'] . "/vin65/models/post_order_shipping_status.php";
  require_once $_ENV['APP_ROOT'] . "/vin65/models/soap_service_queue.php";


  use wgm\models\CSV as CSV;
  use wgm\models\ServiceInput as ServiceInputModel;
  use wgm\models\ServiceInputForm as ServiceInputForm;
  use wgm\vin65\models\PostOrderShippingStatus as PostOrderShippingStatusModel;
  use wgm\vin65\models\SoapServiceQueue as SoapServiceQueue;

  class PostOrderShippingStatus extends AbstractSoapController{

    function __construct($session){
      parent::__construct($session);

      $this->_queue->appendService( "wgm\\vin65\\models\\PostOrderShippingStatus" );
      $this->_input_form = new ServiceInputForm( new PostOrderShippingStatusModel($session) );
    }

    public function setData($page_limit=25, $display_limit=50, $set_limit=1){
      $this->_queue->setData( new CSV($page_limit, $display_limit, $set_limit) );
    }

    public function inputRecord($record){
      // create consumable service model for queue
      $input = new ServiceInputModel();
      $input->addRecord($record);

      $this->_queue->setData($input);
      $this->_queue->init($_ENV['UPLOADS_PATH'] . '/post_order_shipping_status.csv');
    }

  }


?>
