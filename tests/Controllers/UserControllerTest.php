<?php

namespace Tests\Controllers;

use App\Controllers\UserController;
use App\Services\Captcha;
use App\Entities\Client;
use App\Models\ClientModel;
use App\Services\Form;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
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

    public function testConnexionReussie(): void
    {
        // Client fictif
        $user = new Client();
        $user->setSurname('admin');
        $user->setIdClient(1);
        $user->num_client = password_hash('Password123!', PASSWORD_DEFAULT);

        // Mock ClientModel
        $clientModelMock = $this->createStub(ClientModel::class);
        $clientModelMock->method('find')->willReturn($user);

        // Mock Captcha
        $captchaMock = $this->createStub(Captcha::class);
        $captchaMock->method('verify')->willReturn(true);

        // Mock Form — validatePost doit retourner true + méthodes de rendu
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

        // POST simulé — idUser et non password (cf. UserController ligne 38)
        $_POST = [
            'email'              => 'admin@test.com',
            'idUser'             => 'Password123!', // ← le champ s'appelle idUser !
            'token'              => 'test_token',
            'recaptcha_response' => 'fake',
        ];

        $controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$formMock, $clientModelMock, $captchaMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->willReturnCallback(function () {});

        $controller->index();

        $this->assertArrayHasKey('username', $_SESSION);
        $this->assertEquals('admin', $_SESSION['username']);
        $this->assertArrayHasKey('id_user', $_SESSION);
        $this->assertEquals(1, $_SESSION['id_user']);
    }
}