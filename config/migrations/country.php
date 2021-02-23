<?php

$tableSchema[] = "CREATE TABLE `country` (
  `id` int(5) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$tableSchema[] = "INSERT INTO `country` (`id`, `name`) VALUES
(1, 'Россия'),
(2, 'Украина'),
(3, 'Казахстан'),
(4, 'Беларусь'),
(5, 'Латвия'),
(6, 'Молдова'),
(7, 'Эстония'),
(8, 'Азербайджан'),
(9, 'Литва'),
(10, 'США')";
