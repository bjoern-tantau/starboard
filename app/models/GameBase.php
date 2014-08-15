<?php

abstract class GameBase extends Base
{
    /* @var array Merged xmls per type. */

    protected static $xmls = array();

    /**
     * Get XML by type.
     *
     * @param string $type
     * @return SimpleXMLElement
     */
    protected static function getXml($type)
    {
        if (!isset(self::$xmls[$type])) {
            $dir = app_path(Config::get('game.config_dir')) . DIRECTORY_SEPARATOR . $type;
            $files = File::files($dir);
            $xml = simplexml_load_string('<game/>');
            foreach ($files as $file) {
                $xml = XmlHelper::merge($xml, simplexml_load_file($file));
            }
            self::$xmls[$type] = $xml;
        }
        return self::$xmls[$type];
    }

}
