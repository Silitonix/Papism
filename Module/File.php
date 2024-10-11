<?php

namespace Module;

class File
{

    static function read($filename)
    {
        $filename = path($filename);

        $stream = fopen($filename, 'r');
        if ($stream === false) return false;
        $fread = fread($stream, filesize($filename));

        fclose($stream);
        return $fread;
    }

    static function write($filename, $data, $mode = 'w'): false|int
    {
        $path = path_real($filename);

        if (!self::mkdir(dirname($filename))) return false;

        $stream = fopen($path, $mode);
        if ($stream === false) return false;
        $write = fwrite($stream, $data);

        fclose($stream);
        return $write;
    }

    static function append($filename, $data)
    {
        return self::write($filename, $data, 'a');
    }

    static function mkdir($filename)
    {
        $filename = path_real($filename);

        if (is_dir($filename)) return true;
        return mkdir($filename, 0777, true);
    }

    static function delete($filename)
    {
        $path = path($filename);

        if ($path === false) return true;
        if (!is_dir($path)) return unlink($path);

        foreach (scandir($path) as $file) {
            if ($file == '..' || $file == '.') continue;
            if (!self::delete($filename . '/' . $file)) return false;
        }
    }

    static function exist($filename)
    {
        $path = path_real($filename);
        return file_exists($path);
    }

    
    static function serve($filename)
    {
        $path = path($filename);
        $sapi = php_sapi_name();

        Header::mime($path);

        if ($sapi === 'fpm-fcgi' || $sapi === 'cgi-fcgi') {
            header("X-Accel-Redirect: /x-accel-redirect/" . $filename);
        } elseif ($sapi === 'apache2handler' || $sapi === 'apache') {
            header("X-Sendfile: $filename");
            header("Content-Disposition: attachment; filename=" . basename($filename));
        } else {
            readfile($path);
            exit;
        }
    }
}
