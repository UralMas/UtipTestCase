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

$posts->setHandler(Controllers\AuthController::class, true)
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
    ->get(
        '/categories',
        'getCategories',
        'getCategories'
    )
    ->get(
        '/images',
        'getImages',
        'getImages'
    );

$app->mount($posts);
