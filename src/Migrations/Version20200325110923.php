<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325110923 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_DFEC3F3938248176');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rate AS SELECT id, currency_id, date, rate FROM rate');
        $this->addSql('DROP TABLE rate');
        $this->addSql('CREATE TABLE rate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, currency_id INTEGER NOT NULL, rate NUMERIC(10, 4) NOT NULL, date DATETIME NOT NULL, CONSTRAINT FK_DFEC3F3938248176 FOREIGN KEY (currency_id) REFERENCES currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rate (id, currency_id, date, rate) SELECT id, currency_id, date, rate FROM __temp__rate');
        $this->addSql('DROP TABLE __temp__rate');
        $this->addSql('CREATE INDEX IDX_DFEC3F3938248176 ON rate (currency_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_DFEC3F3938248176');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rate AS SELECT id, currency_id, date, rate FROM rate');
        $this->addSql('DROP TABLE rate');
        $this->addSql('CREATE TABLE rate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, currency_id INTEGER NOT NULL, rate NUMERIC(10, 4) NOT NULL, date DATE NOT NULL)');
        $this->addSql('INSERT INTO rate (id, currency_id, date, rate) SELECT id, currency_id, date, rate FROM __temp__rate');
        $this->addSql('DROP TABLE __temp__rate');
        $this->addSql('CREATE INDEX IDX_DFEC3F3938248176 ON rate (currency_id)');
    }
}
