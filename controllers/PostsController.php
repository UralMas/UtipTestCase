<?php

declare(strict_types=1);

namespace UtipTestCase\Controllers;

use Exception;
use Phalcon\Mvc\Controller;
use UtipTestCase\Libraries\ImagesHelper;
use UtipTestCase\Libraries\PostsHelper;
use UtipTestCase\Libraries\User;
use UtipTestCase\Libraries\Utils;
use UtipTestCase\Models\Categories;
use UtipTestCase\Models\Images;
use UtipTestCase\Models\Posts;
use UtipTestCase\Models\Tokens;
use UtipTestCase\Models\Users;

/**
 * Контроллер постов и всего, что с ними связано
 */
class PostsController extends Controller
{

    /**
     * Список разрешённых действий для непривилегированного пользователя
     */
    private const ALLOWED_ACTIONS = [
        'getCategories',
        'getPosts',
        'getImages',
    ];

    /**
     * Данные пользователя
     */
    private User $user;

    /**
     * Проверка авторизации и доступа к методу
     * @throws Exception
     */
    public function onConstruct(): void
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
         * Если пользователь - непривилегированный,
         * то идёт проверка на возможность доступа к данному методу
         */
        if ($userEntry->group_id != Users::GROUP_ADMIN
            && ! in_array($this->router->getMatchedRoute()->getName(), self::ALLOWED_ACTIONS, true)) {
            throw new Exception('Доступ к данному методу запрещён', 405);
        }

        /**
         * Создание класса-хранилища данных пользователя для дальнейшего использования
         */
        $this->user = new User($userEntry->id, $userEntry->group_id);
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
        if ($total > max(0, $offset)) {
            $query = [];

            if (! empty($conditions)) {
                $query['conditions'] = implode(' AND ', $conditions);
            }

            /**
             * Сортировка
             */
            if (! empty($sort)) {
                $query['order'] = $sort;
            }

            /**
             * Пагинация
             */
            if ($offset > 0) {
                $query['offset'] = $offset;
            }
            if ($limit > 0) {
                $query['limit'] = $limit;
            }

            $posts = Posts::find($query);

            /**
             * Формирование данных постов
             */
            foreach ($posts as $post) {
                $response['posts'][] = PostsHelper::getPostData($post, $fields, $expandModels);
            }
        }

