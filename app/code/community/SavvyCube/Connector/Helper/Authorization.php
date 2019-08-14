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
class SavvyCube_Connector_Helper_Authorization extends Mage_Core_Helper_Abstract
{
    const CONSUMER_KEY_LENGTH = 10;

    /**
     * function generates consumer key, encrypts it and tries to send
     * to $consumerEndpoint. Returns operation status.
     *
     * @return bool
     */
    public function initConsumerKey()
    {
        $plainConsumerKey = $this->generateNewKey();
        $eKey = $this->generateNewEKey();
        $this->setCurrentKey($plainConsumerKey);
        $this->setCurrentKey($eKey, 'e');
        Mage::getConfig()->cleanCache();
        Mage::app()->reinitStores();
        return true;
    }

    /**
     * function generates new consumer key
     *
     * @return string
     */
    private function generateNewKey()
    {
        return mcrypt_create_iv(self::CONSUMER_KEY_LENGTH);
    }

    /**
     * function generates new traffic encryption key
     *
     * @return string
     */
    private function generateNewEKey()
    {
        return mcrypt_create_iv(16);
    }

    /**
     * function returns certificate path from current module configuration
     *
     * @return string
     */
    private function getCert()
    {
        return
            Mage::getModuleDir("", 'SavvyCube_Connector') . DS .
            str_replace(
                '/',
                DS,
                (string) Mage::getStoreConfig('wCube/cube_crypt/open_key_file_path')
            );
    }

    /**
     * function generates module activation url
     *
     * @return string url to activate model
     */
    public function getActivateUrl()
    {
        $url = $this->getConsumerEndpoint()
        . "datasources/?act=connect&type=0"
        . "&url=" . urlencode(Mage::getBaseUrl())
        . "&secure_url=" . urlencode(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true))
        . "&key=" . rawurlencode($this->getCurrentEncryptedKey())
        . "&e_key=" . rawurlencode($this->getCurrentEncryptedKey('e'));
        return $url;
    }

    /**
     * return consumer endpoint
     *
     * @return string
     */
    private function getConsumerEndpoint()
    {
        return (string) Mage::getStoreConfig('wCube/cube_crypt/consumer_endpoint');
    }

    /**
     * store consumer secret in configuration with encryption
     */
    private function setCurrentKey($key, $type = 'consumer')
    {
        Mage::getConfig()->saveConfig("cube_crypt/{$type}_secret", Mage::helper('core')->encrypt($key));
        $rsaProvider = new Zend_Crypt_Rsa(array('certificatePath' => $this->getCert()));
        $encryptedKey = $rsaProvider->encrypt($key, $rsaProvider->getPublicKey(), Zend_Crypt_Rsa::BASE64);
        Mage::getConfig()->saveConfig("cube_crypt/{$type}_encrypted", $encryptedKey);
    }

    /**
     * return consumer encrypted secret from configuration in plain text
     *
     * @return string
     */
    private function getCurrentEncryptedKey($type = 'consumer')
    {
        return (string)Mage::getStoreConfig("cube_crypt/{$type}_encrypted");
    }

    /**
     * return current consumer secret from configuration in plain text
     *
     * @return string
     */
    public function getCurrentKey($type = 'consumer')
    {
        return Mage::helper('core')->decrypt((string)Mage::getStoreConfig("cube_crypt/{$type}_secret"));
    }

    /**
     * function checks if request is valid
     *
     * @param Mage_Core_Controller_Request_Http $request request for checking
     *
     * @return bool
     */
    public function checkRequest()
    {
        if (isset($_SERVER['HTTP_X_WSSE'])) {
            $xWsse = $this->parseToken($_SERVER['HTTP_X_WSSE']);
            if ($xWsse) {
                /** @var SavvyCube_Connector_Model_Nonce $nonceModel */
                $nonceModel = Mage::getModel('wCube/nonce');
                $xWsse['Nonce'] = urldecode($xWsse['Nonce']);
                $xWsse['Created'] = urldecode($xWsse['Created']);
                if ($nonceModel->checkNonce($xWsse['Nonce'], $xWsse['Created'])) {
                    $calculatedDigest = base64_encode((sha1($xWsse['Nonce'] . $xWsse['Created'] . $this->getCurrentKey())));
                    return $calculatedDigest === $xWsse['SecretDigest'];
                }
            }
        }
        return false;
    }


    private function parseToken($token)
    {
        $wsse = '/UsernameToken Username="([^"]+)", SecretDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"/';
        if (1 === preg_match($wsse, $token, $matches)) {
            array_shift($matches);
            $map = array('Username', 'SecretDigest', 'Nonce', 'Created');
            return array_combine($map, $matches);
        }
        return false;
    }

    /**
     * return nonce lifeTime value from current module configuration
     *
     * @param bool $secondsFormat return in seconds if true given
     *
     * @return int
     */
    public function getNonceLifetime($secondsFormat = false)
    {
        $lifeTimeInMinutes = (string) Mage::getStoreConfig('wCube/cube_crypt/nonce_lifetime');

        if ($secondsFormat) {
            return $lifeTimeInMinutes * 60;
        }

        return $lifeTimeInMinutes;
    }
}
