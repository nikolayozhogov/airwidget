<?php

namespace SlackWidget\System;

class Repository
{
    const MULTIPLE = 'multiple';

    public static function insert($doc = [])
    {
        if(empty($doc))
            return false;

        $db = Mongo::db(static::$db, static::$collection);

        $doc['created_at'] = time();

        $result = (array)$db->insert($doc);

        return !empty($result['ok']) ? true : false;

    }

    public static function count($filter = array()){

        $db = Mongo::db(static::$db, static::$collection);

        return (integer)$db->find($filter)->count();
    }

    public static function remove($filter = array()){

        $db = Mongo::db(static::$db, static::$collection);

        if(!is_array($filter))
            $filter = array('_id' => Mongo::id($filter));

        $result = (array)$db->remove($filter);
        return (!empty($result['ok']) && !empty($result['n'])) ? (integer)$result['n'] : false;
    }

    public static function update($filter, $set, $params = array()) {

        if(!is_array($filter))
            $filter = array('_id' => Mongo::id($filter));

        $db = Mongo::db(static::$db, static::$collection);

        $set['updated_at'] = time();

        $result = (array)$db->update($filter, array('$set' => $set), $params);

        return (!empty($result['ok']) && !empty($result['updatedExisting'])) ? (integer)$result['updatedExisting'] : false;
    }

    public static function find($filter = [], $params = [])
    {

        $result = [];
        $db = Mongo::db(static::$db, static::$collection);

        if (empty($params['sort'])) {
            $params['sort'] = [];
        }
        if (empty($params['skip'])) {
            $params['skip'] = 0;
        }
        if (empty($params['limit'])) {
            $params['limit'] = 1000000;
        }
        $cursor = $db->find($filter)->sort($params['sort'])->skip($params['skip'])->limit($params['limit']);

        if ($cursor) {
            foreach ($cursor as $row) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public static function find_by_id($id, $params = [])
    {
        $result = self::find(['_id' => \SlackWidget\System\Mongo::id($id)], $params);
        if (isset($result[0]))
            return $result[0];

        return [];
    }

    public static function find_one($filter = [], $params = [])
    {
        $params['limit'] = 1;
        $result = self::find($filter, $params);
        if (isset($result[0]))
            return $result[0];

        return [];
    }
}