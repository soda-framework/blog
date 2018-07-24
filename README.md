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

#### 3. Install your blog
`php artisan soda:blog:install`

#### 4. Create your blog 
1. run `php artisan soda:blog:create`
2. Select your application
3. Give your blog a name
4. Enter a slug for your blog (deafult: `/blog`)
5. Enable/Disable RSS for your blog



