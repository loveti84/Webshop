<?php
// View Routes (HTML Pages)

error_log("routes.php loaded");
$router->get('/', 'Controllers\ViewController@products');
$router->get('/products', 'Controllers\ViewController@products');
$router->get('/product', 'Controllers\ViewController@product');
$router->get('/popular', 'Controllers\ViewController@popular');
$router->get('/login', 'Controllers\ViewController@login');

// API Routes

// Products
$router->get('/api/products', 'Controllers\ProductController@index');
$router->get('/api/products/popular', 'Controllers\ProductController@popular');
$router->get('/api/products/search', 'Controllers\ProductController@search');
$router->get('/api/products/filter', 'Controllers\ProductController@filter');
$router->get('/api/product', 'Controllers\ProductController@show');

// Reviews
$router->post('/api/reviews', handler: 'Controllers\ReviewController@create');

// Users
$router->post('/api/users/login', 'Controllers\UserController@login');
$router->post('/api/users/register', 'Controllers\UserController@register');
$router->post('/api/user/logout', 'Controllers\UserController@logout');
$router->get('/api/user/current', 'Controllers\UserController@current');



