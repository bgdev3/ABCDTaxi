<?php

namespace App\Tests\Controllers;

use App\Controllers\AuthAdminController;
use App\Models\AdminUserModel;
use App\Core\Captcha;
use App\Entities\AdminUser;
use PHPUnit\Framework\TestCase;

class AuthAdminControllerTest extends TestCase
{
    protected function setUp(): void
    {
         if (session_status() === PHP_SESSION_NONE) {
        session_start();
        }

        $_SESSION = [];
        $_POST = [];
        $_SESSION['token'] = 'test_token';
        $_SESSION['token_time'] = time();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function testConnexionReussie(): void
    {
        $adminUser = new AdminUser();
        $adminUser->setUsername('admin');
        $adminUser->setIdAdmin(1);
        $adminUser->setPassword(password_hash('Password123!', PASSWORD_DEFAULT));

        $adminModelMock = $this->createStub(AdminUserModel::class);

        $adminModelMock->method('find')->willReturn($adminUser);

       $captchaMock = $this->createStub(Captcha::class);
        $captchaMock->method('verify')->willReturn(true);

        $_POST = [
            'email' => 'admin@test.com',
            'password' => 'Password123!',
            'token' => 'test_token',
            'recaptcha_response' => 'fake_recaptcha',
        ];

        $controller = $this->getMockBuilder(AuthAdminController::class)
            ->setConstructorArgs([$adminModelMock, $captchaMock])
            ->onlyMethods(['render'])
            ->getMock();
$controller->method('render')->willReturnCallback(function () {});


        $controller->index();

        $this->assertArrayHasKey('username_admin', $_SESSION);
        $this->assertEquals('admin', $_SESSION['username_admin']);
        $this->assertArrayHasKey('id_admin', $_SESSION);
        $this->assertEquals(1, $_SESSION['id_admin']);
    }
}