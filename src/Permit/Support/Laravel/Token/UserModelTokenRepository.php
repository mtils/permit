<?php namespace Permit\Support\Laravel\Token;

use DateTime;
use Illuminate\Database\Eloquent\Model;

use Permit\Token\RepositoryInterface as Repository;
use Permit\Token\GeneratesTokens;
use Permit\User\UserInterface as User;
use Permit\Random\GeneratorInterface as RandomGenerator;
use Permit\Token\TokenExpiredException;
use Permit\Token\TokenInvalidException;
use Permit\Token\TokenNotFoundException;

class UserModelTokenRepository implements Repository
{

    use GeneratesTokens;

    public $rememberKey = 'remember_token';

    public $activationKey = 'activation_code';

    public $passwordResetKey = 'reset_password_code';

    public $oauthKey = 'oauth_token';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $userModel;


    /**
     * @param \Illuminate\Database\Eloquent\Model $userModel
     *
     **/
    public function __construct(Model $userModel,
                                RandomGenerator $randomGenerator)
    {
        $this->userModel = $userModel;
        $this->setRandomGenerator($randomGenerator);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @return string $token
     **/
    public function get(User $user, $type)
    {
        $key = $this->getKeyForType($type);
        return $user->{$key};
    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     * @param int $type (see self::REMEMBER)
     * @return mixed
     **/
    public function getAuthIdByToken($token, $type)
    {

        if (!$this->isValid($token, $type)) {
            throw new TokenInvalidException("Token $token is invalid");
        }

        $key = $this->getKeyForType($type);

        if (!$user = $this->userModel->where($key, $token)->first()) {
            throw new TokenNotFoundException("Token $token not found");
        }

        return $user->getAuthId();

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param \DateTime $expiresAt Set a custom expiry date
     * @return string The generated token
     **/
    public function create(User $user, $type, DateTime $expiresAt=null)
    {

        $key = $this->getKeyForType($type);
        $token = $this->generateToken($type);

        $user->{$key} = $token;
        $user->save();

        return $token;

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param \DateTime $expiresAt Set a custom expiry date
     * @return string The generated token
     **/
    public function update(User $user, $type, DateTime $expiresAt=null)
    {
        // No difference in this implementation
        return $this->create($user, $type, $expiresAt);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param string $token
     * @param int $type (see self::REMEMBER...)
     * @return bool
     **/
    public function exists(User $user, $token, $type)
    {

        $key = $this->getKeyForType($type);

        return ($user->{$key} == $token);

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param string $token (optional)
     * @return bool
     **/
    public function invalidate(User $user, $type, $token=null)
    {
        $key = $this->getKeyForType($type);
        $user->{$key} = null;
        $user->save();
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param int $type (see self::REMEMBER...) (optional)
     * @return void
     **/
    public function purgeExpired($type=null)
    {
        // Better not delete in users table
    }

    /**
     * Returns the user property key for token type $type
     *
     * @param int $type (see self::REMEMBER)
     * @return string
     **/
    protected function getKeyForType($type)
    {
        switch ($type) {
            case self::REMEMBER:
                return $this->rememberKey;
            case self::ACTIVATION:
                return $this->activationKey;
            case self::PASSWORD_RESET:
                return $this->passwordResetKey;
            case self::OAUTH:
                return $this->oauthKey;
        }
    }

}