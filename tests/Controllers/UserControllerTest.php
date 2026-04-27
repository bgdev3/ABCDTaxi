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
        // 🔥 Client fictif
        $user = new Client();
        $user->setSurname('admin');
        $user->setIdClient(1);

        // ⚠️ Attention: propriété publique (ok si ton modèle le fait)
        $user->num_client = password_hash('Password123!', PASSWORD_DEFAULT);

        // 🔥 Mock Model
        $clientModelMock = $this->createStub(ClientModel::class);
        $clientModelMock->method('find')->willReturn($user);

        // 🔥 Mock Captcha
        $captchaMock = $this->createStub(Captcha::class);
        $captchaMock->method('verify')->willReturn(true);

        // 🔥 Mock Form (IMPORTANT -> manquant dans ton test)
        $formMock = $this->createStub(Form::class);

        // 🔥 POST simulé
        $_POST = [
            'email' => 'admin@test.com',
            'password' => 'Password123!',
            'token' => 'test_token',
            'recaptcha_response' => 'fake'
        ];

        // 🔥 Controller avec dépendances CORRECTES
        $controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$formMock, $clientModelMock, $captchaMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->method('render')->willReturnCallback(function () {
            // pas de rendu pendant test
        });

        // 🔥 Action
        $controller->index();

        // 🔥 Assertions session
        $this->assertArrayHasKey('username', $_SESSION);
        $this->assertEquals('admin', $_SESSION['username']);

        $this->assertArrayHasKey('id_user', $_SESSION);
        $this->assertEquals(1, $_SESSION['id_user']);
    }
}