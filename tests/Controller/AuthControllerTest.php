<?php
use PHPUnit\Framework\TestCase;
use App\Controller\AuthController;
use App\Service\RegisterService;
use App\Service\LoginService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthControllerTest extends TestCase
{
    // Mock objects for services and dependencies
    private $registerService;
    private $loginService;
    private $doctrine;
    private $jwtManager;

    public function setUp(): void
    {
        $this->registerService = $this->createMock(RegisterService::class);
        $this->loginService = $this->createMock(LoginService::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
    }

    public function testRegistrationSuccess()
    {
        $authController = new AuthController($this->doctrine);
        $request = Request::create('/api/registration', 'POST', [], [], [], [], json_encode(['email' => 'test@example.com', 'password' => 'password']));
        $this->registerService->expects($this->once())
            ->method('createUser')
            ->willReturn(null);

        $response = $authController->registration($this->registerService, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testLoginSuccess()
    {
        $authController = new AuthController($this->doctrine);
        $request = Request::create('/api/login', 'POST', [], [], [], [], json_encode(['email' => 'test@example.com', 'password' => 'password']));
        $this->loginService->expects($this->once())
            ->method('login')
            ->willReturn('jwt_token');

        $response = $authController->login($request, $this->loginService);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
    }
}
