<?php

interface DAOInterface {

    public function getLastId(EntityInterface $obj, $debug = false);

    public function getById(EntityInterface $obj, $debug = false);

    public function getExisteById(EntityInterface $obj, $debug = false);

    public function getAll(EntityInterface $obj, $debug = false);

    public function insert(EntityInterface $obj, $debug = false);

    public function update(EntityInterface $obj, $debug = false);

    public function delete(EntityInterface $obj, $debug = false);
}

?>