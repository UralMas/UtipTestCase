<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;
use UtipTestCase\Controllers as Controllers;

/**
 * Регистрация роутинга
 */

/** @var Phalcon\Mvc\Micro $app */

/**
 * Авторизация и регистрация
 */
$auth = new MicroCollection();

$auth->setHandler(Controllers\AuthController::class, true)
    ->setPrefix('/api/v1')
    ->post(
        '/auth',
        'auth',
        'auth'
    )
    ->post(
        '/registration',
        'registration',
        'registration'
    );

$app->mount($auth);

/**
 * Действия с постами
 */
$posts = new MicroCollection();

$posts->setHandler(Controllers\PostsController::class, true)
    ->setPrefix('/api/v1/posts')
    ->get(
        '/',
        'getPosts',
        'getPosts'
    )
    ->post(
        '/add',
        'addPost',
        'addPost'
    )
    ->post(
        '/{id:[0-9]+}/edit',
        'editPost',
        'editPost'
    )
    ->post(
        '/{id:[0-9]+}/delete',
        'deletePost',
        'deletePost'
    )
    ->post(
        '/{id:[0-9]+}/upload',
        'uploadImage',
        'uploadImage'
    )
    ->get(
        '/categories',
        'getCategories',
        'getCategories'
    )
    ->post(
        '/categories/{id:[0-9]+}/delete',
        'deleteCategory',
        'deleteCategory'
    )
    ->get(
        '/images',
        'getImages',
        'getImages'
    )
    ->post(
        '/images/{id:[0-9]+}/delete',
        'deleteImage',
        'deleteImage'
    );

$app->mount($posts);
