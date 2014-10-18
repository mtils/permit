Permit
======

Permission checking library and nice interfaces for access control.

Permit is a library to provide complete access control with permission-codes. Permission codes are just an array with some codes (strings) as keys and 1,0,-1 as values to allow, inherit and deny.

The main features and goals are:

* Have a nice top-level api to check permissions
* Always return a user, no matter if it is logged in or not (return a guest or system/cron user)
* Allow an secure and transparent "login-as-another-user" functionality
* Splits authentication and simple getUser()/setUser functionality
* Works with almost every other authentication system (Laravel, Sentry,...)

It works almost like Sentry and currently it depends on Sentry because I didnt implement the actual permission code save methods and group support.

Why not Sentry instead of Permit?

* Sentry bybasses almost all auth functionalities of Laravel
* Never use a Facade which is named by a package ;-)

The Facade API (which is mostly used in views):


```php

//Return the current user (always returns a user object)
Auth::user();

// Set the current user
Auth::setUser($user);

// Check if the user is logged in
Auth::loggedIn();

// Check if the current user has access to permission 'cms.access'
Auth::allowed('cms.access');

// Check if user $joe has access to permission 'user.destroy'
Auth::can($joe)->access('user.destroy');

```

Internally the authentication interfaces are splitted into a container:

```php

interface CurrentUser\ContainerInterface{

  public function user();
  
  public function setUser($user);
  
  public function clearUser();

}

```

The next interface is a permission holder:
(This could be a user or a group)

```php

    public function getAuthId();

    // returns 1, 0,-1
    public function getPermissionAccess($code);

    
    public function setPermissionAccess($code, $access);

    // returns an indexed array of all codes
    public function permissionCodes($inherited=true);

    // returns if the user is a guest, same as !Auth::loggedIn()
    public function isGuest();

    // returns if the user is the system itself (like cron or console)
    public function isSystem();

    public function isSuperUser();

```

The extended version of ContainerInterface is the DualContainerInterface, which allows login as a different user.

```php

interface CurrentUser\DualContainerInterface extends ContainerInterface{
    const BOTH = 0;

    const ACTUAL = 1;

    const STACKED = 2;


    // returns the user, which submitted the login form
    public function actualUser();

    public function setActualUser(HolderInterface $user, $persist=true);

    // Returns the user the actual user wants to be temporarly
    public function stackedUser();

    public function setStackedUser(HolderInterface $user, $persist=true);

    // Force the returned user to be the actualUser().
    // This is very handy if you have an admin interface and the user should
    // be the actualUser inside the admin interface and outside of the the
    // stacked one
    // Laravel Route::when('admin*', Auth::forceActual());
    public function forceActual($force=TRUE);

    // Returns if the currently returned user by user() is the actual user
    public function isActual();

    // Resets this container (logout)
    public function reset($type=self::BOTH);
    
}
```
