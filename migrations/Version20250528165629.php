<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528165629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, expected_delivery_date DATETIME DEFAULT NULL, delivered_at DATETIME DEFAULT NULL, payment_method VARCHAR(255) NOT NULL, shipping_cost NUMERIC(10, 2) NOT NULL, total_ht NUMERIC(10, 2) NOT NULL, total_ttc NUMERIC(10, 2) NOT NULL, tva_amount NUMERIC(10, 2) NOT NULL, shipping_address VARCHAR(255) NOT NULL, billing_address VARCHAR(255) NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_detail (id INT AUTO_INCREMENT NOT NULL, order_ref_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, unit_price_ht NUMERIC(10, 2) NOT NULL, unit_price_ttc NUMERIC(10, 2) NOT NULL, total_price_ht NUMERIC(10, 2) NOT NULL, total_price_ttc NUMERIC(10, 2) NOT NULL, discount_percentage NUMERIC(5, 2) DEFAULT NULL, INDEX IDX_ED896F46E238517C (order_ref_id), INDEX IDX_ED896F464584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46E238517C FOREIGN KEY (order_ref_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F464584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46E238517C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F464584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `order`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_detail
        SQL);
    }
}
