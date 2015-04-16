<?php namespace Permit\Authentication\Exception;

use RuntimeException;

/**
 * A LoginException is for all errors which occurs during login and are
 * caused by the user (credentials wrong, wrong auth method, user suspended,
 * banned, whatever)
 *
 **/
class LoginException extends RuntimeException{}
