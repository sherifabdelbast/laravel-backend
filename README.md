<p align="center"><a href="https://www.taskat.approved.tech/" target="_blank"><img src="https://www.taskat.approved.tech/_nuxt/logo-light.9ee6eafe.png" width="400"></a></p>


# Running the Website

To activate your Laravel project, kindly adhere to these methodical procedures:

## Update dependencies:

    composer update 

## Generation of Application Key:

    php artisan key:generate


## Configuration

Before proceeding further, it is imperative to ensure the operational readiness of your project by meticulously attending to this crucial configuration task:

Make sure you copy the `env.example` and rename to `.env`


## Invocation of Spatie Permissions.

    composer require spatie/laravel-permission

## Active the public disk :
    php artisan storage:link

## Execution of Migrations :
    php artisan migrate
    
## Run the server : 
    php artisan serve
