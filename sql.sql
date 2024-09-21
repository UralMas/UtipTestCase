CREATE TABLE `users` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `group_id` tinyint(1) unsigned NOT NULL DEFAULT '2',
 `login` varchar(255) NOT NULL,
 `password` varchar(255) NOT NULL,
 `state` tinyint(1) unsigned NOT NULL DEFAULT '1',
 PRIMARY KEY (`id`),
 KEY `idx_group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `tokens` (
                         `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                         `user_id` int(11) unsigned NOT NULL,
                         `created_at` datetime NOT NULL,
                         PRIMARY KEY (`id`),
                         KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `categories` (
                            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                            `name` varchar(255) NOT NULL,
                            `created_at` datetime NOT NULL,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `posts` (
                         `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                         `category_id` int(11) unsigned NOT NULL,
                         `author_id` int(11) unsigned NOT NULL,
                         `title` varchar(255) NOT NULL,
                         `content` text NOT NULL,
                         `created_at` datetime NOT NULL,
                          PRIMARY KEY (`id`),
                         KEY `idx_category_id` (`category_id`),
                         KEY `idx_author_id` (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `images` (
                         `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                         `post_id` int(11) unsigned NOT NULL,
                         `title` varchar(255) NOT NULL,
                         `filename` varchar(255) NOT NULL,
                         `created_at` datetime NOT NULL,
                         PRIMARY KEY (`id`),
                         KEY `idx_post_id` (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;