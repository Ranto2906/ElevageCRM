DROP DATABASE IF EXISTS budget;    
CREATE DATABASE budget;
USE budget;

CREATE TABLE department
(
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    department_is_deleted TINYINT(1) DEFAULT 0 -- Boolean to mark as deleted
);

CREATE TABLE user
(
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100),
    password      VARCHAR(100),
    department_id INT,

    FOREIGN KEY (department_id) REFERENCES department (department_id)
);

CREATE TABLE category
(
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)
);

CREATE TABLE type
(
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100),
    type_is_deleted TINYINT(1) DEFAULT 0 -- Boolean to mark as deleted
);

CREATE TABLE priority
(
    priority_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)
);

CREATE TABLE exercise
(
    exercise_id  INT AUTO_INCREMENT PRIMARY KEY,
    start_date   DATE NOT NULL,
    nb_period    INT  NOT NULL,
    start_balance DECIMAL(10, 2),
    exercise_is_deleted TINYINT(1) DEFAULT 0 -- Boolean to mark as deleted
);

CREATE TABLE budget_element
(
    budget_element_id INT AUTO_INCREMENT PRIMARY KEY,
    department_id     INT  NOT NULL,
    category_id       INT  NOT NULL,
    type_id           INT  NOT NULL,
    description       TEXT NOT NULL,
    budget_element_is_deleted TINYINT(1) DEFAULT 0, -- Boolean to mark as deleted

    FOREIGN KEY (department_id) REFERENCES department (department_id),
    FOREIGN KEY (category_id) REFERENCES category (category_id),
    FOREIGN KEY (type_id) REFERENCES type (type_id)
);

CREATE TABLE transaction
(
    transaction_id    INT AUTO_INCREMENT PRIMARY KEY,
    nature            INT NOT NULL,  -- 1 : prevision, 2 : realisation
    exercise_id       INT NOT NULL,
    budget_element_id INT NOT NULL,
    period_num        INT NOT NULL,
    amount            DECIMAL(10, 2),
    status            INT DEFAULT 0, -- 0 : waiting, -1 : refused, 1 : approved
    priority_id       INT NOT NULL,

    FOREIGN KEY (exercise_id) REFERENCES exercise (exercise_id),
    FOREIGN KEY (priority_id) REFERENCES priority (priority_id)
);

-- Table type_coupe 
CREATE TABLE IF NOT EXISTS type_coupe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- Table typecheveux
CREATE TABLE IF NOT EXISTS typecheveux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- Table user
CREATE TABLE IF NOT EXISTS client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    age INT,
    typecheveux_id INT,
    email VARCHAR(100) ,
    telephone VARCHAR(20),
    preferences TEXT,
    FOREIGN KEY (typecheveux_id) REFERENCES typecheveux(id) ON DELETE SET NULL
);

-- Table reservation
CREATE TABLE IF NOT EXISTS reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_reservation DATETIME NOT NULL,
    cout DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES client(id) ON DELETE CASCADE
);

-- Table appreciation
CREATE TABLE IF NOT EXISTS appreciation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_appreciation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES client(id) ON DELETE CASCADE
);

-- Table action
CREATE TABLE IF NOT EXISTS action (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    cout DECIMAL(10,2) NOT NULL DEFAULT 0,
    phase TINYINT NOT NULL -- 1: avant, 2: pendant, 3: apres
);

-- Table action_effectue
CREATE TABLE IF NOT EXISTS action_effectue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_id INT NOT NULL,
    user_id INT NOT NULL,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (action_id) REFERENCES action(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES client(id) ON DELETE CASCADE
);

-- Table reaction
CREATE TABLE IF NOT EXISTS reaction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    cout DECIMAL(10,2),
    phase TINYINT NOT NULL -- 1: avant, 2: pendant, 3: apres
);


-- Table reaction_effectue
CREATE TABLE IF NOT EXISTS reaction_effectue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reaction_id INT NOT NULL,
    action_effectue_id INT NOT NULL,
    date_reaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reaction_effectue_validation(
    id INT AUTO_INCREMENT PRIMARY KEY,
    reaction_effectue_id INT NOT NULL,
    status INT NOT NULL, -- 0: en attente, 1: valide, -1: refuse
    FOREIGN KEY (reaction_effectue_id) REFERENCES reaction_effectue(id) ON DELETE CASCADE
);

