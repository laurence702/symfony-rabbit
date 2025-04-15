<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ?EntityManagerInterface $entityManager;
    private MockObject&MessageBusInterface $messageBusMock;

    protected function setUp(): void
    {
        // Force the DATABASE_URL for the test environment *before* booting the kernel
        $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = 'mysql://symfony:symfony@mysql:3306/symfony_demo_test?serverVersion=8.0';
        putenv('DATABASE_URL='.$_SERVER['DATABASE_URL']);

        $this->client = static::createClient(); // Kernel boots here

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->truncateEntities([Order::class]);

        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        static::getContainer()->set('messenger.bus.default', $this->messageBusMock);
    }

    public function testCreateOrderSuccess(): void
    {
        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        $this->client->request(
            'POST',
            '/api/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'test@example.com', 'amount' => 123.45])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        // Decode the actual response
        $responseData = json_decode($responseContent, true);

        // Assert individual fields or structure
        $this->assertArrayHasKey('id', $responseData); // Check if ID exists
        $this->assertNotNull($responseData['id']);
        $this->assertEquals('test@example.com', $responseData['email']);
        $this->assertEquals(123.45, $responseData['amount']);
        $this->assertEquals('pending', $responseData['status']);
        $this->assertArrayHasKey('created_at', $responseData); // Check if created_at exists
        $this->assertNotNull($responseData['created_at']);

        // Verify the order was persisted
        $orderRepository = $this->entityManager->getRepository(Order::class);
        $order = $orderRepository->findOneBy(['customerEmail' => 'test@example.com']);
        $this->assertNotNull($order);
        $this->assertEquals(123.45, $order->getAmount());
        $this->assertEquals($responseData['id'], $order->getId()); // Verify the ID matches
    }

    public function testCreateOrderMissingEmail(): void
    {
        $this->messageBusMock->expects($this->never())->method('dispatch');

        $this->client->request(
            'POST',
            '/api/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['amount' => 123.45])
        );

        $this->assertResponseStatusCodeSame(400);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $this->assertJsonStringEqualsJsonString(
             json_encode(['error' => 'Missing required fields: email and amount']),
             $responseContent
        );
    }

     public function testCreateOrderInvalidJson(): void
    {
        $this->messageBusMock->expects($this->never())->method('dispatch');

        $this->client->request(
            'POST',
            '/api/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"email": "test@example.com", "amount": 123.45' // Invalid JSON
        );

        $this->assertResponseStatusCodeSame(400);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
         $this->assertJsonStringEqualsJsonString(
             json_encode(['error' => 'Invalid JSON format']),
             $responseContent
        );
    }


    public function testGetOrderSuccess(): void
    {
        $order = new Order();
        $order->setCustomerEmail('get@example.com');
        $order->setAmount(99.99);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $orderId = $order->getId();

        $this->client->request('GET', '/api/orders/' . $orderId);

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);
        $this->assertEquals($orderId, $responseData['id']);
        $this->assertEquals('get@example.com', $responseData['email']);
        $this->assertEquals(99.99, $responseData['amount']);
        $this->assertEquals('pending', $responseData['status']);
    }

    public function testGetOrderNotFound(): void
    {
        $this->client->request('GET', '/api/orders/99999');

        $this->assertResponseStatusCodeSame(404);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
         $this->assertJsonStringEqualsJsonString(
             json_encode(['error' => 'Order not found']),
             $responseContent
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Close the entity manager to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function truncateEntities(array $entities): void
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $this->entityManager->getClassMetadata($entity)->getTableName(), true /* cascade */
            );
            $connection->executeStatement($query);
        }

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        }
    }
} 