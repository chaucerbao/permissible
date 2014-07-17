# Permissible
Permissible is a PHP trait you can attach to your user model to keep track of all your application's permissions from one location.

## Getting started
First, attach the trait to your model, `User` in this case
```php
use Chaucerbao\Permissible\Permissible;

class User
{
  use Permissible;
}
```

Then, you'll need to notify Permissible about the roles this user belongs to, somewhere in the `User` class
```php
$this->setRoles(['Author', 'Moderator']);
```

And set a few permission rules for your application
```php
$this->allow('Guest', 'write', 'Comment');
$this->allow('Moderator', 'delete', 'Comment');
```

You can also set permissions that are determined by some logic
```php
// Allow writes only if the Post belongs to the User
$this->allow('Author', 'write', 'Post', function (Post $post) {
  return $this->id === $post->user_id;
});
```

Finally, check permissions wherever you need, somewhere in your application
```php
// Check if the user is allowed to create a comment
$user->can('write', 'Comment');

// Check if the user is allowed to write to this specific instance of $post
$user->can('write', $post);
```

Here's an example of a complete `User` class
```php
use Chaucerbao\Permissible\Permissible;

class User
{
    use Permissible;

    public function __construct()
    {
      $this->setRoles(['Author', 'Moderator']);
      $this->loadPermissions();
    }

    private function loadPermissions()
    {
      $this->allow('Guest', 'write', 'Comment');
      $this->allow('Moderator', 'delete', 'Comment');
      $this->allow('Author', 'write', 'Post', function ($post) {
        return $this->id === $post->user_id;
      });
    }
}
```

## Public methods
Permissible is simple. There are only 3 methods.

### Set roles
Tells Permissible about the roles a specific user belongs to
```php
void setRoles(array $roles)
```

### Set permissions
Tells Permissible what actions a user is allowed to take based on roles
```php
void allow(string $role, string $action, string $target[, mixed $allowed = true])
```
`$allowed` can be a boolean, or a closure that resolves to a boolean

### Check permissions
Runs through the user's roles and checks to see if any of them allow the user to take `$action` on a `$target`
```php
boolean can(string $action, string $target)
```
