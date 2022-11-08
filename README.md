# About Token Middleware

This is a laravel package for validating URLs with OTPs.

- Change Token
- Require token middleware
- Notifications
  - Token change notification

## Requirements

- PHP 8+
- Laravel 9+

## Steps To Install

- `composer require ikechukwukalu/tokenmiddleware`
- You will need a [queue](https://laravel.com/docs/9.x/queues#introduction) worker for the notifications. For a quick start set `QUEUE_CONNECTION=database` within your `.env` file.
- Run `php artisan queue:table`, `php artisan migrate` and `php artisan queue:work --queue=high,default`
- Add `token` column to the `fillable` and `hidden` arrays within the `User` model class
- Add `'require.token' => \Ikechukwukalu\Sanctumauthstarter\Middleware\RequireToken::class` to the `$routeMiddleware` in `kernel.php`
- Run `php artisan serve`

## Tests

It's recommended that you run the tests before you start adding your models and controllers.
Make sure to keep your `database/factories/UserFactory.php` Class updated with your `users` table so that the Tests can continue to run successfully.

### RUNNING TESTS

- `php artisan vendor:publish --tag=tm-feature-tests`
- `php artisan serve`
- `php artisan test`

## Reserved keywords for payloads

- `_uuid`
- `_token`
- `expires`
- `signature`

Some of the reserved keywords can be changed from the config file.

## Documentation

To generate documentation:

- `php artisan vendor:publish --tag=scribe-config`
- `php artisan scribe:generate`

Visit your newly generated docs:

- If you're using `static` type, find the `docs/index.html` file in your `public/` folder and open it in your browser.
- If you're using `laravel` type, start your app (`php artisan serve`), then visit `/docs`.

`example_languages`:
For each endpoint, an example request is shown in each of the languages specified in this array. Currently, only `bash` (curl), `javascript`(Fetch), `php` (Guzzle) and `python` (requests) are included. You can add extra languages, but you must also create the corresponding Blade view ([see Adding more example languages](https://scribe.knuckles.wtf/laravel/advanced/example-requests)).

Default: `["bash", "javascript"]`

Please visit [scribe](https://scribe.knuckles.wtf/) for more details.

## Publish Controllers

- `php artisan vendor:publish --tag=tm-controllers`

## Publish Models

- `php artisan vendor:publish --tag=tm-models`

## Publish Middleware

- `php artisan vendor:publish --tag=tm-middleware`

## Publish Rules

- `php artisan vendor:publish --tag=tm-rules`

## Publish Routes

- `php artisan vendor:publish --tag=tm-routes`

## Publish Lang

- `php artisan vendor:publish --tag=tm-lang`

## Publish Config

- `php artisan vendor:publish --tag=tm-config`

## Publish Laravel Email Notification Blade

- `php artisan vendor:publish --tag=laravel-notifications`

## License

The Laravel package is an open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
