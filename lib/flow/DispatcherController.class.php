<?php

/*
 * 	Yamp52 - Yet Another Magical PHP framework
 * 	http://code.google.com/p/yamp52/
 * 	
 * 	Copyright (C) 2009, Krzysztof Drezewski <krzych@krzych.eu>
 * 	
 * 	This program is free software; you can redistribute it and/or modify
 * 	it under the terms of the GNU General Public License as published by
 * 	the Free Software Foundation; either version 3 of the License, or
 * 	(at your option) any later version.
 * 	
 * 	This program is distributed in the hope that it will be useful,
 * 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 * 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * 	GNU General Public License for more details.
 * 	
 * 	You should have received a copy of the GNU General Public License
 * 	along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Main dispatcher controller.
 *
 * This is called from web-based front controller and is responsible
 * for proper controller/action mapping
 *
 * @package Yamp
 * @author krzych
 *
 */
class DispatcherController {

    private $getParams;
    private $postParams;
    private $flowName;
    private $actionName;
    private $projectUri;
    private static $productionState;
    private static $maxSavedFlowSteps = 50;
    private $resourcesArray = array();
    private $uriParts;
    /**
     * @Service("Session")
     * @var Session
     */
    private $SessionContainer;


    public function __construct($uri, $resourcesArray, $productionState) {
        //this array keeps mappings resource-prefix -> directory
        $this->resourcesArray = $resourcesArray;
        self::$productionState = $productionState;

        $this->projectUri = $uri;
        $sc = MainFactory::getServiceContainer()->getService("smarty")->assign('projectUri', ($uri) ? "/" . $uri : "");
    }


    /**
     * main function for saving/restoring GET/POST parameters and
     * to determinig Controller
     * @return unknown_type
     */
    public function flowControl() {
        $flowData = $this->determineFlowAndActionNames();

        $this->dispatchResources();

        if (!empty($_GET)) {
            $this->persistGetParams();
        }

        if (!empty($_POST)) {
            $this->persistPostParams();
            $this->persistFlowParams($flowData);

            header('Location: /' . $this->projectUri);
            die;
        }

        $this->retrieveGetParams();
        $this->retrievePostParams();

        /**
         * Nie przychodzimy z POSTA; kto� wpisa� w url inny kontroler i akcj�
         * ni� zapisana w sesji lub przechodzimy z refererem z innego kontrolera - pozw�l przej��
         */
        if (empty($this->postParams)) {
            $this->persistFlowParams($flowData);
        }
        $this->retrieveFlowParams();
        $this->dispatchToController();
    }

    /**
     * For now this is the heart function of loading controllers
     * @return unknown_type
     */
    private function dispatchToController() {

        if (!$this->flowName) {
            $this->flowName = 'main';
        }
        if (!$this->actionName) {
            $this->actionName = 'default';
        }
        addIncludePath(array('lib/flow/' . strtolower($this->flowName)));

        $controllerName = ucwords($this->flowName) . "FlowController";
        $FlowController = MainFactory::getServiceContainer()->FlowController;

        try {
            $Controller = MainFactory::getServiceContainer()->$controllerName;
            $FlowController->registerController($Controller);

            //validation of $_POST parameters
            $outcome = $FlowController->validateAction($this->actionName . "Validate", $this->getPostParams());
            if ($outcome === false) {
                //oups _POST has errors... so go back!
                $this->flowRewind();
           } else {
                $FlowController->retreiveValidationErrors();
                $FlowController->callAction($this->actionName . "Action");
           }
        } catch (Exception $e) {
            DispatcherController::exceptionEnvelope($e);
        }
    }

    /**
     * Goes back by one action
     */
    private function flowRewind() {
        //cofamy o 1 akcj� wstecz, ale �eby nie wpa�� w p�tle to musi by� r�na akcja od tera�niejszej
        $oldFlowName = $this->flowName;
        $oldActionName = $this->actionName;
        $step = 0;
        $this->retrieveFlowParams(1);
        while ($this->flowName == $oldFlowName && $this->actionName == $oldActionName) {
            $this->retrieveFlowParams(1);
            $step++;
            //hold your horses...
            if ($step > 2) {
                $this->flowName = '';
                $this->actionName = '';
                break;
            }
        }
        $this->persistFlowParams(array('flowName' => $this->flowName,
            'actionName' => preg_replace("/Action$/", "", $this->actionName)));
        //pretend that is normal request
        $_POST = $this->getPostParams();
        $this->persistPostParams();

        //go teddy - let me see my old view
        $this->dispatchToController();
    }

    /**
     * Determines flow and action names based od $_SERVER[REQUEST_URI] variable
     * @return mixed array with flow and action names
     */
    private function determineFlowAndActionNames() {
        //determine flow and action names
        $uriParts = explode("/", $_SERVER['REQUEST_URI']);
        if (!empty($uriParts)) {

            foreach ($uriParts as $idx => $part) {
                $part = preg_replace("/[^a-zA-Z0-9\_]*/i", "", $part);
                if ($part == $this->projectUri || $part == '') {
                    array_shift($uriParts);
                }
            }
            //this is usefull - we will need it later
            $this->uriParts = $uriParts;

            return array('flowName' => $uriParts[0], 'actionName' => $uriParts[1]);
        }
    }

