-- Active: 1775812263968@@127.0.0.1@3306@marrakech_food
create database if not exists marrakech_food;

use marrakech_food ;

create table user (
                      id int auto_increment primary key,
                      name varchar(255) not null,
                      email varchar(255) not null unique,
                      password varchar(255) not null,
                      created_at timestamp default current_timestamp
) ;

create table category (
                          id int auto_increment primary key ,
                          name varchar(255) not null
) ;

create table recette (
                         id int auto_increment primary key ,
                         name varchar(255) not null,
                         description text not null,
                         image varchar(255) null,
                         user_id int not null ,
                         foreign key (user_id) references user(id) ,
                         category_id int not null ,
                         foreign key (category_id) references category(id)   ,
                         created_at timestamp default current_timestamp
) ;

insert into category (name) value ('Tajine') ,
('Salade') ,
('Couscous') ,
('Jus') ;



