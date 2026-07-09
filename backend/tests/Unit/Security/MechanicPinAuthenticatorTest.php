<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Garage;
use App\Entity\Mechanic;
use App\Repository\GarageRepository;
use App\Repository\MechanicRepository;
use App\Security\MechanicPinAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[AllowMockObjectsWithoutExpectations]
class MechanicPinAuthenticatorTest extends TestCase
{
    private GarageRepository $garageRepository;
    private MechanicRepository $mechanicRepository;
    private JWTTokenManagerInterface $jwtManager;
    private MechanicPinAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->garageRepository = $this->createMock(GarageRepository::class);
        $this->mechanicRepository = $this->createMock(MechanicRepository::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);

        $this->authenticator = new MechanicPinAuthenticator(
            $this->garageRepository,
            $this->mechanicRepository,
            $this->jwtManager
        );
    }

    /**
     * Test supports returns true for mechanic login endpoint
     */
    public function testSupportsReturnsTrueForMechanicLoginEndpoint(): void
    {
        $request = Request::create('/api/garage/12345678901234/mechanic/login', 'POST');

        $result = $this->authenticator->supports($request);

        $this->assertTrue($result);
    }

    /**
     * Test supports returns false for non-POST requests
     */
    public function testSupportsReturnsFalseForNonPostRequests(): void
    {
        $request = Request::create('/api/garage/12345678901234/mechanic/login', 'GET');

        $result = $this->authenticator->supports($request);

        $this->assertFalse($result);
    }

    /**
     * Test supports returns false for wrong endpoint
     */
    public function testSupportsReturnsFalseForWrongEndpoint(): void
    {
        $request = Request::create('/api/garage/12345678901234/other', 'POST');

        $result = $this->authenticator->supports($request);

        $this->assertFalse($result);
    }

    /**
     * Test supports returns false for invalid URL format
     */
    public function testSupportsReturnsFalseForInvalidUrlFormat(): void
    {
        $request = Request::create('/api/mechanic/login', 'POST');

        $result = $this->authenticator->supports($request);

        $this->assertFalse($result);
    }

    /**
     * Test authenticate throws exception when URL format is invalid
     */
    public function testAuthenticateThrowsExceptionWhenUrlFormatInvalid(): void
    {
        $request = Request::create('/api/invalid/url', 'POST');
        $request->setRequestFormat('json');

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid login URL format');

        $this->authenticator->authenticate($request);
    }

    /**
     * Test authenticate throws exception when request body is invalid
     */
    public function testAuthenticateThrowsExceptionWhenRequestBodyInvalid(): void
    {
        $request = Request::create(
            '/api/garage/12345678901234/mechanic/login',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid request body');

        $this->authenticator->authenticate($request);
    }

    /**
     * Test authenticate throws exception when PIN is missing
     */
    public function testAuthenticateThrowsExceptionWhenPinMissing(): void
    {
        $request = $this->createAuthRequest('12345678901234', []);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Missing PIN');

        $this->authenticator->authenticate($request);
    }

    /**
     * Test authenticate throws exception when garage not found
     */
    public function testAuthenticateThrowsExceptionWhenGarageNotFound(): void
    {
        $request = $this->createAuthRequest('99999999999999', ['pin' => '1234']);

        $this->garageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['siretNumber' => '99999999999999'])
            ->willReturn(null);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Garage not found');

        $this->authenticator->authenticate($request);
    }

    /**
     * Test authenticate throws exception when no mechanics in garage
     */
    public function testAuthenticateThrowsExceptionWhenNoMechanicsInGarage(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $request = $this->createAuthRequest('12345678901234', ['pin' => '1234']);

        $this->garageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['siretNumber' => '12345678901234'])
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([]);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authenticator->authenticate($request);
    }

    /**
     * Test authenticate throws exception when PIN doesn't match any mechanic
     */
    public function testAuthenticateThrowsExceptionWhenPinDoesNotMatch(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic1 = $this->createMock(Mechanic::class);
        $mechanic1->expects($this->once())->method('verifyPin')->with('1234')->willReturn(false);

        $mechanic2 = $this->createMock(Mechanic::class);
        $mechanic2->expects($this->once())->method('verifyPin')->with('1234')->willReturn(false);

        $request = $this->createAuthRequest('12345678901234', ['pin' => '1234']);

        $this->garageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['siretNumber' => '12345678901234'])
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$mechanic1, $mechanic2]);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authenticator->authenticate($request);
    }

    /**
     * Test authenticate returns passport when credentials are valid
     */
    public function testAuthenticateReturnsPassportWhenCredentialsValid(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic1 = $this->createMock(Mechanic::class);
        $mechanic1->method('getId')->willReturn(5);
        $mechanic1->expects($this->once())->method('verifyPin')->with('5678')->willReturn(false);

        $mechanic2 = $this->createMock(Mechanic::class);
        $mechanic2->method('getId')->willReturn(10);
        $mechanic2->expects($this->once())->method('verifyPin')->with('5678')->willReturn(true);

        $request = $this->createAuthRequest('12345678901234', ['pin' => '5678']);

        $this->garageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['siretNumber' => '12345678901234'])
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$mechanic1, $mechanic2]);

        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        // Cannot test getUser() in unit test as it requires user loader
        // The important part is that the correct passport type is returned
    }

    /**
     * Test authenticate stops checking after finding matching mechanic
     */
    public function testAuthenticateStopsAfterFindingMatch(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(1);

        $mechanic1 = $this->createMock(Mechanic::class);
        $mechanic1->method('getId')->willReturn(3);
        $mechanic1->expects($this->once())->method('verifyPin')->with('9999')->willReturn(true);

        $mechanic2 = $this->createMock(Mechanic::class);
        $mechanic2->expects($this->never())->method('verifyPin'); // Should not be called

        $request = $this->createAuthRequest('12345678901234', ['pin' => '9999']);

        $this->garageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['siretNumber' => '12345678901234'])
            ->willReturn($garage);

        $this->mechanicRepository
            ->expects($this->once())
            ->method('findByGarage')
            ->with(1)
            ->willReturn([$mechanic1, $mechanic2]);

        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        // Cannot test getUser() in unit test as it requires user loader
        // The important part is that mechanic2's verifyPin is never called
    }

    /**
     * Test onAuthenticationSuccess returns JWT token
     */
    public function testOnAuthenticationSuccessReturnsJwtToken(): void
    {
        $mechanic = $this->createStub(Mechanic::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($mechanic);

        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($mechanic)
            ->willReturn('generated.jwt.token');

        $request = new Request();

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertEquals('generated.jwt.token', $data['token']);
    }

    /**
     * Test onAuthenticationFailure returns error message
     */
    public function testOnAuthenticationFailureReturnsErrorMessage(): void
    {
        $exception = new AuthenticationException('Authentication failed');

        $request = new Request();

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        // AuthenticationException uses message keys that get translated
        $this->assertNotEmpty($data['message']);
    }

    /**
     * Test onAuthenticationFailure handles custom message
     */
    public function testOnAuthenticationFailureHandlesCustomMessage(): void
    {
        $exception = new CustomUserMessageAuthenticationException('Invalid PIN provided');

        $request = new Request();

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid PIN provided', $data['message']);
    }

    /**
     * Helper method to create an authentication request
     */
    private function createAuthRequest(string $siret, array $data): Request
    {
        return Request::create(
            '/api/garage/' . $siret . '/mechanic/login',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }
}
