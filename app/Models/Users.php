<?php

declare(strict_types=1);

namespace UtipTestCase\Models;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\{Callback as CallbackValidator,
    InclusionIn,
    PresenceOf,
    StringLength,
    Uniqueness as UniquenessValidator};
use Phalcon\Mvc\Model\Behavior\SoftDelete;

/**
 * Токены авторизации
 */
class Users extends ModelCacheable
{

    /**
     * Статусы
     */
    const STATE_DELETED = 0;
    const STATE_ACTIVE = 1;

    /**
     * Группы пользователей
     */
    const GROUP_ADMIN = 1; // Администратор - может управлять всем
    const GROUP_AUTHOR = 2; // Автор - может только получать данные

    /**
     * Группа пользователя
     */
    public int $group_id = 0;

    /**
     * Логин
     */
    public ?string $login;

    /**
     * Пароль (зашифрованный)
     */
    public ?string $password;

    /**
     * Статус
     */
    public int $state = 1;

    /**
     * Действия перед сохранением
     */
    public function beforeSave(): void
    {
        /**
         * Шифрование пароля перед сохранением (при создании или при изменении пароля)
         */
        $originalData = $this->getSnapshotData();

        if (trim($this->password) === '') {
            $this->password = $originalData['password'];
        } elseif (! array_key_exists('password', $originalData) || $this->password !== $originalData['password']) {
            $this->password = $this->getDI()->getSecurity()->hash($this->password);
        }
    }

    /**
     * Валидация данных
     */
    public function validation(): bool
    {
        $validator = new Validation();

        $validator->rules(
            "login",
            [
                new PresenceOf([
                    "message" => "Не указан логин"
                ]),
                new UniquenessValidator([
                    "message" => "Такой логин уже используется. Пожалуйста, укажите другой.",
                    'allowEmpty' => true
                ])
            ]
        );

        $validator->rules(
            "group_id",
            [
                new PresenceOf([
                    "message" => "Не указана группа пользователей"
                ]),
                new InclusionIn(
                    [
                        "message" => "Указана невалидная группа пользователей",
                        "domain"  => [self::GROUP_ADMIN, self::GROUP_AUTHOR],
                        'allowEmpty' => true
                    ]
                )
            ]
        );

        if ($this->hasSnapshotData() === false) {
            $validator->add(
                "password",
                new PresenceOf([
                    "message" => "Не указан пароль"
                ])
            );
        }

        if (! empty($this->password) && ($this->hasSnapshotData() === false || $this->password !== $this->getSnapshotData()['password'])) {
            $validator->rules(
                "password",
                [
                    new StringLength(
                        [
                            "min"            => 8,
                            "messageMinimum" => "Длина пароля должна быть не менее 8 символов",
                            'allowEmpty' => true
                        ]
                    ),
                    new CallbackValidator(
                        [
                            'message' => "Пароль не должен быть одинаковым с логином",
                            'callback' => function() {
                                return $this->password !== $this->login;
                            },
                            'allowEmpty' => true
                        ]
                    ),
                    new CallbackValidator(
                        [
                            'message' => 'Пароль должен содержать строчные, заглавные буквы и цифры',
                            'callback' => function() {
                                return preg_match("/[a-zа-яё]/", $this->password) != false
                                    && preg_match("/[A-ZА-ЯЁ]/", $this->password) != false
                                    && preg_match("/[0-9]/", $this->password) != false;
                            },
                            'allowEmpty' => true
                        ]
                    )
                ]
            );
        }

        return $this->validate($validator);
    }

    /**
     * Инициализация модели
     */
    public function initialize(): void
    {
        /**
         * Сохранение слепка данных перед сохранением
         */
        $this->keepSnapshots(true);

        /**
         * Подмена удаления на отключение записи
         */
        $this->addBehavior(new SoftDelete(
            [
                'field' => 'state',
                'value' => self::STATE_DELETED
            ]
        ));
    }

}
