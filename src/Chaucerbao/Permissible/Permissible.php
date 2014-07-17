<?php
namespace Chaucerbao\Permissible;

trait Permissible
{
    private $roles = [];

    private $permissions = [];

    protected function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    protected function allow($role, $action, $target, $allowed = true)
    {
        $this->permissions[$role][$target][$action] = $allowed;
    }

    public function can($action, $target)
    {
        $roles = $this->roles;
        $object = null;

        if (is_object($target)) {
            $object = $target;
            $target = get_class($object);
        }

        foreach ($roles as $role) {
            if (!isset($this->permissions[$role][$target][$action])) {
                continue;
            }

            $allowed = $this->permissions[$role][$target][$action];

            if (is_callable($allowed)) {
                $allowed = call_user_func($allowed, $object);
            }

            if ($allowed) {
                return true;
            }
        }

        return false;
    }
}
