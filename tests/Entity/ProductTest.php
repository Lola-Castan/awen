<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Image;
use App\Enum\ProductStatus;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Product entity.
 * 
 * This test class covers all basic functionality of the Product entity including:
 * - Basic getters and setters
 * - Business logic methods
 * - Collection management
 * - Data validation scenarios
 * 
 * @author Awen Development Team
 * @covers \App\Entity\Product
 */
class ProductTest extends TestCase
{
  private Product $product;

  /**
   * Set up a fresh Product instance before each test.
   * 
   * @return void
   */
  protected function setUp(): void
  {
    $this->product = new Product();
  }

  /**
   * Test that a new Product instance is properly initialized.
   * 
   * @return void
   */
  public function testProductInitialization(): void
  {
    $this->assertNull($this->product->getId());
    $this->assertNull($this->product->getName());
    $this->assertEquals(ProductStatus::Draft, $this->product->getStatus());
    $this->assertFalse($this->product->isShowcaseProduct());
    $this->assertInstanceOf(ArrayCollection::class, $this->product->getCategories());
    $this->assertInstanceOf(ArrayCollection::class, $this->product->getImages());
    $this->assertCount(0, $this->product->getCategories());
    $this->assertCount(0, $this->product->getImages());
  }

  /**
   * Test basic string properties setters and getters.
   * 
   * @return void
   */
  public function testBasicStringProperties(): void
  {
    $name = 'Test Product';
    $shortDescription = 'A test product for unit testing';
    $longDescription = 'This is a comprehensive description of our test product used for unit testing purposes.';

    $this->product->setName($name);
    $this->product->setShortDescription($shortDescription);
    $this->product->setLongDescription($longDescription);

    $this->assertEquals($name, $this->product->getName());
    $this->assertEquals($shortDescription, $this->product->getShortDescription());
    $this->assertEquals($longDescription, $this->product->getLongDescription());
  }

  /**
   * Test numeric properties setters and getters.
   * 
   * @return void
   */
  public function testNumericProperties(): void
  {
    $stock = 100;
    $weight = 250;
    $width = 15;
    $depth = 10;
    $height = 5;
    $price = 2500;

    $this->product->setStock($stock);
    $this->product->setWeight($weight);
    $this->product->setWidth($width);
    $this->product->setDepth($depth);
    $this->product->setHeight($height);
    $this->product->setPrice($price);

    $this->assertEquals($stock, $this->product->getStock());
    $this->assertEquals($weight, $this->product->getWeight());
    $this->assertEquals($width, $this->product->getWidth());
    $this->assertEquals($depth, $this->product->getDepth());
    $this->assertEquals($height, $this->product->getHeight());
    $this->assertEquals($price, $this->product->getPrice());
  }

  /**
   * Test boolean properties setters and getters.
   * 
   * @return void
   */
  public function testBooleanProperties(): void
  {
    // Test showcase product
    $this->assertFalse($this->product->isShowcaseProduct());

    $this->product->setShowcaseProduct(true);
    $this->assertTrue($this->product->isShowcaseProduct());

    $this->product->setShowcaseProduct(false);
    $this->assertFalse($this->product->isShowcaseProduct());
  }

  /**
   * Test ProductStatus enum handling.
   * 
   * @return void
   */
  public function testProductStatus(): void
  {
    // Default status should be Draft
    $this->assertEquals(ProductStatus::Draft, $this->product->getStatus());

    // Test setting different statuses
    $this->product->setStatus(ProductStatus::Published);
    $this->assertEquals(ProductStatus::Published, $this->product->getStatus());

    $this->product->setStatus(ProductStatus::Archived);
    $this->assertEquals(ProductStatus::Archived, $this->product->getStatus());

    $this->product->setStatus(ProductStatus::Draft);
    $this->assertEquals(ProductStatus::Draft, $this->product->getStatus());
  }

  /**
   * Test DateTime properties handling.
   * 
   * @return void
   */
  public function testDateTimeProperties(): void
  {
    $createdAt = new \DateTimeImmutable('2024-01-15 10:30:00');
    $updatedAt = new \DateTimeImmutable('2024-01-20 15:45:00');

    $this->product->setCreatedAt($createdAt);
    $this->product->setUpdatedAt($updatedAt);

    $this->assertEquals($createdAt, $this->product->getCreatedAt());
    $this->assertEquals($updatedAt, $this->product->getUpdatedAt());
  }
  /**
   * Test User (creator) relationship.
   * 
   * @return void
   */
  public function testCreatorRelationship(): void
  {
    $creator = new User();
    $creator->setUsername('test_creator');

    $this->assertNull($this->product->getCreator());

    $this->product->setCreator($creator);
    $this->assertEquals($creator, $this->product->getCreator());
    $this->assertEquals('test_creator', $this->product->getCreator()->getUsername());
  }
  /**
   * Test category collection management.
   * 
   * @return void
   */
  public function testCategoryManagement(): void
  {
    $category1 = new Category();
    $category1->setName('Test Category 1');

    $category2 = new Category();
    $category2->setName('Test Category 2');

    // Initially empty
    $this->assertCount(0, $this->product->getCategories());

    // Add categories
    $this->product->addCategory($category1);
    $this->assertCount(1, $this->product->getCategories());
    $this->assertTrue($this->product->getCategories()->contains($category1));

    $this->product->addCategory($category2);
    $this->assertCount(2, $this->product->getCategories());
    $this->assertTrue($this->product->getCategories()->contains($category2));

    // Adding same category twice should not increase count
    $this->product->addCategory($category1);
    $this->assertCount(2, $this->product->getCategories());

    // Remove category
    $this->product->removeCategory($category1);
    $this->assertCount(1, $this->product->getCategories());
    $this->assertFalse($this->product->getCategories()->contains($category1));
    $this->assertTrue($this->product->getCategories()->contains($category2));
  }
  /**
   * Test image collection management.
   * 
   * @return void
   */
  public function testImageManagement(): void
  {
    $image1 = new Image();
    $image1->setFilename('test1.jpg');

    $image2 = new Image();
    $image2->setFilename('test2.jpg');

    // Initially empty
    $this->assertCount(0, $this->product->getImages());

    // Add images
    $this->product->addImage($image1);
    $this->assertCount(1, $this->product->getImages());
    $this->assertTrue($this->product->getImages()->contains($image1));

    $this->product->addImage($image2);
    $this->assertCount(2, $this->product->getImages());
    $this->assertTrue($this->product->getImages()->contains($image2));
  }
  /**
   * Test image removal with bidirectional relationship.
   * 
   * @return void
   */
  public function testImageRemoval(): void
  {
    $image = new Image();
    $image->setFilename('test.jpg');

    // First add the image
    $this->product->addImage($image);
    $this->assertCount(1, $this->product->getImages());

    // Then remove it
    $this->product->removeImage($image);
    $this->assertCount(0, $this->product->getImages());
    $this->assertFalse($this->product->getImages()->contains($image));
  }

