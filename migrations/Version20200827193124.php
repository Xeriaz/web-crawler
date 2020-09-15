<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200827193124 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_32D5C2B37E07171E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes AS SELECT id, base_route_id, route, state, times_visited, created_on FROM routes');
        $this->addSql('DROP TABLE routes');
        $this->addSql('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_route_id INTEGER DEFAULT NULL, route VARCHAR(255) NOT NULL COLLATE BINARY, state VARCHAR(255) NOT NULL COLLATE BINARY, times_visited INTEGER NOT NULL, created_on DATETIME NOT NULL, CONSTRAINT FK_32D5C2B37E07171E FOREIGN KEY (base_route_id) REFERENCES base_routes (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO routes (id, base_route_id, route, state, times_visited, created_on) SELECT id, base_route_id, route, state, times_visited, created_on FROM __temp__routes');
        $this->addSql('DROP TABLE __temp__routes');
        $this->addSql('CREATE INDEX IDX_32D5C2B37E07171E ON routes (base_route_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please m* @Assert\Urlodify it to your needs
        $this->addSql('DROP INDEX IDX_32D5C2B37E07171E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes AS SELECT id, base_route_id, route, state, times_visited, created_on FROM routes');
        $this->addSql('DROP TABLE routes');
        $this->addSql('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_route_id INTEGER DEFAULT NULL, route VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, times_visited INTEGER NOT NULL, created_on DATETIME NOT NULL)');
        $this->addSql('INSERT INTO routes (id, base_route_id, route, state, times_visited, created_on) SELECT id, base_route_id, route, state, times_visited, created_on FROM __temp__routes');
        $this->addSql('DROP TABLE __temp__routes');
        $this->addSql('CREATE INDEX IDX_32D5C2B37E07171E ON routes (base_route_id)');
    }
}
