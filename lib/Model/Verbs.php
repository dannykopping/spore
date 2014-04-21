<?php
namespace Spore\Model;

/**
 * @author Danny Kopping
 */
class Verbs
{
    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';
    const HEAD   = 'HEAD';
    const PATCH  = 'PATCH';

    public static function getAll()
    {
        return [
            self::GET,
            self::POST,
            self::PUT,
            self::DELETE,
            self::HEAD,
            self::PATCH,
        ];
    }
}