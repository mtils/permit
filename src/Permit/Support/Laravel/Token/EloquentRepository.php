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
use Permit\Token\TokenCollisionException;

class EloquentRepository implements Repository
{

    use GeneratesTokens;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $tokenModel;

    /**
     * @var array
     **/
    protected $tokenCache = [];

    /**
     * Tweak the current date
     * @var \DateTime
     **/
    protected $now;

    /**
     * @param \Illuminate\Database\Eloquent\Model $tokenModel
     * @param \Permit\Random\GeneratorInterface $randomGenerator
     **/
    public function __construct(Model $tokenModel,
                                RandomGenerator $randomGenerator)
    {
        $this->tokenModel = $tokenModel;
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

        if ($tokenModel = $this->getModelByUser($user, $type)) {
            if ($type == self::REMEMBER) {
                $user->setRememberToken($tokenModel->token);
            }
            return $tokenModel->token;
        }

        return '';

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

        if (!$tokenModel = $this->getModelByToken($token, $type)) {
            throw new TokenNotFoundException("Token $token not found");
        }

        if ($this->isExpired($tokenModel)) {
            throw new TokenExpiredException(
                "Token $token expired",
                $tokenModel->expiresAt
            );
        }

        return $tokenModel->user_id;

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

        $this->deleteExistingToken($user, $type);

        $token = $this->generateToken($type);

        $attributes = [
            'user_id' => $user->getAuthId(),
            'token_type' => $type,
            'token' => $token
        ];

        if ($expiresAt) {
            $attributes['expires_at'] = $expiresAt;
        }

        $tokenModel = $this->createModel($attributes);

        $tokenModel->save();

        if ($type == self::REMEMBER) {
            $user->setRememberToken($token);
        }

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

        if (!$tokenModel = $this->getModelByUser($user, $type)) {
            return $this->create($user, $type, $expiresAt);
        }

        $token = $this->generateToken($type);

        $tokenModel->token = $token;

        if ($expiresAt) {
            $tokenModel->expiresAt = $expiresAt;
        }

        if ($type == self::REMEMBER) {
            $user->setRememberToken($token);
        }

        $tokenModel->save();

        return $token;

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

        if (!$tokenModel = $this->getModelByToken($token, $type)) {
            return false;
        }

        if ($type == self::REMEMBER) {
            $user->setRememberToken($tokenModel->token);
        }

        return ($tokenModel->user_id == $user->getAuthId());

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

        if (!$tokenModel = $this->getModelByUser($user, $type)) {
            return true;
        }

        return $tokenModel->delete();

    }

    /**
     * {@inheritdoc}
     *
     * @param int $type (see self::REMEMBER...) (optional)
     * @return void
     **/
    public function purgeExpired($type=null)
    {

        $now = $this->getNow();

        $query = $this->tokenModel->newQuery()
                                  ->where('expires_at','<=', $now)
                                  ->whereNotNull('expires_at');

        if ($type) {
            $query->where('token_type', $type);
        }

        $query->delete();

    }

    public function getNow()
    {

        if (!$this->now) {
            return new DateTime;
        }

        return $this->now;
    }

    public function setNow(DateTime $now)
    {
        $this->now = $now;
        return $this;
    }

    protected function createModel(array $attributes=[])
    {
        return $this->tokenModel->newInstance($attributes);
    }

    protected function getModelByUser(User $user, $type)
    {
        $query = $this->tokenModel->newQuery()
                                  ->where('user_id', $user->getAuthId())
                                  ->where('token_type', $type);

        return $query->first();
    }

    protected function getModelByToken($token, $type=null)
    {
        $query = $this->tokenModel->newQuery()
                                  ->where('token', $token)
                                  ->where('token_type', $type);

        return $query->first();
    }

    protected function isExpired(Model $tokenModel)
    {
        $now = $this->getNow();
        return ($tokenModel->expires_at && $tokenModel->expires_at < $now);
    }

    protected function deleteExistingToken(User $user, $type)
    {

        if (!$oldTokenModel = $this->getModelByUser($user, $type)) {
            return;
        }

        if ($this->isExpired($oldTokenModel)) {
            $oldTokenModel->delete();
            return;
        }

        throw new TokenCollisionException(
            $oldTokenModel->expires_at,
            $oldTokenModel->token
        );
    }

}