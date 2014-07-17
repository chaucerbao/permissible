<?php
class PermissibleTest extends PHPUnit_Framework_TestCase
{
    public function testSetRoles()
    {
        $roles = ['Role 1', '<Role-2>', '"Role Three"'];

        $user = new MockUser;
        $method = $this->getHiddenMethod('MockUser', 'setRoles');

        /* Set the roles */
        $method->invoke($user, $roles);

        $this->assertEquals(['Role 1', '<Role-2>', '"Role Three"'], PHPUnit_Framework_Assert::readAttribute($user, 'roles'));
    }

    public function testAllowBoolean()
    {
        $user = new MockUser;
        $method = $this->getHiddenMethod('MockUser', 'allow');

        /* Set the permissions */
        $method->invoke($user, 'Some role', 'read', 'Some thing');
        $method->invoke($user, 'Some role', 'write', 'Some thing', false);
        $method->invoke($user, 'Another role', 'read', 'Some thing', false);

        $this->assertEquals(['Some role' => ['Some thing' => ['read' => true, 'write' => false]], 'Another role' => ['Some thing' => ['read' => false]]], PHPUnit_Framework_Assert::readAttribute($user, 'permissions'));
    }

    public function testAllowClosure()
    {
        $user = new MockUser;
        $method = $this->getHiddenMethod('MockUser', 'allow');

        /* Set the permissions, allow closure to determine access */
        $method->invoke($user, 'Some role', 'read', 'Some thing', function () {
            return 1 + 2 + 3;
        });

        $permissions = PHPUnit_Framework_Assert::readAttribute($user, 'permissions');

        $this->assertArrayHasKey('Some role', $permissions);
        $this->assertArrayHasKey('Some thing', $permissions['Some role']);
        $this->assertArrayHasKey('read', $permissions['Some role']['Some thing']);
        $this->assertTrue(is_callable($permissions['Some role']['Some thing']['read']));
        $this->assertEquals(6, $permissions['Some role']['Some thing']['read']());
    }

    public function testCanBoolean()
    {
        $user = new MockUser;

        /* Give the user a role */
        $setRoles = $this->getHiddenMethod('MockUser', 'setRoles');
        $setRoles->invoke($user, ['A role']);

        /* Set the permission */
        $allow = $this->getHiddenMethod('MockUser', 'allow');
        $allow->invoke($user, 'A role', 'write', 'MockObject');

        $allowed = $user->can('write', 'MockObject');

        $this->assertTrue($allowed);
    }

    public function testCanClosure()
    {
        $user = new MockUser;
        $object = new MockObject;

        /* Give the user a role */
        $setRoles = $this->getHiddenMethod('MockUser', 'setRoles');
        $setRoles->invoke($user, ['Another role']);

        /* Set the permissions, determine access based on a closure */
        $allow = $this->getHiddenMethod('MockUser', 'allow');
        $allow->invoke($user, 'Another role', 'write', 'MockObject', function ($object) use ($user) {
            return $user->id === $object->user_id;
        });

        /* User owns the object */
        $user->id = 24;
        $object->user_id = 24;
        $allowedA = $user->can('write', $object);

        /* User does not own the object */
        $object->user_id = 76;
        $allowedB = $user->can('write', $object);

        $this->assertTrue($allowedA);
        $this->assertFalse($allowedB);
    }

    /* Use a reflection to access private/protected methods */
    private function getHiddenMethod($class, $method)
    {
        $method = new ReflectionMethod($class, $method);
        $method->setAccessible(true);

        return $method;
    }
}

class MockUser
{
    use Chaucerbao\Permissible\Permissible;

    public $id;
}

class MockObject
{
    public $user_id;
}
