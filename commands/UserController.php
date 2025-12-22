<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\User;

/**
 * Консольная команда для управления пользователями
 */
class UserController extends Controller
{
    /**
     * Создает нового пользователя
     * 
     * Использование:
     * php yii user/create username email password [role]
     * 
     * Примеры:
     * php yii user/create admin admin@example.com password123 author
     * php yii user/create reader reader@example.com password123 reader
     * 
     * @param string $username Имя пользователя
     * @param string $email Email
     * @param string $password Пароль
     * @param string $role Роль (author или reader, по умолчанию reader)
     * @return int Exit code
     */
    public function actionCreate($username, $email, $password, $role = 'reader')
    {
        // Перевірка роли
        if (!in_array($role, [User::ROLE_AUTHOR, User::ROLE_READER])) {
            $this->stdout("Error: Role must be 'author' or 'reader'.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        // Перевірка, чи існує користувач
        if (User::find()->where(['username' => $username])->exists()) {
            $this->stdout("Error: User with username '{$username}' already exists.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        if (User::find()->where(['email' => $email])->exists()) {
            $this->stdout("Error: User with email '{$email}' already exists.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        // Створення користувача
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->role = $role;
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($password);
        $user->generateAuthKey();

        if ($user->save()) {
            $this->stdout("Success! User created:\n", \yii\helpers\Console::FG_GREEN);
            $this->stdout("  ID: {$user->id}\n");
            $this->stdout("  Username: {$user->username}\n");
            $this->stdout("  Email: {$user->email}\n");
            $this->stdout("  Role: {$user->role}\n");
            $this->stdout("  Status: " . ($user->status ? 'Active' : 'Inactive') . "\n");
            return ExitCode::OK;
        } else {
            $this->stdout("Error: Failed to create user.\n", \yii\helpers\Console::FG_RED);
            foreach ($user->errors as $attribute => $errors) {
                foreach ($errors as $error) {
                    $this->stdout("  - {$attribute}: {$error}\n", \yii\helpers\Console::FG_RED);
                }
            }
            return ExitCode::DATAERR;
        }
    }

    /**
     * Список всіх користувачів
     * 
     * Використання:
     * php yii user/list
     * 
     * @return int Exit code
     */
    public function actionList()
    {
        $users = User::find()->all();

        if (empty($users)) {
            $this->stdout("No users found.\n", \yii\helpers\Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout("Users list:\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout(str_repeat('-', 80) . "\n");
        
        foreach ($users as $user) {
            $this->stdout(sprintf(
                "ID: %-5d | Username: %-15s | Email: %-25s | Role: %-8s | Status: %s\n",
                $user->id,
                $user->username,
                $user->email,
                $user->role,
                $user->status ? 'Active' : 'Inactive'
            ));
        }

        return ExitCode::OK;
    }

    /**
     * Видаляє користувача
     * 
     * Використання:
     * php yii user/delete username
     * 
     * @param string $username Ім'я користувача
     * @return int Exit code
     */
    public function actionDelete($username)
    {
        $user = User::find()->where(['username' => $username])->one();

        if (!$user) {
            $this->stdout("Error: User '{$username}' not found.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }

        if ($user->delete()) {
            $this->stdout("Success! User '{$username}' deleted.\n", \yii\helpers\Console::FG_GREEN);
            return ExitCode::OK;
        } else {
            $this->stdout("Error: Failed to delete user.\n", \yii\helpers\Console::FG_RED);
            return ExitCode::DATAERR;
        }
    }
}

