<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@savvycube.com so we can send you a copy immediately.
 *
 * @category   SavvyCube
 * @package    SavvyCube_Connector
 * @copyright  Copyright (c) 2014 SavvyCube (http://www.savvycube.com). SavvyCube is a trademark of Webtex Solutions, LLC (http://www.webtexsoftware.com).
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SavvyCube_Connector_ApiController extends Mage_Core_Controller_Front_Action
{
    private function _authorize()
    {
        if (!$this->getAuthHelper()->checkRequest()) {
            Mage::app()->getResponse()
                ->setHeader('HTTP/1.1', '401 Unauthorized')
                ->setBody('<h1>401 Unauthorized</h1>')
                ->sendResponse();
            exit;
        }
    }

    private function formatResponse($data, $encrypt)
    {
        if ($encrypt) {
            Mage::app()->getResponse()->setHeader('Content-Type', 'text/plain');
            Mage::app()->getResponse()->setHeader('Content-Encoding', 'gzip');
            Mage::app()->getResponse()->setBody(
                $this->encrypt(Mage::helper('core')->jsonEncode($data))
            );
        } else {
            Mage::app()->getResponse()->setHeader('Content-Type', 'application/json');
            Mage::app()->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($data)
            );
        }
    }

    private function encrypt($data)
    {
        $key = $this->getAuthHelper()->getCurrentKey('e');

        return rtrim(
            base64_encode(
                mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_128,
                    $key,
                    gzencode($data),
                    MCRYPT_MODE_ECB,
                    mcrypt_create_iv(
                        mcrypt_get_iv_size(
                            MCRYPT_RIJNDAEL_128,
                            MCRYPT_MODE_ECB
                        ),
                        MCRYPT_DEV_URANDOM
                    )
                )
            ),
            "\0"
        );

    }

    /**
     * Authorization Helper
     *
     * @return SavvyCube_Connector_Helper_Authorization
     */
    protected function getAuthHelper()
    {
        return Mage::helper('wCube/authorization');
    }

    public function dispatch($action)
    {
        $this->_authorize();
        try {
            $apiResource = Mage::getModel('wCube/api_' . $action);
            $method = strtolower($this->getRequest()->getMethod()) . "Method";
            try {
                $parameters = $this->_getParameters();
            } catch (Exception $e) {
                Mage::app()->getResponse()
                    ->setHeader('HTTP/1.1', '404 Not Found')
                    ->setBody($e->getMessage())
                    ->sendResponse();
                exit;
            }

            if (!$apiResource) {
                Mage::app()->getResponse()
                    ->setHeader('HTTP/1.1', '404 Not Found')
                    ->setBody('<h1>404: Api resource not found</h1>')
                    ->sendResponse();
                exit;
            } else {
                if (($this->_isEncryptionRequired($action, $this->getRequest()->getMethod())
                    && !$this->getAuthHelper()->getCurrentKey('e'))
                ) {
                    Mage::app()->getResponse()
                        ->setHeader('HTTP/1.1', '401 Unauthorized')
                        ->setBody('no encryption key')
                        ->sendResponse();
                    exit;
                } elseif (!is_callable(array($apiResource, $method))) {
                    Mage::app()->getResponse()
                        ->setHeader('HTTP/1.1', '404 Not Found')
                        ->setBody('<h1>404: method is not supported for this Api resource</h1>')
                        ->sendResponse();
                    exit;
                }
            }

            Varien_Profiler::start(self::PROFILER_KEY . '::predispatch');
            $this->preDispatch();
            Varien_Profiler::stop(self::PROFILER_KEY . '::predispatch');

            if ($this->getRequest()->isDispatched()) {
                /**
                 * preDispatch() didn't change the action, so we can continue
                 */
                if (!$this->getFlag('', self::FLAG_NO_DISPATCH)) {
                    $_profilerKey = self::PROFILER_KEY . '::' . $this->getFullActionName();

                    Varien_Profiler::start($_profilerKey);
                    $response = $apiResource->init($parameters)->$method();
                    $this->formatResponse($response, $this->_isEncryptionRequired($action, $this->getRequest()->getMethod()));
                    Varien_Profiler::stop($_profilerKey);

                    Varien_Profiler::start(self::PROFILER_KEY . '::postdispatch');
                    $this->postDispatch();
                    Varien_Profiler::stop(self::PROFILER_KEY . '::postdispatch');
                }
            }
        } catch (Mage_Core_Controller_Varien_Exception $e) {
            // set prepared flags
            foreach ($e->getResultFlags() as $flagData) {
                list($action, $flag, $value) = $flagData;
                $this->setFlag($action, $flag, $value);
            }
            // call forward, redirect or an action
            list($method, $parameters) = $e->getResultCallback();
            switch ($method) {
                case Mage_Core_Controller_Varien_Exception::RESULT_REDIRECT:
                    list($path, $arguments) = $parameters;
                    $this->_redirect($path, $arguments);
                    break;
                case Mage_Core_Controller_Varien_Exception::RESULT_FORWARD:
                    list($action, $controller, $module, $params) = $parameters;
                    $this->_forward($action, $controller, $module, $params);
                    break;
                default:
                    $actionMethodName = $this->getActionMethodName($method);
                    $this->getRequest()->setActionName($method);
                    $this->$actionMethodName($method);
                    break;
            }
        }
    }

    /**
     * get parameters from request which matching with configuration
     *
     * @throws Exception
     * @return array
     */
    private function _getParameters()
    {
        return array_merge(
            $this->_getParametersByAction('default', $this->getRequest()->getMethod()),
            $this->_getParametersByAction($this->getRequest()->getActionName(), $this->getRequest()->getMethod())
        );
    }

    private function _isEncryptionRequired($action, $method)
    {
        /** @var Varien_Simplexml_Element $doNotEncryptNode */
        $doNotEncryptNode = Mage::getConfig()->getNode("default/wCube/donotencrypt");
        if ($doNotEncryptNode && $doNotEncryptNode->hasChildren()) {
            $doNotEncryptArray = $doNotEncryptNode->asArray();
            $action = strtolower($action);
            $method = strtoupper($method);
            return !isset($doNotEncryptArray[$action][$method]);
        }

        return true;
    }

    private function _getParametersByAction($action, $method)
    {
        $parameters = array();
        $result = array();
        /** @var Varien_Simplexml_Element $actionNode */
        $actionNode = Mage::getConfig()->getNode("default/wCube/parameters/{$action}");
        if ($actionNode && $actionNode->hasChildren()) {
            $actionArray = $actionNode->asArray();
            if (array_key_exists($method, $actionArray)) {
                $parameters = $actionArray[$method];
            }
        }
        if (is_array($parameters)) {
            foreach ($parameters as $name => $options) {
                $value = $this->getRequest()->getParam($name, isset($options['default']) ? $options['default'] : null);
                if (isset($option['required']) && $options['required'] && $value === null) {
                    throw new Exception('Missing required parameter:' . $name);
                }
                $result[$name] = $value;
            }
        }

        return $result;
    }

    public function hasAction($action)
    {
        return true;
    }
}
