<?php

namespace Tests\Controllers;

use App\Controllers\UserController;
use App\Core\Captcha;
use App\Entities\Client;
use App\Models\ClientModel;
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

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function testConnexionReussie(): void
    {
        // 🔹 User simulé (IMPORTANT : doit matcher ton controller)
        $user = new Client();
        $user->setSurname('admin');
        $user->setIdClient(1);

        // Simule ce que retourne l'attribut en BDD (IMPORTANT : doit matcher ton controller)
        // Ici l'attribut n'a pas le meme nom que la propriété privée, c'est pour ça que je l'ai mis en public et
     
        $user = new Client();
        $user->setSurname('admin');
        $user->setIdClient(1);
        $user->num_client = password_hash('Password123!', PASSWORD_DEFAULT); // propriété publique mappée depuis la BDD
        // 🔹 Mock model
        $clientModelMock = $this->createStub(ClientModel::class);
        $clientModelMock->method('find')->willReturn($user);

        // 🔹 Mock captcha
        $captchaMock = $this->createStub(Captcha::class);
        $captchaMock->method('verify')->willReturn(true);

        // 🔹 POST simulé (IMPORTANT: idUser = ton controller)
        $_POST = [
            'email' => 'admin@test.com',
            'idUser' => 'Password123!',
            'token' => 'test_token',
            'recaptcha_response' => 'fake'
        ];

        // 🔹 Controller mock
        $controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$clientModelMock, $captchaMock])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->method('render')->willReturnCallback(function () {});

        // 🔹 Action
        $controller->index();

        // 🔥 ASSERTIONS ALIGNÉES AVEC TON CODE
        $this->assertArrayHasKey('username', $_SESSION);
        $this->assertEquals('admin', $_SESSION['username']);

        $this->assertArrayHasKey('id_user', $_SESSION);
        $this->assertEquals(1, $_SESSION['id_user']);
    }
}