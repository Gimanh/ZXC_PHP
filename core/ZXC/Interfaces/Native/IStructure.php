<?php

namespace ZXC\Interfaces\Native;


interface IStructure
{
    /**
     * Select data from DB with stated params and data
     * @return mixed
     */
    public function select();

    /**
     * Delete data from DB with stated params and data
     * @return mixed
     */
    public function delete();

    /**
     * Update data in DB with stated params and data
     * @return mixed
     */
    public function update();

    /**
     * Insert data into DB with stated params and data
     * @return mixed
     */
    public function insert();

}