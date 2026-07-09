<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Garage;
use App\Entity\Owner;
use App\Entity\Receptionist;
use App\EventSubscriber\JwtCreatedSubscriber;
use Doctrine\Common\Collections\ArrayCollection;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class JwtCreatedSubscriberTest extends TestCase
{
    private RequestStack $requestStack;
    private JwtCreatedSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->subscriber = new JwtCreatedSubscriber($this->requestStack);
    }

    /**
     * Test that subscriber subscribes to JWT_CREATED event
     */
    public function testGetSubscribedEvents(): void
    {
        $events = JwtCreatedSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(Events::JWT_CREATED, $events);
        $this->assertEquals('onJwtCreated', $events[Events::JWT_CREATED]);
    }

    /**
     * Test onJwtCreated adds garage_ids for Owner
     */
    public function testOnJwtCreatedAddsGarageIdsForOwner(): void
    {
        $garage1 = $this->createStub(Garage::class);
        $garage1->method('getId')->willReturn(1);

        $garage2 = $this->createStub(Garage::class);
        $garage2->method('getId')->willReturn(2);

        $garage3 = $this->createStub(Garage::class);
        $garage3->method('getId')->willReturn(3);

        $owner = $this->createStub(Owner::class);
        $owner->method('getGarages')->willReturn(new ArrayCollection([$garage1, $garage2, $garage3]));

        $payload = ['username' => 'owner@example.com'];
        $event = new JWTCreatedEvent($payload, $owner);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        $this->assertArrayHasKey('garage_ids', $result);
        $this->assertCount(3, $result['garage_ids']);
        $this->assertEquals([1, 2, 3], $result['garage_ids']);
    }

    /**
     * Test onJwtCreated handles Owner with empty garages
     */
    public function testOnJwtCreatedHandlesOwnerWithNoGarages(): void
    {
        $owner = $this->createStub(Owner::class);
        $owner->method('getGarages')->willReturn(new ArrayCollection([]));

        $payload = ['username' => 'owner@example.com'];
        $event = new JWTCreatedEvent($payload, $owner);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        $this->assertArrayHasKey('garage_ids', $result);
        $this->assertCount(0, $result['garage_ids']);
        $this->assertEquals([], $result['garage_ids']);
    }

    /**
     * Test onJwtCreated adds garage context for Receptionist
     */
    public function testOnJwtCreatedAddsGarageContextForReceptionist(): void
    {
        $receptionist = $this->createStub(Receptionist::class);

        $request = new Request();
        $request->attributes->set('garage_context', [
            'garage_id' => 5,
            'garage_siret' => '12345678901234',
        ]);

        $this->requestStack->push($request);

        $payload = ['username' => 'receptionist@example.com'];
        $event = new JWTCreatedEvent($payload, $receptionist);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        $this->assertArrayHasKey('garage_id', $result);
        $this->assertArrayHasKey('garage_siret', $result);
        $this->assertEquals(5, $result['garage_id']);
        $this->assertEquals('12345678901234', $result['garage_siret']);
    }

    /**
     * Test onJwtCreated handles Receptionist when no request available
     */
    public function testOnJwtCreatedHandlesReceptionistWithNoRequest(): void
    {
        $receptionist = $this->createStub(Receptionist::class);

        // No request in stack

        $payload = ['username' => 'receptionist@example.com'];
        $event = new JWTCreatedEvent($payload, $receptionist);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        // Payload should remain unchanged
        $this->assertArrayNotHasKey('garage_id', $result);
        $this->assertArrayNotHasKey('garage_siret', $result);
        $this->assertEquals($payload, $result);
    }

    /**
     * Test onJwtCreated handles Receptionist when no garage context in request
     */
    public function testOnJwtCreatedHandlesReceptionistWithNoGarageContext(): void
    {
        $receptionist = $this->createStub(Receptionist::class);

        $request = new Request();
        // No garage_context attribute

        $this->requestStack->push($request);

        $payload = ['username' => 'receptionist@example.com'];
        $event = new JWTCreatedEvent($payload, $receptionist);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        // Payload should remain unchanged
        $this->assertArrayNotHasKey('garage_id', $result);
        $this->assertArrayNotHasKey('garage_siret', $result);
        $this->assertEquals($payload, $result);
    }

    /**
     * Test onJwtCreated preserves existing payload data for Owner
     */
    public function testOnJwtCreatedPreservesExistingPayloadForOwner(): void
    {
        $garage = $this->createStub(Garage::class);
        $garage->method('getId')->willReturn(10);

        $owner = $this->createStub(Owner::class);
        $owner->method('getGarages')->willReturn(new ArrayCollection([$garage]));

        $payload = [
            'username' => 'owner@example.com',
            'roles' => ['ROLE_OWNER'],
            'exp' => 1234567890,
        ];
        $event = new JWTCreatedEvent($payload, $owner);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        // Original payload should be preserved
        $this->assertEquals('owner@example.com', $result['username']);
        $this->assertEquals(['ROLE_OWNER'], $result['roles']);
        $this->assertEquals(1234567890, $result['exp']);
        // garage_ids should be added
        $this->assertEquals([10], $result['garage_ids']);
    }

    /**
     * Test onJwtCreated preserves existing payload data for Receptionist
     */
    public function testOnJwtCreatedPreservesExistingPayloadForReceptionist(): void
    {
        $receptionist = $this->createStub(Receptionist::class);

        $request = new Request();
        $request->attributes->set('garage_context', [
            'garage_id' => 7,
            'garage_siret' => '98765432109876',
        ]);

        $this->requestStack->push($request);

        $payload = [
            'username' => 'receptionist@example.com',
            'roles' => ['ROLE_RECEPTIONIST'],
            'exp' => 9876543210,
        ];
        $event = new JWTCreatedEvent($payload, $receptionist);

        $this->subscriber->onJwtCreated($event);

        $result = $event->getData();

        // Original payload should be preserved
        $this->assertEquals('receptionist@example.com', $result['username']);
        $this->assertEquals(['ROLE_RECEPTIONIST'], $result['roles']);
        $this->assertEquals(9876543210, $result['exp']);
        // garage context should be added
        $this->assertEquals(7, $result['garage_id']);
        $this->assertEquals('98765432109876', $result['garage_siret']);
    }
}
