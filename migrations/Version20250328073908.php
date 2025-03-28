<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328073908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление ролей ROLE_USER и ROLE_ADMIN в таблицу roles и связывание их с пользователями.';
    }

    public function up(Schema $schema): void
    {
        // Добавление ролей ROLE_USER и ROLE_ADMIN, если они еще не существуют
        $this->addSql("INSERT INTO roles (name) 
                        SELECT 'ROLE_USER' 
                        WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'ROLE_USER')");

        $this->addSql("INSERT INTO roles (name) 
                        SELECT 'ROLE_ADMIN' 
                        WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'ROLE_ADMIN')");

        // Получаем ID роли ROLE_USER
        $roleUserId = $this->connection->fetchOne('SELECT id FROM roles WHERE name = ?', ['ROLE_USER']);

        //TODO: дополнительно перепроверить заполнение user_roles
        // Проверяем, что роль ROLE_USER существует
        if ($roleUserId !== false) {
            // Получаем всех пользователей
            $users = $this->connection->fetchAllAssociative('SELECT id FROM users');

            // Логируем количество пользователей
            echo "Number of users found: " . count($users) . "\n";

            // Проходим по всем пользователям и добавляем их в таблицу user_roles с ролью ROLE_USER
            foreach ($users as $user) {
                echo "Inserting user with id: " . $user['id'] . "\n"; // Логируем, какой пользователь вставляется

                try {
                    $this->addSql('
                    INSERT INTO user_roles (user_id, role_id)
                    VALUES (?, ?)
                ', [$user['id'], $roleUserId]);

                    echo "Inserted user_id: " . $user['id'] . " with role_id: " . $roleUserId . "\n"; // Логируем успешную вставку
                } catch (\Exception $e) {
                    echo "Error inserting user_id: " . $user['id'] . " - " . $e->getMessage() . "\n"; // Логируем ошибку
                }
            }
        } else {
            echo "ROLE_USER not found.\n";
        }
    }

    public function down(Schema $schema): void
    {
        // Удаляем связи пользователей с ролями ROLE_USER и ROLE_ADMIN
        $this->addSql('DELETE FROM user_roles WHERE role_id IN (SELECT id FROM roles WHERE name IN (?, ?))', ['ROLE_USER', 'ROLE_ADMIN']);

        // Удаляем роли ROLE_USER и ROLE_ADMIN
        $this->addSql('DELETE FROM roles WHERE name IN (?, ?)', ['ROLE_USER', 'ROLE_ADMIN']);
    }
}
