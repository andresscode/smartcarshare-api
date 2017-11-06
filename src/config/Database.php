<?php
/**
 * Created by PhpStorm.
 * User: Andress
 * Date: 4/11/17
 * Time: 4:50 PM
 */

class Database
{
    // Fields
    private $dbHost = 'us-cdbr-iron-east-05.cleardb.net';
    private $dbName = 'heroku_a028403062390ef';
    private $dbUser = 'b1b2b4ef4db8de';
    private $dbPass = 'd1a6d601';

    /**
     * Connects to the default MySQL database.
     *
     * @return PDO Connection to the default MySQL database.
     */
    public function connect()
    {
        $mysql_connect_str = "mysql:host=$this->dbHost;dbname=$this->dbName";
        $connection = new PDO($mysql_connect_str, $this->dbUser, $this->dbPass);

        return $connection;
    }

    /**
     * Returns an Array of PDO objects fetched from the database using the query passed as argument.
     *
     * @param $query String with the query to be used to fetch the data from the database. The string can contain
     *               variables, i.e. "SELECT * FROM table WHERE column = $value"
     * @return array PDO objects, the array can be empty when no records were matched or the array can contain just
     *               one value, is important to notice that you have to loop the array to get each object data or
     *               access the array like this array[0] when is just a single value returned.
     */
    public function get($query)
    {
        try
        {
            $con = $this->connect();

            $stmt = $con->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            $con = null;

            return $result;
        }
        catch (PDOException $e)
        {
            die("There was a problem with the database " . $e->getMessage());
        }
    }

    /**
     * Queries the database to insert (POST) the data passed in the query string. The method will try to insert the
     * data into the database and will check if there is any error with the database to return the database message
     * or returning the id number of the new insertion in case there is no errors.
     *
     * @param $query String with the data to insert, i.e. "INSERT INTO table (column, column) VALUES (value, value)"
     * @return int ID of the resource created in the database, or, the String message in case of error.
     */
    public function post($query)
    {
        try
        {
            $con = $this->connect();

            $stmt = $con->prepare($query);
            $stmt->execute();

            if ($stmt->errorCode() != 0000)
            {
                $con = null;
                return $stmt->errorInfo()[2];
            }

            $id = intval($con->lastInsertId());

            $con = null;

            return $id;
        }
        catch (PDOException $e)
        {
            die("There was a problem with the database " . $e->getMessage());
        }
    }
}