<?php

namespace SlackWidget\System;

class Mongo {

    private static $client; // сервер
    private static $db = []; // база данных
    private static $collections = []; // коллекция

    public static function db($db_name, $collection){

        // соединение с сервером
        if(empty(self::$client)){
            self::$client = new \MongoClient();
        }

        // соединение с базой данных
        if(empty(self::$db[$db_name])){
            self::$db[$db_name] = self::$client->selectDB($db_name);
        }

        // получить коллекцию
        if(empty(self::$collections[$collection])){
            self::$collections[$collection] = new \MongoCollection(self::$db[$db_name], $collection);
        }

        return self::$collections[$collection];
    }

    public static function is_valid($id){
        return preg_match('/^[0-9a-zA-Z]{24}$/',(string)$id);
    }

    public static function id($id = false){
        if($id == false){
            return new \MongoId();
        } else {
            return new \MongoId((string)$id);
        }
    }
}