        return $response;
    }

    /**
     * Создание нового поста
     *
     * @throws Exception
     */
    public function addPost(): array
    {
        /**
         * Обработка и очистка POST-параметров
         */
        $title = $this->request->getPost('title', 'string', '');
        $content = $this->request->getPost('content', 'string', '');
        $categoryId = $this->request->getPost('category_id', 'int', 0);

        if (empty($title) || empty($content) || $categoryId <= 0) {
            throw new Exception("Переданы неверные данные", 400);
        }

        /**
         * Если не указан автор - то автором становится пользователь, который публикует пост
         */
        $authorId = $this->request->get('author_id', 'int', $this->user->getId());

        $post = new Posts([
            'category_id'   => $categoryId,
            'author_id'     => $authorId,
            'title'         => $title,
            'content'       => $content
        ]);

        /**
         * Валидация и сохранение поста
         */
        if (! $post->create()) {
            throw new Exception($post->getMessages()[0]->getMessage(), 400);
        }

        return [
            'id' => $post->id,
            'create_at' => $post->created_at
        ];
    }

    /**
     * Редактирование поста
     *
     * @throws Exception
     */
    public function editPost(int $id): array
    {
        $post = Posts::findFirstById($id);

        if (! $post) {
            throw new Exception("Пост с ID: $id не найден", 400);
        }

        /**
         * Обработка и очистка POST-параметров
         */
        $title = $this->request->getPost('title', 'string', '');
        $content = $this->request->getPost('content', 'string', '');
        $authorId = $this->request->getPost('author_id', 'int', 0);

        if (empty($title) && empty($content) && $authorId == 0) {
            throw new Exception("Не переданы параметры, которые надо изменить", 400);
        }

        /**
         * Изменение данных поста
         */
        if (! empty($title)) {
            $post->title = $title;
        }
        if (! empty($content)) {
            $post->content = $content;
        }
        if ($authorId != 0) {
            $post->author_id = $authorId;
        }

        /**
         * Валидация и сохранение поста
         */
        if (! $post->update()) {
            throw new Exception($post->getMessages()[0]->getMessage(), 400);
        }

        return [
            'updated_at' => $post->updated_at
        ];
    }

    /**
     * Удаление поста
     *
     * @throws Exception
     */
    public function deletePost(int $id): array
    {
        $post = Posts::findFirstById($id);

        if (! $post) {
            throw new Exception("Пост с ID: $id не найден", 400);
        }

        /**
         * Удаление поста
         */
        if (! $post->delete()) {
            throw new Exception($post->getMessages()[0]->getMessage(), 400);
        }

        return [];
    }

    /**
     * Вывод данных категорий постов
     *
     * @throws Exception
     */
    public function getCategories(): array
    {
        /**
         * Обработка и проверка GET-параметра "expand" (включение данных из других моделей)
         * Разрешены связки с только моделями Posts и Images
         */
        $expandModels = PostsHelper::getExpandModelsForRequest(
            $this->request->getQuery('expand', 'string', ''),
            ['posts', 'images']
        );

        /**
         * Обработка и проверка GET-параметра "fields" (вывод только определённых полей категорий)
         */
        $possibleFieldsNames = ['id', 'name', 'created_at'];

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
            ['id', 'name', 'created_at']
        );

        /**
         * Обработка и очистка прочих GET-параметров
         */
        $offset = $this->request->getQuery('offset', 'int', 0);
        $limit = $this->request->getQuery('limit', 'int', 0);

        /**
         * Вначале определяем общее количество категорий постов
         */
        $total = Categories::count();

        /**
         * Составляем каркас ответа
         */
        $response = [
            'categories' => [],
            'pagination' => [
                'total'     => $total,
                'offset'    => $offset,
                'limit'     => $limit
            ]
        ];

        /**
         * Если категории по данным условиям существуют - составляем запрос выборки
         */
        if ($total > max(0, $offset)) {
            $query = [];

            /**
             * Сортировка
             */
            if (! empty($sort)) {
                $query['order'] = $sort;
            }

            /**
             * Пагинация
             */
            if ($offset > 0) {
                $query['offset'] = $offset;
            }
            if ($limit > 0) {
                $query['limit'] = $limit;
            }

            $categories = Categories::find($query);

            /**
             * Формирование данных постов
             */
            foreach ($categories as $category) {
                $categoryData = [];

                /**
                 * Заполнение данных полей
                 */
                foreach ($fields as $field) {
                    $categoryData[$field] = $category->$field;
                }

                /**
                 * Данные постов
                 */
                if (in_array('posts', $expandModels, true)) {
                    $postFields = ['id', 'author_id', 'category_id', 'title', 'content', 'created_at'];
                    $postExpandModels = [];

                    if (in_array('images', $expandModels, true)) {
                        $postExpandModels[] = 'images';
                    }

                    $categoryData['posts'] = [];

                    foreach ($category->posts as $post) {
                        $categoryData['posts'][] = PostsHelper::getPostData($post, $postFields, $postExpandModels);
                    }
                }

                /**
                 * Данные изображений
                 * Выводятся отдельно если только в запросе нет связки с постами, т.к. там тоже выводятся изображения
                 */
                if (in_array('images', $expandModels, true) && ! in_array('posts', $expandModels, true)) {
                    $images = [];

                    foreach ($category->images as $image) {
                        $images[] = [
                            'id'    => $image->id,
                            'title' => $image->title,
                            'url'   => ImagesHelper::getImageUrl($image->filename)
                        ];
                    }

                    $categoryData['images'] = $images;
                }

                $response['categories'][] = $categoryData;
            }
        }

        return $response;
    }

    /**
     * Удаление категории
     *
     * @throws Exception
     */
    public function deleteCategory(int $id): array
    {
        $category = Categories::findFirstById($id);

        if (! $category) {
            throw new Exception("Категория с ID: $id не найдена", 400);
        }

        /**
         * Удаление категории
         */
        if (! $category->delete()) {
            throw new Exception($category->getMessages()[0]->getMessage(), 400);
        }

        return [];
    }

    /**
     * Вывод данных изображений
     *
     * @throws Exception
     */
    public function getImages(): array
    {
        /**
         * Обработка и проверка GET-параметра "fields" (вывод только определённых полей изображений)
         */
        $possibleFieldsNames = ['id', 'title', 'filename', 'url', 'created_at'];

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
            ['id', 'name', 'created_at']
        );

        /**
         * Обработка и очистка прочих GET-параметров
         */
        $categoryId = $this->request->get('category_id', 'int', 0);
        $postId = $this->request->get('post_id', 'int', 0);
        $offset = $this->request->getQuery('offset', 'int', 0);
        $limit = $this->request->getQuery('limit', 'int', 0);

        if ($categoryId > 0) {
            /**
             * Если указана категория - выбираются изображения из связанных с ней постов
             */
            $category = Categories::findFirstById($categoryId);

            if ($category) {
                if (! empty($sort)) {
                    $images = $category->getRelated(
                        'Images',
                        [
                            'order' => $sort,
                        ]
                    );
                } else {
                    $images = $category->images;
                }
            }
        } elseif ($postId > 0) {
            /**
             * Если указан пост - выбираются связанные с ним изображения
             */
            $post = Posts::findFirstById($postId);

            if ($post) {
                if (! empty($sort)) {
                    $images = $post->getRelated(
                        'Images',
                        [
                            'order' => $sort,
                        ]
                    );
                } else {
                    $images = $post->images;
                }
            }
        } else {
            /**
             * Если ничего не указано - выбираются все изображения
             */
            $query = [];

            /**
             * Сортировка
             */
            if (! empty($sort)) {
                $query['order'] = $sort;
            }

            /**
             * Пагинация
             */
            if ($offset > 0) {
                $query['offset'] = $offset;
            }
            if ($limit > 0) {
                $query['limit'] = $limit;
            }

            $images = Images::find($query);
        }

        $total = isset($images) ? $images->count() : 0;

        /**
         * Составляем каркас ответа
         */
        $response = [
            'images' => [],
            'pagination' => [
                'total'     => $total,
                'offset'    => $offset,
                'limit'     => $limit
            ]
        ];

        /**
         * Если изображения по данным условиям существуют - делаем выборку
         */
        if (isset($images) && $total > max(0, $offset)) {
            $x = 0;

            foreach ($images as $image) {
                if ($x < $offset) {
                    continue;
                }

                $response['images'][] = PostsHelper::getImageData($image, $fields);

                if ($limit > 0 && ++$x == (max(0, $offset) + $limit)) {
                    break;
                }
            }
        }

        return $response;
    }

    /**
     * Удаление изображения
     *
     * @throws Exception
     */
    public function deleteImage(int $id): array
    {
        $image = Images::findFirstById($id);

        if (! $image) {
            throw new Exception("Изображение с ID: $id не найдено", 400);
        }

        /**
         * Удаление изображения
         */
        if (! $image->delete()) {
            throw new Exception($image->getMessages()[0]->getMessage(), 400);
        }

        return [];
    }
}
