<?php

namespace TheBigSurf\WireUpdateHook\Observer;

/**
 * Update Hook Observer
 * base class to extend when hooking API for magento updates
 *
 * Copyright © 2016 The big Surf. All rights reserved.
 */


class UpdateHookObserver implements \Magento\Framework\Event\ObserverInterface
{
     /**
     * Logging instance
     * @var \TheBigSurf\WireUpdateHook\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;

    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;

    /**
    * @var \Magento\Framework\Escaper
    */
    protected $_escaper;

    protected $messageManager;

    protected $apitoken;
    protected $apierroremail;
    protected $apilog;
    protected $sendername;
    protected $senderemail;

    protected $firebaseSKUs = '/api/1.0/magento-sync/hook/products/';

    protected $firebaseAttributes = '/api/1.0/magento-sync/hook/attributes/';

    /**
     * Constructor
     * @param \TheBigSurf\WireUpdateHook\Logger\Logger $logger
     */
    public function __construct(
        \TheBigSurf\WireUpdateHook\Logger\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $this->apitoken      = $this->_scopeConfig->getValue('setting/general/apitoken');
        $this->apierroremail = $this->_scopeConfig->getValue('setting/general/apierroremail');
        $this->apilog        = $this->_scopeConfig->getValue('setting/general/apilog');
        $this->sendername    = $this->_scopeConfig->getValue('trans_email/ident_general/name');
        $this->senderemail   = $this->_scopeConfig->getValue('trans_email/ident_general/email');


        // get the API URL
        $apiUrl = $this->getApiUrl();

        // get the data for the request
        $data = $this->getData($observer);

        if ($this->apilog && $data) {

            $this->_logger->info($data);

        }

        // send the request
        $response = $this->sendRequest( $apiUrl, $data );

        // process the response
        $this->processReponse( $response );

    }


    /**
     * URL for API endpoint
     * @return string
     *
     */
    protected function getApiUrl() {

        return '';

    }


    /**
     * generate the URL for the SKUs endpoint
     * @return string
     *
     */
    protected function getSkuApiUrl() {

        $path = $this->_scopeConfig->getValue('setting/general/apiurl');

        // trim off any trailing slashes
        $path = rtrim($path, '/');

        $path .= $this->firebaseSKUs;

        return $path;

    }


    /**
     * generate the URL for the Attributes endpoint
     * @return string
     *
     */
    protected function getAttributeApiUrl() {

        $path = $this->_scopeConfig->getValue('setting/general/apiurl');

        // trim off any trailing slashes
        $path = rtrim($path, '/');

        $path .= $this->firebaseAttributes;

        return $path;

    }


    /**
     * build the JSON data to pass with the request
     * @return string - JSON formatted
     *
     */
    protected function getData( $observer ) {

        return '{}';

    }


    /**
     * send the request
     * @return object
     *
     */
    protected function sendRequest( $apiUrl, $data ) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  // JSON formated string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content­-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($data);
        $headers[] = 'X-Sync-Authorization: ' . $this->apitoken;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);
        $httpcode      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result        = json_decode($server_output);

        curl_close ($ch);

        return (object) [
            'output'   => $server_output,
            'httpcode' => $httpcode,
            'result'   => $result,
            'apiUrl'   => $apiUrl,
        ];

    }


    /**
     * process the response data
     *
     */
    protected function processReponse( $response ) {

        // check for errors
        $this->errorCheck( $response );

        // log response
        $this->setApiResponseLog( $response );

    }


    /**
     * check for errors and update admin
     *
     */
    protected function errorCheck( $response ) {

        if ( is_object($response->result) && isset($response->result->message) && $response->httpcode != 200 ) {

            $message = is_array($response->result->message) ? implode(" | ", $response->result->message) : $response->result->message;

            if ( $this->apierroremail != "" ) {

                $this->inlineTranslation->suspend();

                $emaildata = [
                    'httpcode'=> $response->httpcode,
                    'comment'=> $message
                ];

                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($emaildata);
                $error = false;

                $sender = [
                    'name' => $this->_escaper->escapeHtml( $this->sendername ),
                    'email' => $this->_escaper->escapeHtml( $this->senderemail ),
                ];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier('send_email_email_template') // this code we have mentioned in the email_templates.xml
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom( $sender )
                    ->addTo( $this->apierroremail )
                    ->getTransport();

                $transport->sendMessage(); ;

                $this->inlineTranslation->resume();

            }

            $this->messageManager->addError("Product API Error: " . $message);

        }

    }

    protected function setApiResponseLog( $response ) {

        $this->_logger->info( $response->httpcode );

        if (is_object($response->result) && isset($response->result->message)) {

            $message = is_array($response->result->message) ? implode(" | ", $response->result->message) : $response->result->message;

            $this->_logger->info($message);

        } else {

            $this->messageManager->addError('Product API Error: ' . $response->output);

            $this->_logger->info($response->output);

        }

    }

}