# Generate Key

```
openssl genrsa -des3 -out privkey.pem 2048

openssl rsa -in privkey.pem -outform PEM -pubout -out public.pem
```

# Single file

The file `makeJwt.php` write an file named `token_<uniqid>` with the token JWT into.

The file `decodeJwt.php` can read and check the token written by the `makeJwt.php` file. Before use, change the name of the JWT token file at ligne `16`.

# Client / Server version

With this branch, the server use Silex with standard component of symfony.

Use:

* Firewall
* Guard for check header `Authorization`
* Custom user provider implements `Symfony\Component\Security\Core\User\UserProviderInterface`

This component can use the standard flow for authenticate and allow access to ressouces on server application.

The JWT token is set into the request. You can get it for add custom access check between Token content and request content.
For get the token `$request->attributes->get('jwt');`. The token is an `Lcobucci\JWT\Token` instance.

## Launch server

Open terminal (Unix) or command line (Windows) and go to `server` folder. Run this command for launch the PHP integrated Web Server `php -S 127.0.0.1:8000`

## Run Client

Open another terminal (Unix) or command line (Windows) and go to `client` folder. Run this command for launch client query `php client.php`.

