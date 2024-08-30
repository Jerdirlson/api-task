DROP DATABASE IF EXISTS `lotr`;
CREATE DATABASE `lotr` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `lotr`;

DROP TABLE IF EXISTS `factions`;
CREATE TABLE `factions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `faction_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `equipments`;
CREATE TABLE `equipments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `made_by` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `characters`;
CREATE TABLE `characters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `birth_date` date NOT NULL,
  `kingdom` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `equipment_id` int NOT NULL,
  `faction_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_id` (`equipment_id`),
  KEY `faction_id` (`faction_id`),
  CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipments` (`id`),
  CONSTRAINT `characters_ibfk_2` FOREIGN KEY (`faction_id`) REFERENCES `factions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
);

-- Tabla de permisos
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
);

-- Tabla intermedia de rol-permisos
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Tabla intermedia de usuario-roles
CREATE TABLE user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);



-- Insertar roles
INSERT INTO roles (name, description) VALUES
    ('admin', 'Administrator with full access'),
    ('editor', 'Editor with access to content management'),
    ('viewer', 'Viewer with read-only access');

-- Insertar permisos
INSERT INTO permissions (name, description) VALUES
    ('manage_users', 'Permission to manage user accounts'),
    ('edit_content', 'Permission to edit content'),
    ('view_content', 'Permission to view content');

-- Asignar permisos a roles
INSERT INTO role_permissions (role_id, permission_id) VALUES
    ((SELECT id FROM roles WHERE name = 'admin'), (SELECT id FROM permissions WHERE name = 'manage_users')),
    ((SELECT id FROM roles WHERE name = 'admin'), (SELECT id FROM permissions WHERE name = 'edit_content')),
    ((SELECT id FROM roles WHERE name = 'admin'), (SELECT id FROM permissions WHERE name = 'view_content')),
    ((SELECT id FROM roles WHERE name = 'editor'), (SELECT id FROM permissions WHERE name = 'edit_content')),
    ((SELECT id FROM roles WHERE name = 'editor'), (SELECT id FROM permissions WHERE name = 'view_content')),
    ((SELECT id FROM roles WHERE name = 'viewer'), (SELECT id FROM permissions WHERE name = 'view_content'));

-- Crear un usuario de prueba
INSERT INTO `user` (username, password) VALUES
    ('admin_user', '$2y$10$E0NRZ9JQLTjGzZhK.WQ2BO.9JlTgONByYRhft/HmB66TTDZCjwlqy'); -- password: 'admin123'

-- Asignar roles al usuario de prueba
INSERT INTO user_roles (user_id, role_id) VALUES
    ((SELECT id FROM `user` WHERE username = 'admin_user'), (SELECT id FROM roles WHERE name = 'admin'));


ALTER TABLE `user`
    ADD COLUMN `role_id` INT NOT NULL AFTER `password`;

-- Asignar un rol al usuario de prueba
UPDATE `user` SET `role_id` = (SELECT id FROM roles WHERE name = 'admin') WHERE username = 'admin_user';


INSERT INTO `factions` (
  `id`,
  `faction_name`,
  `description`
) VALUES (
  1,
  'MORDOR',
  'Mordor es un país situado al sureste de la Tierra Media, que tuvo gran importancia durante la Guerra del Anillo por ser el lugar donde Sauron, el Señor Oscuro, decidió edificar su fortaleza de Barad-dûr para intentar atacar y dominar a todos los pueblos de la Tierra Media.'
);

INSERT INTO `equipments` (
  `id`,
  `name`,
  `type`,
  `made_by`
) VALUES (
  1,
  'Maza de Sauron',
  'arma',
  'desconocido'
);

INSERT INTO `characters` (
  `id`,
  `name`,
  `birth_date`,
  `kingdom`,
  `equipment_id`,
  `faction_id`
) VALUES (
  1,
  'SAURON',
  '3019-03-25',
  'AINUR',
  1,
  1
);