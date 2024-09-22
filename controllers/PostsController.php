<?php

declare(strict_types=1);

namespace UtipTestCase\Controllers;

use Exception;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use UtipTestCase\Libraries\ImagesHelper;
use UtipTestCase\Libraries\PostsHelper;
use UtipTestCase\Libraries\User;
use UtipTestCase\Libraries\Utils;
use UtipTestCase\Models\Posts;
use UtipTestCase\Models\Tokens;
use UtipTestCase\Models\Users;

/**
 * Контроллер постов и всего, что с ними связано
 */
class PostsController extends Controller
{

    /**
     * Данные пользователя
     */
    private User $user;


    /**
     * Проверка авторизации перед любым роутингом в данный контроллер
     * @throws Exception
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher): bool
    {
        /*if (! $this->request->hasHeader('Authorization')) {
            throw new GameAccountException();
        }

        $token = substr($this->request->getHeader('Authorization'), 7);*/
        $token = $this->request->get('token', 'string', '');

        /**
         * Проверка полученных данных
         */
        if ($token === '') {
            throw new Exception('Не передан токен авторизации', 401);
        }

        /**
         * Поиск связанных данных по токену
         */
        $tokenEntry = Tokens::findFirstByToken($token);

        if (! $tokenEntry) {
            throw new Exception('Передан неверный токен авторизации', 401);
        }

        /** @var Tokens $tokenEntry */

        /**
         * Проверка токена на время жизни
         */
        if ((date('U') - Utils::formatDate($tokenEntry->created_at, 'U')) >= $this->config->application->tokenLifetime) {
            throw new Exception('Токен авторизации устарел, пройдите авторизацию', 401);
        }

        /**
         * Проверка пользователя на возможность выполнения действий
         */
        $userEntry = Users::findFirstById($tokenEntry->user_id);

        if (! $userEntry || $userEntry->state != Users::STATE_ACTIVE) {
            throw new Exception('Доступ по данному токену запрещён', 403);
        }

        /** @var Users $userEntry */

        /**
         * Создание класса-хранилища данных пользователя для дальнейшего использования
         */
        $this->user = new User($userEntry->id, $userEntry->group_id);

        return true;
    }

    /**
     * Вывод данных постов
     *
     * @throws Exception
     */
    public function getPosts(): array
    {
        /**
         * Обработка и проверка GET-параметра "expand" (включение данных из других моделей)
         * Разрешены связки с только моделями Categories и Images
         */
        $expandModels = PostsHelper::getExpandModelsForRequest(
            $this->request->getQuery('expand', 'string', ''),
            ['category', 'images']
        );

        /**
         * Обработка и проверка GET-параметра "fields" (вывод только определённых полей поста)
         */
        $possibleFieldsNames = ['id', 'author_id', 'category_id', 'title', 'content', 'created_at'];

        $fields = PostsHelper::getFieldsForRequest(
            $this->request->getQuery('fields', 'string', ''),
            $possibleFieldsNames,
            $possibleFieldsNames
        );

        /**
         * Обработка и проверка GET-параметра "sort"
         */
        $sort = PostsHelper::getSortForRequest(
            $this->request->getQuery('sort', 'string', ''),
            ['id', 'author_id', 'category_id', 'title', 'created_at']
        );

        /**
         * Обработка и очистка прочих GET-параметров
         */
        $authorId = $this->request->getQuery('author_id', 'int', 0);
        $categoryId = $this->request->getQuery('category_id', 'int', 0);
        $offset = $this->request->getQuery('offset', 'int', 0);
        $limit = $this->request->getQuery('limit', 'int', 0);

        /**
         * Составление условий выборки постов
         */
        $conditions = [];

        if ($authorId != 0) {
            $conditions[] = "author_id = $authorId";
        }
        if ($categoryId != 0) {
            $conditions[] = "category_id = $categoryId";
        }

        /**
         * Вначале определяем общее количество постов по данным условиям
         */
        $total = Posts::count(! empty($conditions) ? $conditions : null);

        /**
         * Составляем каркас ответа
         */
        $response = [
            'posts' => [],
            'pagination' => [
                'total'     => $total,
                'offset'    => $offset,
                'limit'     => $limit
            ]
        ];

        /**
         * Если посты по данным условиям существуют - составляем запрос выборки
         */
        if ($total > $offset) {
            $query = [];

            if (! empty($conditions)) {
                $query['conditions'] = implode(' AND ', $conditions);
            }

            /**
             * Сортировка
             */
            if (! empty($sort)) {
                $query['order'] = implode(', ', $sort);
            }

            /**
             * Пагинация
             */
            if (! empty($offset)) {
                $query['offset'] = $offset;
            }
            if (! empty($limit)) {
                $query['limit'] = $limit;
            }

            $posts = Posts::find($query);

            /**
             * Формирование данных постов
             */
            foreach ($posts as $post) {
                $postData = [];

                /**
                 * Заполнение данных полей
                 */
                foreach ($fields as $field) {
                    $postData[$field] = $post->$field;
                }

                /**
                 * Данные категории
                 */
                if (in_array('category', $expandModels, true)) {
                    $postData['category'] = [
                        'name' => $post->category->name
                    ];
                }

                /**
                 * Данные изображений
                 */
                if (in_array('images', $expandModels, true)) {
                    $images = [];

                    foreach ($post->images as $image) {
                        $images[] = [
                            'id'    => $image->id,
                            'title' => $image->title,
                            'url'   => ImagesHelper::getImageUrl($image->filename)
                        ];
                    }

                    $postData['images'] = $images;
                }

                $response['posts'][] = $postData;
            }
        }

        return $response;
    }
}
