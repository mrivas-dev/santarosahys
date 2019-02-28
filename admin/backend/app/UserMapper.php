<?php
namespace App;

/**
 *
Clase UserMapper
Es un conjunto de usuarios
 *
 **/
class UserMapper extends Mapper
{
    public function getAll($request, $response, array $args)
    {
        $sess = Session::loggedInfo();
        $db = DBHandler::getHandler();
        $query = "Select * from user";
        $resultado = $db->getAllRecords($query);
        return $response->withJson($resultado);
    }
    public function hojeador($request, $response, array $args)
    {
        $sess = Session::loggedInfo();
        $db = DBHandler::getHandler();
        $datos = [];
        $params = array_merge($request->getQueryParams(), $request->getParsedBody());
        if (empty($params['cuantos'])) {$params['cuantos'] = 10;}

        if (isset($params['pag'])) {
            $desde = (int) ($params['pag'] * $params['cuantos']);
            $pag = (int) $params['pag'];
            if ($desde < 0) {$desde = 0;}
        } else {
            $desde = 0;
            $pag = 1;
        }
        if (isset($params['columna'])) {
            $columna = $params['columna'];
            if (strpos($params['columna'], 'tx') !== false) {$columna = substr($params['columna'], 2);}
        } else { $columna = '';}

        $datos = [
            'pag' => $pag,
            'desde' => $desde,
            'cuantos' => (int) $params['cuantos'],
            'direccion' => (empty($params['direccion']) ? 'asc' : $params['direccion']),
            'columna' => $columna,
            'hasta' => (int) $params['cuantos'],
            'fecha' => (!isset($params['fecha'])) ? null : vfech($params['fecha']),
            'texto' => (isset($params['texto'])) ? $params['texto'] : null,
        ];
        foreach ($params as $k => $v) {
            if (!array_key_exists($k, $datos)) {
                error_log("parametro faltante en browser: " . $k);
                $datos[$k] = $v;
            }
		}
		$ordenamiento = ($datos['direccion'] == '' || $datos['columna'] == '') ? 'nombuser desc' : $datos['columna'] . ' ' . $datos['direccion'];

        if ($datos['texto']) {
            $query = 'SELECT SQL_CALC_FOUND_ROWS * from user where (nombuser like "%'.$datos['texto'].'%" or path like "%'.$datos['texto'].'%") order by '.$ordenamiento.' limit '.$datos['desde'].','.$datos['cuantos'].';';
            $todas = $db->getAllRecords($query);
        } else {
            $query = 'SELECT SQL_CALC_FOUND_ROWS * from user order by '.$ordenamiento.' limit '.$datos['desde'].','.$datos['cuantos'].';';
            $todas = $db->getAllRecords($query);
        }
        $cuantas = $db->getOneRecord('SELECT found_rows()');
        return $response->withJson(array('todos' => $todas, 'cuantas' => $cuantas));
    }
}
