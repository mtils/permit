<?php namespace Permit\Registration;

use Permit\User\UserInterface;

interface ActivatableUserInterface extends UserInterface{

    public function isActivated();

    public function activate();

    public function deactivate();

}