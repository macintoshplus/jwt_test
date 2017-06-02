# Generate Key

```
openssl genrsa -des3 -out privkey.pem 2048

openssl rsa -in privkey.pem -outform PEM -pubout -out public.pem
```



