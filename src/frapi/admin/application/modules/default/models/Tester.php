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
class Default_Model_Tester extends Lupin_Model_DB
{
    /**
     * Build a form
     *
     * This method is used to build extra "param" form fields.
     *
     * @return string The newly formed "form".
     */
    public function buildForm()
    {
        return '
        <div id="params-container">
            <div id="params">
            </div>
            <em>Add a parameter ("name=value")</em>:<img src="/images/add.png" id="add" />
        </div>';
    }
}