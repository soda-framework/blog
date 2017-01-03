# Soda CMS - Blog add-on
Top up your CMS with a sweet blog!

## Installation
Install easily using Composer

#### 1. Require Soda Blog

`cd app-name`

`composer require soda-framework/blog`

#### 2. Integrate into Laravel

Add package to providers in `/config/app.php`

```
'providers' => [
    Soda\Blog\SodaBlogServiceProvider::class,
]
```

#### 3. Migrate & Seed

`php artisan soda:blog:migrate`

`php artisan soda:blog:seed`
