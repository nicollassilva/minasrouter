<?php

namespace App\Models;

class User
{
    public static $names = [
        1 => 'Nicollas',
        'John',
        'Leo',
        'Tiago',
        'Hey, I\'m bug... You friend!'
    ];

    public static function find(Int $id)
    {
        return self::userData($id);
    }

    public static function userData(Int $id)
    {
        if(!isset(self::$names[$id])) {
            return [
                'error' => 404,
                'message' => 'User not found'
            ];
        }

        return [
            'id' => $id,
            'name' => self::$names[$id],
            'email' => 'lyod.hp@gmail.com',
            'developer' => true,
            'age' => 21,
            'from' => 'Brazil',
            'inspire' => [
                'I need a six month holiday, TWICE A YEAR!',
                'Men also have feelings... For example, they can feel hungry.'
            ]
        ];
    }
}
