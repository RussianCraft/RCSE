<?php

User {
    public __construct(Logger $logger, Configurator $config)
    public userRegister(string $login, string $password, string $email, string $sex, string $birthdate, string $origin)
    public userLogin(string $id, string $pass, bool $save_session)
    public userGetInfo(string $login)
    public userVerify(string $login, string $code)
    public userPunish(string $login, array $params)
    public userEdit(string $login, array $data)
    public userSuspend(string $login)
    public userRemove(string $login)
    public userResetPass(string $login[, string $code = null])
    private userSessionCreate(string $login, bool $save_session)
    private userSessionObtainAll(string $login)
    private userSessionUpdate(string $login, mixed $data)
    private userSessionObtain(string $login, string $session_id)
    private userSessionValidate(string $login, string $session_id)
    public userIsSessionSet()
    private userSessionEndAll(string $login)
    public userSessionEnd() : bool

}