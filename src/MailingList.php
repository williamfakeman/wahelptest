<?php

namespace App;

use PDO;

class MailingList
{
    /**
     * Singleton database pattern
     * @var PDO
     */
    public static $dbInstance = null;

    /**
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$dbInstance === null) {
            self::$dbInstance = new PDO(
                'mysql:host=localhost;dbname=wahelp;charset=utf8mb4;',
                'wahelp',
                'wahelp',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        return self::$dbInstance;
    }

    /**
     * Uploads a CSV file to the database
     * @param string $file - path to the CSV file
     * @return int - number of rows inserted
     */
    public static function upload(string $file): int
    {
        $db = self::getInstance();

        $file = fopen($file, 'r');
        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            $number = $row[0];
            $name = $row[1];

            $sql = 'INSERT INTO `users` (`number`, `name`) VALUES (:number, :name)';
            $stmt = $db->prepare($sql);
            if($stmt->execute([':number' => $number, ':name' => $name])) {
                $count++;
            }
        }
        fclose($file);

        return $count;
    }

    /**
     * Selects all users uses many-to-many relationship with middle mailing_list_users table 
     * and send messages to them, then updates mailing_list_users table
     * @param int $listId - mailing list id
     * @param string $title - message title
     * @param string $text - message text
     * @param int $break - max number of messages to send (for testing purposes)
     * @return int - number of messages sent
     */
    public static function sendAll(int $listId, string $title, string $text, int $break = 10000): int
    {
        $db = self::getInstance();

        $sql = 'SELECT `id`, `number`, `name` FROM `users` WHERE `id` NOT IN (SELECT `user_id` FROM `mailing_list_users` WHERE `list_id` = :listId)';
        $stmtUsers = $db->prepare($sql);
        $stmtUsers->execute([':listId' => $listId]);

        $count = 0;
        while($user = $stmtUsers->fetch()) {
            if(self::send($user['number'], $user['name'], $title, $text)) {
                $sql = 'INSERT INTO `mailing_list_users` (`list_id`, `user_id`) VALUES (:listId, :userId)';
                $stmtMailingListUsers = $db->prepare($sql);
                $stmtMailingListUsers->execute([':listId' => $listId, ':userId' => $user['id']]);

                $count++;

                // for testing purpouses
                if($count >= $break) {
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * Send message to user from mailing list - dummy
     * @param string $number - number of the user
     * @param string $name - name of the user
     * @param string $title - message title
     * @param string $text - message text
     * @return bool - true if the message was sent, false otherwise
     */
    public static function send(string $number, string $name, string $title, string $text): bool
    {
        return true;
    }
}