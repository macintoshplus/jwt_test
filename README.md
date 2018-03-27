# Generate Key

```
openssl genrsa -des3 -out privkey.pem 2048

openssl rsa -in privkey.pem -outform PEM -pubout -out public.pem
```

# Installation

Use [composer](https://getcomposer.org/) for install the dependencies.

```
php composer install -o
```


# Single file

The file `makeJwt.php` write an file named `token_<uniqid>` with the token JWT into.

The file `decodeJwt.php` can read and check the token written by the `makeJwt.php` file. Before use, change the name of the JWT token file at ligne `16`.

# Client / Server version

The server Application is based on Silex. The JWT token validation work with MiddleWare executed before the routing component.

This solution is not completed. You cannot use the embeded User Control Access.

Read the branch `with_guard` for most avanced use.
