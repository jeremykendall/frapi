<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://getfrapi.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@getfrapi.com so we can send you a copy immediately.
 *
 * @license   New BSD
 * @copyright echolibre ltd.
 * @package   frapi-admin
 */
class TesterController extends Lupin_Controller_Base
{
    public function init($styles = array())
    {
        $actions = array('index', 'ajax');

        $this->_helper->_acl->allow('admin', $actions);
        //$this->view->addHelperPath('echolibre/View/Helper', 'Zend_View_Helper_FormStatic');
        parent::init($styles);
    }

    public function indexAction()
    {
        $request     = $this->getRequest();
        $form        = new Default_Form_Tester;

        $confModel   = new Default_Model_Configuration();
        if (!$confModel->getKey("api_url")) {
            $this->addInfoMessage(
                "Remember, you can set the default API domain name in " .
                "<a href=\"/configuration\">configuration</a>!"
            );
        }

        if (!class_exists("HttpRequest")) {
            $this->addErrorMessage(
                "HttpRequest class was not found &#8212; the " .
                "<a href=\"http://pecl.php.net/package/pecl_http\" title=\"PECL HTTP\">" .
                "pecl_http</a> package is required to use the tester."
            );
        }

        $this->view->form = $form;
    }

    public function ajaxAction()
    {
        $this->view  = new Lupin_View();

        $method      = strtolower($this->_request->getParam('method'));
        $query_uri   = trim($this->_request->getParam('query_uri'), '/ ');
        $url         = $this->_request->getParam('url');
        $ssl         = $this->_request->getParam('ssl');
        $extraParams = $this->_request->getParam('param');

        $params      = array();

        if (!empty($extraParams)) {
            foreach ($extraParams as $newParam) {
                $parms                 = explode('=', $newParam, 2);
                if (count($parms) > 1) {
                    list($key, $value) = $parms;
                    $params[$key]      = $value;
                }
            }
        }

        $newMethod = HTTP_METH_GET;

        switch ($method) {
            case 'get':
                $newMethod = HTTP_METH_GET;
                break;
            case 'post':
                $newMethod = HTTP_METH_POST;
                break;
            case 'put':
                $newMethod = HTTP_METH_PUT;
                break;
            case 'delete':
                $newMethod = HTTP_METH_DELETE;
                break;
            case 'head':
                $newMethod = HTTP_METH_HEAD;

                break;
        }

        $email = $this->_request->getParam('email');
        $pass = $this->_request->getParam('secretKey');

        $request_url = 'http' . ($ssl !== null ? 's' : '') . '://' . $url . '/' . $query_uri;

        $httpOptions = array();

        if ($email && $pass) {
            $httpOptions = array(
                'headers'      => array('Accept' => '*/*'),
                'httpauth'     => $email . ':' . $pass,
                'httpauthtype' => HTTP_AUTH_DIGEST,
            );
        }

        $request = new HttpRequest($request_url, $newMethod, $httpOptions);

        if ("post" == $method) {
            $request->addPostFields($params);
        } else {
            $request->addQueryData($params);
        }

        $res = $request->send();

        $responseInfo = $request->getResponseInfo();
        $response = array(
            'request_url'         => $responseInfo['effective_url'],
            'response_headers'    => $this->collapseHeaders($res->getHeaders()),
            'content'             => $res->getBody(),
            'status'              => $res->getResponseCode(),
            'method'              => strtoupper($method),
            'request_post_fields' => http_build_query(
                !is_null($postFields = $request->getPostFields()) ? $postFields : array()
            )
        );

        $this->view->renderJson($response);
    }

    protected function collapseHeaders($headers)
    {
        $header_string = "";
        foreach ($headers as $name => $value) {
            if (is_array($value)) {
                $value = implode("\n\t", $value);
            }

            $header_string .= $name . ": " . wordwrap($value, 45, "\n\t") . "\n";
        }
        return $header_string;
    }
}
