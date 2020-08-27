<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200826194442 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE base_routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_route VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_route_id INTEGER DEFAULT NULL, route VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, times_visited INTEGER NOT NULL, created_on DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_32D5C2B37E07171E ON routes (base_route_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE base_routes');
        $this->addSql('DROP TABLE routes');
    }
}
