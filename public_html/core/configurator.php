<?php
declare(strict_types=1);
namespace RCSE\Core;

$config = new Configurator();

echo $config->is_logging_enabled();
echo $config->is_installed();
echo $config->is_plugins_enabled();
echo $config->get_base_params();

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
     * @throws \Exception
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
            throw new \Exception("File not found: " . $file_path . "\n", 404);
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
        catch (\Exception $e) {
            echo "Configurator Error (" . $e->getCode() . "): " . $e->getMessage() . "! Site should be reconfigured!";
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
            echo "Configurator Error: Failed to get property 'log', setting to default (true)";
            return true;
        }
    }

    /**
     * If main.json read properly, returns plugins mode (i.e. on or off), else returns true
     *
     * @return boolean
     */
    public function is_plugins_enabled() : bool
    {
        $main_read = $this->get_main_config();

        if ($main_read) {
            return $this->site_json['plugins'];
        }
        else {
            echo "Configurator Error: Failed to get property 'plugins', setting to default (true)";
            return true;
        }
    }

    /**
     * If main.json read properly, returns installation state (i.e. installed or not), else returns true
     *
     * @return boolean
     */
    public function is_installed() : bool
    {
        $main_read = $this->get_main_config();

        if($main_read) {
            return $this->site_json['plugins'];
        }
        else {
            echo "Configurator Error: Failed to get property 'installed',  setting to default (false)";
            return true;
        }
    }

    /**
     * If main.json read properly, returns array of base parameters, else returns defaulted array of same parameters
     *
     * @return array
     */
    public function get_base_params() : array
    {
        $main_read = $this->get_main_config();

        if($main_read) {
            $temp_arr = ['name' => $this->site_json['name'], 'about' => $this->site_json['about'], 'keywords' => $this->site_json['keywords'], 'theme' => $this->site_json['theme'], 'lang' => $this->site_json['lang']];
            return $temp_arr;
        }
        else {
            echo "Configurator Error: Failed to get base properties, settiong to defaults";
            $temp_arr = ['name' => "Unset", 'about' => "Configuration's dead", 'keywords' => "should, be, reinstalled", 'theme' => "default", 'lang' => "en"];
            return $temp_arr;
        }
    }

}