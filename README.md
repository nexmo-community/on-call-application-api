![Vonage][logo]

# Build an On-Call Application With Symfony, React Native and Vonage

This repository is the complete example for the accompanying post at: [Community Repository](https://github.com/nexmo-community/on-call-application-api)

This branch is the introduction branch. For a more complete example of the code please checkout to the `end-tutorial` branch.

**Table of Contents**

- [Prerequisites](#prerequisites)
- [Getting Started](#getting-started)
    * [Generate JWT keypair](#generate-jwt-keypair)
    * [Exposing Your Application To the Internet](#exposing-your-application-to-the-internet)
    * [Environment Variables](#environment-variables)
    * [Start Docker](#start-docker)
    * [Database Migrations](#database-migrations)
    * [Data Fixtures](#data-fixtures)
- [Test It](#test-it)
- [Code of Conduct](#code-of-conduct)
- [Contributing](#contributing)
- [License](#license)

## Prerequisites

- A phone number
- [A Vonage account](https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=)
- [Docker](http://getcomposer.org/)
- [Ngrok](https://learn.vonage.com/blog/2017/07/04/local-development-nexmo-ngrok-tunnel-dr)

## Getting Started

### Generate JWT keypair

Because this project will be making use of a mobile app built in React Native, along with authentication being required, JWT will be generated. So certificates need to be generated in other to make the JWT tokens. In the root of your project, run the following three commands:

```bash
mkdir API/var/jwt
openssl genpkey -out API/var/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in API/var/jwt/private.pem -out API/var/jwt/public.pem -pubout
```

### Exposing Your Application To the Internet

Making a phone call with Vonage, requires a virtual phone number with a webhook to log the events that happen when a phone call is made, answered, rejected, or ended. For the tutorial, ngrok is the service of choice to expose the application to the Internet. Install ngrok, and run the following command:

```
ngrok http 8080
```

Make sure to copy your ngrok HTTPS url as you'll need this later when configuring the project.

### Environment Variables

Copy or rename `.env.dist` to `.env`.

First fields to be updated are the values for your database credentials and config.  An example is shown below, but please use more secure credentials.

```env
DATABASE_URL=mysql://db_user:db_password@mysql:3306/on_call?serverVersion=8.0

MYSQL_DATABASE=on_call
MYSQL_USER=db_user
MYSQL_PASSWORD=db_password
MYSQL_ROOT_PASSWORD=root_password

JWT_PASSPHRASE=<your jwt passphrase>
```

### Start Docker

```bash
cd Docker
docker-compose up -d
composer install
```

### Database Migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Data Fixtures

```bash
php bin/console doctrine:fixtures:load
```

## Test it

Make a POST request to `<ngrok Url>/api/login_check` replacing the `<` and `>` with your ngrok url. The content type of the post request needs to be `application/json` and the body of the request needs to be similar to the following:

```json
{
    "username": "dev+1@company.com",
    "password": "test_pass"
}
```

On a successful request, you'll receive a JWT token in the response.

## Run the App

In a new Terminal run the following two commands:

```bash
cd MobileApp
npm install
expo start
```

## Code of Conduct

In the interest of fostering an open and welcoming environment, we strive to make participation in our project and our community a harassment-free experience for everyone. Please check out our [Code of Conduct][coc] in full.

## Contributing
We :heart: contributions from everyone! Check out the [Contributing Guidelines][contributing] for more information.

[![contributions welcome][contribadge]][issues]

## License

This project is subject to the [MIT License][license]

[logo]: vonage_logo.png "Vonage"
[contribadge]: https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat "Contributions Welcome"

[coc]: CODE_OF_CONDUCT.md "Code of Conduct"
[contributing]: CONTRIBUTING.md "Contributing"
[license]: LICENSE "MIT License"

[issues]: ./../../issues "Issues"