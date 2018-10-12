<?php
declare(strict_types=1);
namespace RCSE\Core;

$config = new Configurator();

echo $config->is_logging_enabled();

/**
 * class Configurator
 * Parses from and to JSON config files
 */
class Configurator
{
    private 
        $logger,
        $site_json,
        $db_json;
 
    function __construct()
    {

    }

    /**
     * Reads contents of ./configs/$file, if it exists, returns contents_of_file, else throws FileNotFoundException and returns false
     *
     * @param string $file filename, must end with .json (i.e. "main.json)
     * @throws FileNotFoundException
     * @return string|bool
     */
    private function read_file(string $file)
    {
        $file_handler; $file_contents;
        $file_path = "./configs/" . $file;

        if (file_exists($file_path)) {
            $file_handler = fopen($file_path, "rb");

            $file_contents = fread($file_handler, filesize($file_path));
            
            fclose($file_handler);

            return $file_contents;
        }
        else {
            throw new FileNotFoundException($file_path);
            return false;
        }

    }

    /**
     * Reads main.json and parses config file, if failed returns false and echoes "File error!", if succeeds returns true and puts site and database segments of config to corresponding arrays
     *
     * @return boolean
     */
    private function get_main_config() : bool
    {
        try {
        $file = $this->read_file("main.json");
        }
        catch (FileNotFoundException $e) {
            echo "Error (" . $e->getCode() . "): " . $e->getMessage();
            return false;
        }

        $main_json = json_decode($file[1], true);

        $this->site_json = $main_json['site'];
        $this->db_json = $main_json['database'];

        return true;
        
    }

    /**
     * If main.json read properly, returns logging mode (i.e. on or off), else returns true
     *
     * @return boolean
     */
    public function is_logging_enabled() : bool
    {
        $main_read = $this->get_main_config();

        if($main_read) {
            return $this->site_json['log'];
        } 
        else {
            echo "Failed to get property, setting to default (true)";
            return true;
        }
    }

}