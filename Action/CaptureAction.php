<?php

namespace Fullpipe\Payum\Uniteller\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Fullpipe\Payum\Uniteller\Api;

class CaptureAction implements ActionInterface, ApiAwareInterface
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
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /* @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $details['URL_RETURN'] && $request->getToken()) {
            $details['URL_RETURN'] = $request->getToken()->getAfterUrl();
        }

        $refer = $_SERVER['HTTP_REFERER'];

        $pos = strpos($refer, 'mfmag');
        if ($pos === false) {
            $details['Merchant_ID'] = $this->api->getShopId();
        }else{
            $details['Merchant_ID'] = $this->api->getShopId_mfmag();
        }

        $details['Signature'] = $this->api->sing($details->toUnsafeArray());

        $details->validatedKeysSet(array(
            'Merchant_ID',
            'OrderNumber',
            'OrderAmount',
            'Signature',
            'Currency',
            'Signature',
        ));
        

        $details['URL_RETURN_OK'] = $details['URL_RETURN_OK'] . '?Order_ID=' . $details['OrderNumber'];

        $details['URL_RETURN_NO'] = $details['URL_RETURN_NO'] . '?Order_ID=' . $details['OrderNumber'];

        throw new HttpPostRedirect($this->api->getPaymentPageUrl(), $details->toUnsafeArray());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
