<?php
declare(strict_types=1);
namespace RCSE\Core;

class User
{
    /** @var Configurator */
    private $config;

    /** @var Database */
    private $database;

    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger, Configurator $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->database = new Database($this->logger, $this->config);
    }

    public function userRegister(string $login, string $password, string $email, string $sex, string $birthdate, string $origin)
    {
        if($this->database->databaseCheckData('accounts','by_login',$login)) {
            $message = "Failed to register account: login {$login} is already claimed!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            throw new \Exception($message, 1020);
        }
        if($this->database->databaseCheckData('accounts','by_email',$email)) {
            $message = "Failed to register account: email {$email} is already claimed!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            throw new \Exception($message, 1020);
        }

        $birthdate = new DateTime($birthdate).format('Y-m-d');
        $regdate = date('Y-m-d');
        $passhash = password_hash($password, PASSWORD_ARGON2I);
        $settings = [
            "avatar" => "default",
            "group" => "user"
        ];

        $params = [
            ":login" => $login,
            ":password" => $passhash,
            ":email" => $email,
            ":sex" => $sex,
            ":birthdate" => $birthdate,
            ":origin" => $origin,
            ":regdate" => $regdate,
            ":settings" => json_encode($settings)
        ];
        
        try {
            return $this->database->databaseSendData('accounts', 'insert', $params);
        } catch(\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;
        }

    }

    public function userLogin()
    {}

    public function userVerify()
    {}

    public function userPunish()
    {}

    public function userEdit()
    {}

    public function userSuspend()
    {}

    public function userRemove()
    {
    }
}