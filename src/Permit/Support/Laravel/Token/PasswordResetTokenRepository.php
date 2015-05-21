<?php namespace Permit\Support\Laravel\Token;

use DateTime;
use Permit\User\ProviderInterface;
use Permit\Token\RepositoryInterface as PermitTokenRepository;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class PasswordResetTokenRepository implements TokenRepositoryInterface
{

    /**
     * @var \Permit\Token\RepositoryInterface
     **/
    protected $permitRepository;

    /**
     * @var \Permit\User\ProviderInterface
     **/
    protected $userProvider;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * @param \Permit\Token\RepositoryInterface $permitRepository
     **/
    public function __construct(PermitTokenRepository $permitRepository,
                                ProviderInterface $userProvider,
                                $expires=60)
    {
        $this->permitRepository = $permitRepository;
        $this->userProvider = $userProvider;
        $this->setExpires($expires);
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {

        $expiry = $this->getExpires();

        $expiresAt = $expiry ? $this->now()->modify("+$expiry minutes") : null;

        return $this->permitRepository->create(
            $user,
            PermitTokenRepository::PASSWORD_RESET,
            $expiresAt
        );

    }

    /**
     * {@inheritdoc}
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        return $this->permitRepository->exists(
            $user,
            $token,
            PermitTokenRepository::PASSWORD_RESET
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $token
     * @return void
     */
    public function delete($token)
    {

        $authId = $this->permitRepository->getAuthIdByToken(
            $token,
            PermitTokenRepository::PASSWORD_RESET
        );

        if (!$authId) {
            return;
        }

        $user = $this->userProvider->retrieveByAuthId($authId);

        $this->permitRepository->invalidate(
            $user,
            PermitTokenRepository::PASSWORD_RESET,
            $token
        );

    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function deleteExpired()
    {
        $this->permitRepository->purgeExpired();
    }

    /**
     * Get the number of minutes the token will be valid
     *
     * @return int
     **/
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set the number of minutes the token will be valid
     *
     * @param int $expires
     * @return self
     **/
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    protected function now()
    {
        return new DateTime;
    }

}