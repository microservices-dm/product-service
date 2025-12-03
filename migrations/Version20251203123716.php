<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203123716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_attribute (id SERIAL NOT NULL, category_id INT NOT NULL, product_attribute_id INT NOT NULL, is_required BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D1A3DCB12469DE2 ON category_attribute (category_id)');
        $this->addSql('CREATE INDEX IDX_3D1A3DCB3B420C91 ON category_attribute (product_attribute_id)');
        $this->addSql('CREATE TABLE product_attribute (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, unit VARCHAR(50) DEFAULT NULL, is_required BOOLEAN NOT NULL, is_filterable BOOLEAN NOT NULL, sort_order INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN product_attribute.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE product_attribute_value (id SERIAL NOT NULL, product_id INT NOT NULL, attribute_id INT NOT NULL, value_string VARCHAR(255) DEFAULT NULL, value_integer INT DEFAULT NULL, value_decimal NUMERIC(10, 0) DEFAULT NULL, value_boolean BOOLEAN DEFAULT NULL, value_text TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CCC4BE1F4584665A ON product_attribute_value (product_id)');
        $this->addSql('CREATE INDEX IDX_CCC4BE1FB6E62EFA ON product_attribute_value (attribute_id)');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCB3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES product_attribute (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_attribute_value ADD CONSTRAINT FK_CCC4BE1F4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_attribute_value ADD CONSTRAINT FK_CCC4BE1FB6E62EFA FOREIGN KEY (attribute_id) REFERENCES product_attribute (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category_attribute DROP CONSTRAINT FK_3D1A3DCB12469DE2');
        $this->addSql('ALTER TABLE category_attribute DROP CONSTRAINT FK_3D1A3DCB3B420C91');
        $this->addSql('ALTER TABLE product_attribute_value DROP CONSTRAINT FK_CCC4BE1F4584665A');
        $this->addSql('ALTER TABLE product_attribute_value DROP CONSTRAINT FK_CCC4BE1FB6E62EFA');
        $this->addSql('DROP TABLE category_attribute');
        $this->addSql('DROP TABLE product_attribute');
        $this->addSql('DROP TABLE product_attribute_value');
    }
}