  /**
   * Test getMainImage method with empty collection.
   * 
   * @return void
   */
  public function testGetMainImageWhenEmpty(): void
  {
    $this->assertNull($this->product->getMainImage());
  }
  /**
   * Test getMainImage method with images having positions.
   * 
   * @return void
   */
  public function testGetMainImageWithPositions(): void
  {
    $image1 = new Image();
    $image1->setFilename('image1.jpg')->setPosition(1);

    $image2 = new Image();
    $image2->setFilename('image2.jpg')->setPosition(0); // Main image

    $image3 = new Image();
    $image3->setFilename('image3.jpg')->setPosition(2);

    $this->product->addImage($image1);
    $this->product->addImage($image2);
    $this->product->addImage($image3);

    // Should return image with position 0
    $this->assertEquals($image2, $this->product->getMainImage());
  }
  /**
   * Test getMainImage method when no image has position 0.
   * 
   * @return void
   */
  public function testGetMainImageFallback(): void
  {
    $image1 = new Image();
    $image1->setFilename('image1.jpg')->setPosition(1);

    $image2 = new Image();
    $image2->setFilename('image2.jpg')->setPosition(2);

    $this->product->addImage($image1);
    $this->product->addImage($image2);

    // Should return first image when none has position 0
    $mainImage = $this->product->getMainImage();
    $this->assertNotNull($mainImage);
    $this->assertTrue($mainImage === $image1 || $mainImage === $image2);
  }
  /**
   * Test fluent interface (method chaining) functionality.
   * 
   * @return void
   */
  public function testFluentInterface(): void
  {
    $creator = new User();
    $creator->setUsername('chain_creator');

    $category = new Category();
    $category->setName('Chain Category');

    $createdAt = new \DateTimeImmutable();

    $result = $this->product
      ->setName('Chained Product')
      ->setPrice(1000)
      ->setStatus(ProductStatus::Published)
      ->setShowcaseProduct(true)
      ->setCreator($creator)
      ->addCategory($category)
      ->setCreatedAt($createdAt);

    $this->assertInstanceOf(Product::class, $result);
    $this->assertEquals('Chained Product', $this->product->getName());
    $this->assertEquals(1000, $this->product->getPrice());
    $this->assertEquals(ProductStatus::Published, $this->product->getStatus());
    $this->assertTrue($this->product->isShowcaseProduct());
    $this->assertEquals($creator, $this->product->getCreator());
    $this->assertTrue($this->product->getCategories()->contains($category));
    $this->assertEquals($createdAt, $this->product->getCreatedAt());
  }
  /**
   * Test handling of null values for optional properties.
   * 
   * @return void
   */
  public function testNullableProperties(): void
  {
    // Test setting null values
    $this->product->setShortDescription(null);
    $this->product->setLongDescription(null);
    $this->product->setStock(null);
    $this->product->setWeight(null);
    $this->product->setWidth(null);
    $this->product->setDepth(null);
    $this->product->setHeight(null);
    $this->product->setPrice(null);
    $this->product->setCreator(null);

    // Verify null values are properly handled
    $this->assertNull($this->product->getShortDescription());
    $this->assertNull($this->product->getLongDescription());
    $this->assertNull($this->product->getStock());
    $this->assertNull($this->product->getWeight());
    $this->assertNull($this->product->getWidth());
    $this->assertNull($this->product->getDepth());
    $this->assertNull($this->product->getHeight());
    $this->assertNull($this->product->getPrice());
    $this->assertNull($this->product->getCreator());

    // Test that updatedAt can be set to a specific value
    $updateTime = new \DateTimeImmutable('2024-06-10 12:00:00');
    $this->product->setUpdatedAt($updateTime);
    $this->assertEquals($updateTime, $this->product->getUpdatedAt());
  }

  /**
   * Clean up after each test.
   * 
   * @return void
   */
  protected function tearDown(): void
  {
    unset($this->product);
  }
}
