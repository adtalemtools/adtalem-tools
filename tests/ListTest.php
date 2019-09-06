<?php

namespace DrushUsersCommands\Tests;

use UsersCommands\Tests\TestBase;

class ListTestCase extends TestBase
{
    /**
     * Set up each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->drush('role:create', ['editor'], $this->siteOptions);
        $this->drush('user:create', ['foo'], $this->siteOptions);
        $this->drush('user:create', ['bar'], $this->siteOptions);
        $this->drush('user:block', ['bar'], $this->siteOptions);
        $this->drush('user:role:add', ['editor', 'foo'], $this->siteOptions);
    }

    /**
     * Test all users are returned.
     */
    public function testAllUsers()
    {
        $this->drush('users:adminlist', [], $this->siteOptions);

        $output = $this->getOutput();
        $this->assertContains('foo', $output);
        $this->assertContains('bar', $output);
        $this->assertContains('admin', $output);
        $this->assertNotContains('anonymous', $output);
    }

    /**
     * Test role option.
     */
    public function testUsersReturnedByRole()
    {
        $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + ['roles' => 'content_admin']
        );

        $output = $this->getOutput();
        $this->assertContains('foo', $output);
        $this->assertNotContains('bar', $output);
        $this->assertNotContains('admin', $output);
    }

    /**
     * Test status option.
     */
    public function testUsersReturnedByStatus()
    {
        $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + ['status' => 'blocked']
        );

        $output = $this->getOutput();
        $this->assertNotContains('foo', $output);
        $this->assertContains('bar', $output);
        $this->assertNotContains('admin', $output);
    }

    /**
     * Test last-login option.
     */
    public function testUsersReturnedByLogin()
    {
        // Update the login time for user 1. Drush user:login does not do this.
        $now = time();

        $this->drush(
            'sql:query',
            ["UPDATE users_field_data SET login={$now} WHERE uid=1;"],
            $this->siteOptions
        );

        $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + ['last-login' => 'today']
        );

        $output = $this->getOutput();
        $this->assertContains('admin', $output);
        $this->assertNotContains('foo', $output);
        $this->assertNotContains('bar', $output);
    }

    /**
     * Test status and role options in combination.
     */
    public function testUsersReturnedByStatusRole()
    {
        $this->drush('user:create', ['baz'], $this->siteOptions);
        $this->drush('user:block', ['baz'], $this->siteOptions);
        $this->drush('user:role:add', ['editor', 'baz'], $this->siteOptions);

        $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + ['roles' => 'content_admin', 'status' => 'blocked']
        );

        $output = $this->getOutput();
        $this->assertNotContains('foo', $output);
        $this->assertNotContains('bar', $output);
        $this->assertNotContains('admin', $output);
        $this->assertContains('baz', $output);
    }

    /**
     * Test status, role and last-login options in combination.
     */
    public function testUsersReturnedByStatusRoleLogin()
    {
        // Update the login time for user 1. Drush user:login does not do this.
        $now = time();

        $this->drush(
            'sql:query',
            ["UPDATE users_field_data SET login={$now} WHERE uid=1;"],
            $this->siteOptions
        );

        // Create another administrator.
        $this->drush('user:create', ['baz'], $this->siteOptions);
        $this->drush('role:create', ['administrator'], $this->siteOptions);

        $this->drush(
            'user:role:add',
            ['administrator', 'baz'],
            $this->siteOptions
        );

        // Give the admin user the administrator role.
        $this->drush(
            'user:role:add',
            ['administrator', 'admin'],
            $this->siteOptions
        );

        $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + [
                'roles' => 'administrator',
                'status' => 'active',
                'last-login' => 'today',
            ]
        );

        $output = $this->getOutput();
        $this->assertNotContains('baz', $output);
        $this->assertNotContains('foo', $output);

        // If baz is not in the output then 'admin' has to match user name.
        $this->assertContains('admin', $output);
    }

    /**
     * Test validation.
     */
    public function testValidation()
    {
        // Role 'garbage' does not exist.
        $result = $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + ['roles' => 'garbage'],
            null,
            null,
            self::EXIT_ERROR
        );

        $this->assertEquals(1, $result);

        // Status 'garbage' does not exist;
        $result = $this->drush(
            'users:adminlist',
            [],
            $this->siteOptions + ['status' => 'garbage'],
            null,
            null,
            self::EXIT_ERROR
        );

        $this->assertEquals(1, $result);
    }
}
