<?php
class MongoDBWrapper
{
    /**
     * @var Mongo
     */
    protected static $_MongoObject;

    /**
     * @var MongoDB
     */
    protected static $_MongoDBObject;

    protected static $_Init = true;

    public static function getMongoDBInstance()
    {
        self::build();
        return self::$_MongoDBObject;
    }

    public static function getMongoObject()
    {
        self::build();
        return self::$_MongoObject;
    }

    protected static function build()
    {
        if(self::$_Init)
        {
            $mongoConfig = ConfigHandler::item('mongoDB');

            ob_start();
            // Connect to test database
            MongoLog::setLevel(MongoLog::WARNING);
            MongoLog::setModule(MongoLog::SERVER | MongoLog::RS);

            if(isset($mongoConfig['username']))
                self::$_MongoObject = new Mongo("mongodb://{$mongoConfig['username']}:{$mongoConfig['password']}@{$mongoConfig['host']}/{$mongoConfig['dbname']}");
            else
                self::$_MongoObject = new Mongo("mongodb://{$mongoConfig['host']}/{$mongoConfig['dbname']}");

            self::$_MongoDBObject = self::$_MongoObject->{$mongoConfig['dbname']};
            self::$_Init = false;
            $mongoDBLog = ob_get_clean();

            if(!empty($mongoDBLog))
                self::mongoDBLogDump($mongoDBLog);
        }
    }

    protected static function mongoDBLogDump($logLine)
    {
        $file = ROOT_PATH. '/logs/mongodb.' . date('d-m-Y') . '.log';
        file_put_contents($file, $logLine, FILE_APPEND | LOCK_EX);
    }
}