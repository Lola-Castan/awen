<?php

namespace App\Tests\Enum;

use App\Enum\ProductStatus;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the ProductStatus enum.
 * 
 * This test class ensures that the ProductStatus enum is properly defined
 * and behaves as expected throughout the application.
 * 
 * @author Awen Development Team
 * @covers \App\Enum\ProductStatus
 */
class ProductStatusTest extends TestCase
{
  /**
   * Test that all expected enum cases are defined with correct values.
   * 
   * @return void
   */
  public function testEnumCases(): void
  {
    $this->assertEquals('draft', ProductStatus::Draft->value);
    $this->assertEquals('published', ProductStatus::Published->value);
    $this->assertEquals('archived', ProductStatus::Archived->value);
  }

  /**
   * Test that enum cases can be created from string values.
   * 
   * @return void
   */
  public function testEnumFromValue(): void
  {
    $this->assertEquals(ProductStatus::Draft, ProductStatus::from('draft'));
    $this->assertEquals(ProductStatus::Published, ProductStatus::from('published'));
    $this->assertEquals(ProductStatus::Archived, ProductStatus::from('archived'));
  }

  /**
   * Test that all enum cases are accessible.
   * 
   * @return void
   */
  public function testAllCases(): void
  {
    $cases = ProductStatus::cases();

    $this->assertCount(3, $cases);
    $this->assertContains(ProductStatus::Draft, $cases);
    $this->assertContains(ProductStatus::Published, $cases);
    $this->assertContains(ProductStatus::Archived, $cases);
  }

  /**
   * Test enum comparison and equality.
   * 
   * @return void
   */
  public function testEnumComparison(): void
  {
    $draft1 = ProductStatus::Draft;
    $draft2 = ProductStatus::from('draft');
    $published = ProductStatus::Published;

    $this->assertTrue($draft1 === $draft2);
    $this->assertFalse($draft1 === $published);
    $this->assertTrue($draft1 !== $published);
  }

  /**
   * Test that invalid enum values throw exceptions.
   * 
   * @return void
   */
  public function testInvalidEnumValue(): void
  {
    $this->expectException(\ValueError::class);
    ProductStatus::from('invalid_status');
  }

  /**
   * Test tryFrom method for safe enum creation.
   * 
   * @return void
   */
  public function testTryFromMethod(): void
  {
    // Valid values
    $this->assertEquals(ProductStatus::Draft, ProductStatus::tryFrom('draft'));
    $this->assertEquals(ProductStatus::Published, ProductStatus::tryFrom('published'));
    $this->assertEquals(ProductStatus::Archived, ProductStatus::tryFrom('archived'));

    // Invalid values should return null
    $this->assertNull(ProductStatus::tryFrom('invalid'));
    $this->assertNull(ProductStatus::tryFrom(''));
    $this->assertNull(ProductStatus::tryFrom('DRAFT')); // Case sensitive
  }

  /**
   * Test enum in array operations.
   * 
   * @return void
   */
  public function testEnumInArrayOperations(): void
  {
    $statuses = [ProductStatus::Draft, ProductStatus::Published];

    $this->assertTrue(in_array(ProductStatus::Draft, $statuses, true));
    $this->assertTrue(in_array(ProductStatus::Published, $statuses, true));
    $this->assertFalse(in_array(ProductStatus::Archived, $statuses, true));
  }

  /**
   * Test enum serialization behavior.
   * 
   * @return void
   */
  public function testEnumSerialization(): void
  {
    $status = ProductStatus::Published;

    // Test JSON serialization
    $json = json_encode($status);
    $this->assertEquals('"published"', $json);

    // Test that we can reconstruct from the serialized value
    $decoded = json_decode($json, true);
    $reconstructed = ProductStatus::from($decoded);
    $this->assertEquals($status, $reconstructed);
  }
}
