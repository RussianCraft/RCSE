<?php
declare (strict_types = 1);
namespace RCSE\Core;

class User
{
    /** @var Configurator */
    private $config;

    /** @var Database */
    private $database;

    /** @var Logger */
    private $logger;

    /** @var Utils */
    private $utils;

    /** @var File */
    private $file;

    public function __construct(Logger $logger, Configurator $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->utils = new Utils();
        $this->file = new File();
        $this->database = new Database($this->logger, $this->config);
    }

    public function userRegister(string $login, string $password, string $email, string $sex, string $birthdate, string $origin)
    {
        if ($this->database->databaseCheckData('accounts', 'by_login', $login)) {
            $message = "Failed to register account: login {$login} is already claimed!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            return $message;
        }
        if ($this->database->databaseCheckData('accounts', 'by_email', $email)) {
            $message = "Failed to register account: email {$email} is already claimed!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            return $message;
        }

        $birthdate = new DateTime($birthdate) . format('Y-m-d');
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

        $file_path = "/userdata/{$login}/";
        $file_name = "session.json";

        file_put_contents($file_path.$file_name,"{}");

        try {
            return $this->database->databaseSendData('accounts', 'insert', $params);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;
        }
    }

    public function userLogin(string $id, string $pass)
    {
        if (strpos($id, '@') !== false && strpos($id, '.') !== false) {
            $type = "by_email";
        } else {
            $type = "by_login";
        }

        if ($this->database->databaseCheckData('accounts', $type, $id) === false) {
            $message = "Failed to login: account for {$id} not found!";
            $this->logger->log($this->logger::ERROR, $message, get_class($this));
            return $message;
        }

        try {
            $user_data = $this->database->databaseGetData('accounts', $type, $id);
        } catch (\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;
        }

        if (password_verify($pass, $user_data['password']) === false) {
            $message = "Failed to login: password doesn't match!";
            $this->logger->log($this->logger::NOTICE, $message, get_class($this));
            return $message;
        }
    }

    public function userVerify()
    { }

    public function userPunish()
    { }

    public function userEdit()
    { }

    public function userSuspend()
    { }

    public function userRemove()
    { }

    private function userSessionCreate(string $login)
    {
        $file_path = "/userdata/{$login}/";
        $file_name = "session.json";

        $session_id = Utils::utilsRandomString(16);
        
        try {
            $session_file_data = $this->file->fileRead($file_path, $file_name);
        } catch(\Exception $e) {
            $this->logger->log($this->logger::ERROR, $e->getMessage(), get_class($this));
            return false;
        }
    }

    private function userSessionObtain(string $login, string $session_id)
    { }

    private function userSessionValidate(string $login, string $session_id)
    { }

    private function userGetIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
