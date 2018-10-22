<?php
declare(strict_types=1);
namespace RCSE\Core;

$config = new Configurator();

echo $config->get_main_config("1");

/**
 * class Configurator
 * Parses from and to JSON config files
 */
class Configurator
{
    private 
        $logger,
        $errorhandler;
 
    function __construct()
    {
        $errorhandler = new ErrorHandler();
    }

    /**
     * Reads contents of ./configs/$file, if it exists, returns contents_of_file, else throws FileNotFoundException and returns false
     *
     * @param string $file filename, must end with .json (i.e. "main.json)
     * @throws \Exception
     * @return string|bool
     */
    private function read_file(string $file) : string
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
            throw new \Exception("File not found: " . $file_path,  404);
        }

    }

    /**
     * Reads main.json and parses config file, if failed echoes "File not found" and redirects to admin.php to reconfigure, 
     * if succeeds returns array with chosen properties
     * @param string $type config type, defalut "site"
     *
     * @return array
     */
    public function get_main_config(string $type="site") : array
    {
        try {
        $file = $this->read_file("main.json");
        }
        catch (\Exception $e) {
            $message = "Configurator Error (" . $e->getCode() . "): " . $e->getMessage() . "!\n Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n";
            $this->errorhandler->config_error($message);
            die;
        }

        $main_json = json_decode($file, true);

        switch(strtolower($type)) {
            case "site":
                return $main_json['site'];
                break;
            case "db":
            case "database":
                return $main_json['database'];
                break;
            default:
                echo "Error!";
                exit;
        }
    }
    
    /**
     * Reads queries.json and parses it, if failed echoes "File not found" and redirects to admin.php to reconfigure,
     * if succeeds returns array with chosen queries
     * @param string $type
     */



    /**
     * If main.json read properly, returns array of site properties
     *
     * @return array
     */
    public function get_site_params() : array
    {
        $main_read = $this->get_main_config();

        return $this->site_json;
    }

    /**
     * If main.json read properly, returns array of database properties
     * 
     * @return array
     */
    public function get_db_params() : array
    {
        $main_read = $this->get_main_config();

        return $this->db_json;
    }

}