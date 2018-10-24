<?php
declare(strict_types=1);
namespace Core;

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
        $this->errorhandler = new ErrorHandler();
    }

    /**
     * Reads contents of ./configs/$file, if it exists, returns contents_of_file, else throws FileNotFoundException
     *
     * @param string $file filename, must end with .json (i.e. "main.json)
     * @throws Exceptions\FileNotFoundException
     * @return string
     */
    private function read_file(string $file) : string
    {
        $file_handler; $file_contents;
        $file_path = $_SERVER['DOCUMENT_ROOT'] . "/configs/" . $file;

        if (file_exists($file_path)) {
            $file_handler = fopen($file_path, "rb");

            $file_contents = fread($file_handler, filesize($file_path));
            
            fclose($file_handler);

            return $file_contents;
        } else {
            throw new Exceptions\FileNotFoundException($file_path);
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
        } catch (Exceptions\FileNotFoundException $e) {
            $message = "Configurator Error (" . $e->getCode() . "): " . $e->getMessage() . "!\n Site should be reconfigured! Redirecting to AdminPanel in 5 seconds!\n";
            $this->errorhandler->config_error($message);
            die;
        }

        $main_json = json_decode($file, true);

        switch(strtolower($type)) {
            case "site":
                return $main_json['site'];
            case "db":
            case "database":
                return $main_json['database'];
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