Кейс-задание №4
Подключение к базе данных: /php/db_connect.php


Используйте следующий запрос для генерации структуры базы данных:
create table books
(
    id        int auto_increment
        primary key,
    title     varchar(255)   not null,
    author    varchar(100)   not null,
    year      int            not null,
    category  varchar(255)   not null,
    price     decimal(10, 2) null,
    stock     int            not null,
    cover     varchar(255)   null,
    file_path varchar(255)   null
);

create table users
(
    id       int auto_increment
        primary key,
    username varchar(50)                not null,
    password varchar(255)               not null,
    email    varchar(100)               not null,
    role     enum ('admin', 'customer') not null
);

create table rentals
(
    id           int auto_increment
        primary key,
    user_id      int  null,
    book_id      int  null,
    rental_start date null,
    rental_end   date null,
    constraint rentals_ibfk_1
        foreign key (user_id) references users (id),
    constraint rentals_ibfk_2
        foreign key (book_id) references books (id)
);

create index book_id
    on rentals (book_id);

create index user_id
    on rentals (user_id);
