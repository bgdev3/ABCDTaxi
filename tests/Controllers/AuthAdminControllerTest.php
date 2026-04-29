<?php
namespace App\Tests\Controllers;

use App\Controllers\AuthAdminController;
use App\Models\AdminUserModel;
use App\Services\Captcha;
use App\Services\Mailer;
use App\Services\Form;
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

    // #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function testConnexionReussie(): void
    {
        $adminUser = new AdminUser();
        $adminUser->setUsername('admin');
        $adminUser->setIdAdmin(1);
        $adminUser->setPassword(password_hash('Password123!', PASSWORD_DEFAULT));

        // Mock des 5 dépendances dans le bon ordre
        $adminModelMock = $this->createMock(AdminUserModel::class);
        $adminModelMock->expects($this->once())
            ->method('find')
            ->willReturn($adminUser);

        $adminEntityStub = $this->createStub(AdminUser::class); // position #2

        $mailerMock    = $this->createStub(Mailer::class);
        $captchaMock   = $this->createStub(Captcha::class);

     $formMock = $this->createStub(Form::class);
$formMock->method('validatePost')->willReturn(true);

foreach ([
    'startForm', 'startFieldset', 'startDiv',
    'addInput', 'addLabel',
    'endDiv', 'endFieldset', 'endForm'
] as $method) {
    $formMock->method($method)->willReturnSelf();
}

$formMock->method('getFormElements')->willReturn([]);

        $_POST = [
            'email'              => 'admin@test.com',
            'password'           => 'Password123!',
            'token'              => 'test_token',
            'recaptcha_response' => 'fake_recaptcha',
        ];

        $controller = $this->getMockBuilder(AuthAdminController::class)
            ->setConstructorArgs([
                $adminModelMock,
                $adminEntityStub, // AdminUser en #2
                $mailerMock,      // Mailer en #3
                $captchaMock,     // Captcha en #4
                $formMock,        // Form en #5
            ])
            ->onlyMethods(['render'])
            ->getMock();
        
        $controller->expects($this->once())
            ->method('render')
            ->willReturnCallback(function () {});

        $controller->index();

        $this->assertArrayHasKey('username_admin', $_SESSION);
        $this->assertEquals('admin', $_SESSION['username_admin']);
        $this->assertArrayHasKey('id_admin', $_SESSION);
        $this->assertEquals(1, $_SESSION['id_admin']);
    }
}