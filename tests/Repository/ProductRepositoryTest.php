<?php

namespace App\Tests\Repository;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;
use App\Enum\ProductStatus;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Integration tests for ProductRepository.
 * 
 * These tests verify that database queries work correctly
 * with a real test database.
 */
class ProductRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ProductRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();        $this->repository = $this->entityManager->getRepository(Product::class);

        // Clean database before each test
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {        parent::tearDown();

        // Close entity manager to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }    /**
     * Test findSortedPublishedProducts method without category filter
     */
    public function testFindSortedPublishedProductsWithoutCategory(): void
    {
        // Create test data
        $creator = $this->createTestCreator();
        
        // Create products with explicit creation dates
        $product1 = $this->createTestProduct(
            'Product A', 
            1000, 
            ProductStatus::Published, 
            $creator, 
            new \DateTimeImmutable('2024-01-01 10:00:00')
        );
        
        $product2 = $this->createTestProduct(
            'Product B', 
            2000, 
            ProductStatus::Published, 
            $creator, 
            new \DateTimeImmutable('2024-01-02 10:00:00')
        ); // More recent
        
        $product3 = $this->createTestProduct(
            'Product C', 
            1500, 
            ProductStatus::Draft, 
            $creator, 
            new \DateTimeImmutable('2024-01-03 10:00:00')
        ); // Draft - should not appear
        
        $this->entityManager->flush();

        // Test default sorting (newest)
        $results = $this->repository->findSortedPublishedProducts();
        
        $this->assertCount(2, $results);
        $this->assertEquals('Product B', $results[0]->getName()); // Most recent first
        $this->assertEquals('Product A', $results[1]->getName());
    }    /**
     * Test sorting by ascending price
     */
    public function testFindSortedPublishedProductsByPriceAsc(): void
    {
        $creator = $this->createTestCreator();
        
        $product1 = $this->createTestProduct('Expensive Product', 3000, ProductStatus::Published, $creator);
        $product2 = $this->createTestProduct('Cheap Product', 1000, ProductStatus::Published, $creator);
        $product3 = $this->createTestProduct('Medium Product', 2000, ProductStatus::Published, $creator);
        
        $this->entityManager->flush();

        $results = $this->repository->findSortedPublishedProducts(null, 'price_asc');
        
        $this->assertCount(3, $results);
        $this->assertEquals('Cheap Product', $results[0]->getName());
        $this->assertEquals('Medium Product', $results[1]->getName());
        $this->assertEquals('Expensive Product', $results[2]->getName());
    }    /**
     * Test sorting by descending price
     */
    public function testFindSortedPublishedProductsByPriceDesc(): void
    {
        $creator = $this->createTestCreator();
        
        $product1 = $this->createTestProduct('Expensive Product', 3000, ProductStatus::Published, $creator);
        $product2 = $this->createTestProduct('Cheap Product', 1000, ProductStatus::Published, $creator);
        
        $this->entityManager->flush();

        $results = $this->repository->findSortedPublishedProducts(null, 'price_desc');
        
        $this->assertCount(2, $results);
        $this->assertEquals('Expensive Product', $results[0]->getName());
        $this->assertEquals('Cheap Product', $results[1]->getName());
    }    /**
     * Test filtering by category
     */
    public function testFindSortedPublishedProductsByCategory(): void
    {
        $creator = $this->createTestCreator();
        $category1 = $this->createTestCategory('Category 1');
        $category2 = $this->createTestCategory('Category 2');
        
        $product1 = $this->createTestProduct('Product Cat1', 1000, ProductStatus::Published, $creator);
        $product1->addCategory($category1);
        
        $product2 = $this->createTestProduct('Product Cat2', 2000, ProductStatus::Published, $creator);
        $product2->addCategory($category2);
        
        $product3 = $this->createTestProduct('Product Without Cat', 1500, ProductStatus::Published, $creator);
        
        $this->entityManager->flush();

        // Test with category 1
        $results = $this->repository->findSortedPublishedProducts($category1);
        $this->assertCount(1, $results);
        $this->assertEquals('Product Cat1', $results[0]->getName());

        // Test with category 2
        $results = $this->repository->findSortedPublishedProducts($category2);
        $this->assertCount(1, $results);
        $this->assertEquals('Product Cat2', $results[0]->getName());
    }    /**
     * Test findFeaturedProducts method
     */
    public function testFindFeaturedProducts(): void
    {
        $creator = $this->createTestCreator();
        
        $product1 = $this->createTestProduct('Normal Product', 1000, ProductStatus::Published, $creator);
        $product2 = $this->createTestProduct('Featured Product', 2000, ProductStatus::Published, $creator);
        $product2->setShowcaseProduct(true);
        $product3 = $this->createTestProduct('Another Featured', 3000, ProductStatus::Published, $creator);
        $product3->setShowcaseProduct(true);
        
        $this->entityManager->flush();

        $results = $this->repository->findFeaturedProducts();
        
        $this->assertCount(2, $results);
        // Check that only featured products are returned
        foreach ($results as $product) {
            $this->assertTrue($product->isShowcaseProduct());
        }
    }    /**
     * Test with limit on featured products
     */
    public function testFindFeaturedProductsWithLimit(): void
    {
        $creator = $this->createTestCreator();
        
        // Create 3 featured products
        for ($i = 1; $i <= 3; $i++) {
            $product = $this->createTestProduct("Featured $i", 1000 * $i, ProductStatus::Published, $creator);
            $product->setShowcaseProduct(true);
        }
        
        $this->entityManager->flush();

        // Request only 2 results
        $results = $this->repository->findFeaturedProducts(2);
        
        $this->assertCount(2, $results);
    }    /**
     * Test findByCreatorGroupedByStatus method
     */
    public function testFindByCreatorGroupedByStatus(): void
    {
        $creator1 = $this->createTestCreator('creator1');
        $creator2 = $this->createTestCreator('creator2');
        
        // Creator 1's products
        $this->createTestProduct('Published Product', 1000, ProductStatus::Published, $creator1);
        $this->createTestProduct('Draft Product', 2000, ProductStatus::Draft, $creator1);
        $this->createTestProduct('Archived Product', 3000, ProductStatus::Archived, $creator1);
        
        // Creator 2's product (should not appear in results)
        $this->createTestProduct('Other Creator', 1500, ProductStatus::Published, $creator2);
        
        $this->entityManager->flush();

        $results = $this->repository->findByCreatorGroupedByStatus($creator1);
        
        // Check result structure
        $this->assertArrayHasKey('published', $results);
        $this->assertArrayHasKey('draft', $results);
        $this->assertArrayHasKey('archived', $results);
        
        // Check counts
        $this->assertCount(1, $results['published']);
        $this->assertCount(1, $results['draft']);
        $this->assertCount(1, $results['archived']);
        
        // Check product names
        $this->assertEquals('Published Product', $results['published'][0]->getName());
        $this->assertEquals('Draft Product', $results['draft'][0]->getName());
        $this->assertEquals('Archived Product', $results['archived'][0]->getName());
    }    /**
     * Utility method to create a test user
     */
    private function createTestCreator(string $username = 'testcreator'): User
    {
        $creator = new User();
        $creator->setUsername($username)
            ->setEmail("$username@test.com")
            ->setPassword('password123')
            ->setFirstName('Test')
            ->setLastName('Creator')
            ->setBirthDate(new \DateTimeImmutable('1990-01-01'))
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($creator);
        
        return $creator;
    }    /**
     * Utility method to create a test category
     */
    private function createTestCategory(string $name): Category
    {
        $category = new Category();
        $category->setName($name);

        $this->entityManager->persist($category);
        
        return $category;
    }    /**
     * Utility method to create a test product
     */
    private function createTestProduct(string $name, int $price, ProductStatus $status, User $creator, ?\DateTimeImmutable $createdAt = null): Product
    {
        $product = new Product();
        $product->setName($name)
            ->setShortDescription("Description of $name")
            ->setLongDescription("Long description of $name")
            ->setPrice($price)
            ->setStock(10)
            ->setStatus($status)
            ->setCreator($creator)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->entityManager->persist($product);
        
        // Small delay to ensure timestamps are different
        if ($createdAt === null) {
            usleep(1000);
        }
        
        return $product;
    }    /**
     * Clean database before each test
     */
    private function cleanDatabase(): void
    {
        // Delete in order to respect FK constraints
        $this->entityManager->createQuery('DELETE FROM App\Entity\Product')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Category')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }
}
