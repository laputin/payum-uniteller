<?php

namespace Fullpipe\Payum\Uniteller\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Bridge\Spl\ArrayObject;
use Fullpipe\Payum\Uniteller\Api;

class FillOrderDetailsAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();
        $details = ArrayObject::ensureArrayObject($order->getDetails());

        $details->defaults(array(
            'Delay' => Api::DEFAULT_PAYMENT_FORM_Delay,
            'CardPayment' => Api::DEFAULT_ORDER_CardPayment,
            'Language' => Api::PAYMENT_PAGE_LANGUAGE_RU,
            'CardPayment' => Api::DEFAULT_ORDER_CardPayment,
            'WMPayment' => Api::DEFAULT_ORDER_WMPayment,
            'YMPayment' => Api::DEFAULT_ORDER_YMPayment,

        ));
        /*
                if ($this->api->isSandbox()) {
                    unset($details['Delay']);
                    unset($details['MeanType']);
                    unset($details['EMoneyType']);
                }
         */
                $details['OrderNumber'] = $this->api->validateOrderNumber($order->getNumber());
                $details['OrderAmount'] = ((float) $order->getTotalAmount())/100;
                $details['OrderCurrency'] = $this->api->validateOrderCurrency($order->getCurrencyCode());
                $details['OrderComment'] = $order->getDescription();
                /* $details['Customer_IDP'] = $order->getClientId(); */
       $details['Email'] = $order->getClientEmail();

       $details->validateNotEmpty('OrderNumber', 'OrderAmount', 'OrderCurrency');

       $order->setDetails($details);
   }

   /**
    * {@inheritDoc}
    */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