    /**
     * Retrieves $_POST parameters from $_SESSION
     * @return mixed $_POST parameters
     */
    public function getPostParams() {
        return $this->postParams;
    }

    /**
     * Retrieves $_GET parameters from $_SESSION
     * @return mixed $_GET parameters
     */
    public function getGetParams() {
        return $this->getParams;
    }

    /**
     * Saves flow and action names to user $_SESSION
     * @return unknown_type
     */
    private function persistFlowParams($flowData) {
        $savedFlow = $this->SessionContainer->get('flowParams');
        if (!$flowData['flowName']) {
            $flowData['flowName'] = 'main';
        }
        if (!$flowData['actionName']) {
            $flowData['actionName'] = 'default';
        }
        $savedFlow[] = $flowData;
        if (sizeof($savedFlow) > DispatcherController::$maxSavedFlowSteps) {
            array_shift($savedFlow);
        }
        $this->SessionContainer->save('flowParams', $savedFlow);
    }

    /**
     * Retrieves flow and action names from user $_SESSION
     * @return mixed
     */
    private function retrieveFlowParams($stepBack=null) {
        $da = $this->SessionContainer->get('flowParams');
        for ($x = 0; $x < $stepBack; $x++) {
            array_pop($da);
        }
        $this->SessionContainer->save('flowParams', $da);

        $this->flowName = $da[sizeof($da) - 1]['flowName'];
        $this->actionName = $da[sizeof($da) - 1]['actionName'];
    }

    /**
     * Retrieves $_GET parameters from user $_SESSION
     * @return unknown_type
     */
    private function retrieveGetParams() {
        $this->getParams = $this->SessionContainer->get('getParams');
        $this->SessionContainer->remove('getParams');
    }

    /**
     * Saves $_GET parameters to user $_SESSION
     * @return unknown_type
     */
    private function persistGetParams() {
        $this->SessionContainer->save('getParams', $_GET);
    }

    /**
     * Retrieves $_POST paramaters from user $_SESSION
     * @return unknown_type
     */
    private function retrievePostParams() {
        $this->postParams = $this->SessionContainer->get('postParams');
        $this->SessionContainer->remove('postParams');
    }

    /**
     * Saves $_POST parameters to user $_SESSION
     * @return unknown_type
     */
    private function persistPostParams() {
        $this->SessionContainer->save('postParams', $_POST);
    }

    /**
     * Prints any file requested by the page
     * @return unknown_type
     */
    private function dispatchResources() {
        foreach ($this->resourcesArray as $idx => $res) {

            //little override for various files (favicon.ico, robots.txt and so on)
            if ( preg_match("/^[a-z]+\.(ico|txt)$/", $this->uriParts[0]) ) {
                $this->uriParts[1] = 'resources';
                $this->uriParts[2] = $this->uriParts[0];
                $this->uriParts[0] = 'resources';
            }
            
            if ($res['resource'] == $this->uriParts[0]) {

                $parts = $this->uriParts;
                $parts[0] = $res['directory'];

                $fileName = implode(DIRECTORY_SEPARATOR, $parts);
                if (file_exists($fileName)) {
                    $fh = fopen($fileName, 'r');
                    if ($fh) {
                        while (!feof($fh)) {
                            echo fgets($fh, 255);
                        }
                        fclose($fh);
                    }
                }
                die;
            }
        }
    }

    /**
     * This method is global exception handler.
     * Here we determine if throw an Exception or maybe redirect user to another controller
     *
     * @param Exception $e
     */
    public static function exceptionEnvelope($e) {
        /**
         * @var Configuration
         */
        $Conf = MainFactory::getServiceContainer()->Configuration;

        /**
         * @var Authentication
         */
        $Authentication = MainFactory::getServiceContainer()->AuthenticationService;

        /**
         * @var Session
         */
        $Session = MainFactory::getServiceContainer()->Session;

        if (is_object($e)) {
            $ref = new ReflectionClass($e);
            switch ($ref->name) {
                /**
                 * Are we handling this kind of exception or just rethrow it?
                 */
                case 'ActionInsufficientPermissionsException':
                    $Session->save('noperm_message',
                            new Message(MESSAGE_ERR, "Insufficient permissions"));

                    if (!$Authentication->isLogged() && $Conf->get("permission.denied") == 'login') {
                        header("Location: /" . $Conf->get("www.uri") . "/authenticate/login");
                    } elseif ($Conf->get("permission.denied") == 'noperm') {
                        header("Location: /" . $Conf->get("www.uri") . "/authenticate/noperm");
                    } else {
                        throw $e;
                    }
                    die;
                    break;

                case 'InvalidArgumentException':
                    header("HTTP/1.0 404 Not Found");
                    die;
                    break;

                default:
                    throw $e;
            }
            die;
        }
    }

}