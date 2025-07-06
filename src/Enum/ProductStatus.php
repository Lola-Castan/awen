<?php
/**
 * This enum is used to manage product visibility and availability throughout the application lifecycle:
 * - Draft: Product is being created/edited but not visible to customers
 * - Published: Product is visible and available for purchase
 * - Archived: Product is no longer available but kept for historical records
 */
namespace App\Enum;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
    
    /**
     * Returns a human-readable representation of the status
     * 
     * @return string The localized label for the status
     */
    public function getLabel(): string
    {
        return match($this) {
            self::Draft => 'Brouillon',
            self::Published => 'Publié',
            self::Archived => 'Archivé',
        };
    }
    
    /**
     * Only published products are visible to customers
     * 
     * @return bool True if the product status is Published, false otherwise
     */
    public function isVisible(): bool
    {
        return $this === self::Published;
    }
    
    /**
     * Draft products are being created/edited but not visible to customers
     * 
     * @return bool True if the product status is Draft, false otherwise
     */
    public function isDraft(): bool
    {
        return $this === self::Draft;
    }
    
    /**
     * Archived products are no longer available but kept for historical records
     * 
     * @return bool True if the product status is Archived, false otherwise
     */
    public function isArchived(): bool
    {
        return $this === self::Archived;
    }
}