<?php

use PrestaSafe\PrettyBlocks\DataPersister\ConnectedEmployeeDataPersister;
use PrestaSafe\PrettyBlocks\DataProvider\ConnectedEmployeeDataProvider;

class PrettyBlocksConnectedEmployeeModuleFrontController extends ModuleFrontController
{
    public function displayAjaxEmployeeAlert()
    {
        $sessionId = Tools::getValue('session_id');

        if (!$sessionId) {
            $this->ajaxDie(json_encode([
                'success' => false,
            ]));
        }

        ConnectedEmployeeDataPersister::update($sessionId);

        $connectedEmployees = ConnectedEmployeeDataProvider::get();
        if (null === $connectedEmployees) {
            $this->ajaxDie(json_encode([
                'success' => false,
            ]));
        }

        $this->ajaxDie(json_encode([
            'success'           => true,
            'number_of_editors' => $connectedEmployees
        ]));
    }
}
