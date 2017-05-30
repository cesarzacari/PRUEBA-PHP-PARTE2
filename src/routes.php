<?php
// Routes

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    $str = file_get_contents(__DIR__ . '\employees.json');
    $json = json_decode($str, true);

    // Render index view
    return $this->renderer->render($response, 'index.phtml', array(

    		'employees' => $json
    	));
});


$app->post('/search', function ($request, $response, $args) {
    $email = $request->getParam('email');
    $str = file_get_contents(__DIR__ . '\employees.json');
    $json = json_decode($str, true);

    $arr_employees = array();
    foreach($json as $employee){
    	if($employee['email'] == $email)
    	{
    		array_push($arr_employees, $employee);
    	}
    }

    // Render index view
    return $this->renderer->render($response, 'index.phtml', array(

    		'employees' => $arr_employees
    	));
});

$app->post('/all_list', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    $str = file_get_contents(__DIR__ . '\employees.json');
    $json = json_decode($str, true);

    // Render index view
    return $this->renderer->render($response, 'index.phtml', array(

    		'employees' => $json
    	));
});


$app->get('/ver_detalle/[{id}]', function ($request, $response, $args) {
    $id = $request->getAttribute('id');
    $str = file_get_contents(__DIR__ . '\employees.json');
    $json = json_decode($str, true);
    if(trim($id) == "")
    {
    	echo "Debe seleccionar un ver detalle";
    }
    else
    {
    	$arr_employees = array();
	    foreach($json as $employee){
	    	if($employee['id'] == $id)
	    	{
	    		array_push($arr_employees, $employee);
	    	}
	    }

	    if(count($arr_employees) == 0)
	    {
	    	echo "No se encontró datos del empleado seleccionado";
	    }
	    else
	    {
	    	return $this->renderer->render($response, 'detail.phtml', array(
    			'employees' => $arr_employees
    		));
	    }  
    }
});

$app->get('/webservice/wsBuscarXSalario.xml/{salariomin}/{salariomax}', function ($request, $response, $args){
	$salario_minimo = floatval($args['salariomin']);
	$salario_maximo = floatval($args['salariomax']);
    $str = file_get_contents(__DIR__ . '/employees.json');
	$ljson = (array)json_decode($str,TRUE);
	$larr_employees = array();
	foreach ($ljson as $employee)
	{
		$sueldo_actual = floatval((str_replace('$', '', str_replace(',', '', $employee['salary']))));
   		//$this->logger->info("salario float '/' ". $sueldo_actual);
    	if($sueldo_actual > $salario_minimo and $sueldo_actual < $salario_maximo)
    	{
    		array_push($larr_employees, (array)$employee);
		}
	}

	$newResponse = $response->withHeader('Content-type', 'application/xml');
 	return $this->renderer->render($newResponse, 'wsBuscarXSalario.xml', ["larr_employees" => $larr_employees]);
})->setName('wsBuscarXSalario.xml');

$app->post('/webservice', function ($request, $response, $args) {
    $salario_minimo = $request->getParam('salariomin');	
	$salario_maximo = $request->getParam('salariomax');	
    $str = file_get_contents(__DIR__ . '\employees.json');
    $json = json_decode($str, true);
	$arr_employees = array();
    foreach($json as $employee){
    	$sueldo_actual = floatval((str_replace('$', '', str_replace(',', '', $employee['salary']))));
    	if($sueldo_actual > $salario_minimo && $sueldo_actual < $salario_maximo)
    	{
    		array_push($arr_employees, $employee);
    	}
    }

    if(count($arr_employees) == 0)
    {
    	echo "Debe ingresar rango salarial.";
		echo "<br><br>";
		echo '<div class="row">
                <form class="form-inline" id="search_email" method="post" action="all_list">
                  <input type="submit" class="btn btn-primary pull-right" value="Ver lista completa" />
                </form></div>';
    }
    else
    {
    	//creating object of SimpleXMLElement
		$xml_user_info = new SimpleXMLElement("<?xml version=\"1.0\"?><user_info></user_info>");
		//function call to convert array to xml
		array_to_xml($arr_employees,$xml_user_info);
		//saving generated xml file
		$xml_file = $xml_user_info->asXML('employee.xml');
		//success and error message based on xml creation
		if($xml_file){
		    //echo 'Se ha creado el archivo employee.xml en la ruta public/.'."<br>";
			echo 'URL servicio web "wsBuscarXSalario.xml/{min}/{max}" (Buscar por Rango Salarial) : '."<br><br>";
			echo "http://" . $_SERVER["SERVER_NAME"] .":" .$_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"] ."/wsBuscarXSalario.xml/" .$salario_minimo ."/" .$salario_maximo ."";
			echo "<br><br>";
		    echo '<div class="row">
                  <form class="form-inline" id="lista_completa" method="post" action="all_list">
                  <input type="submit" class="btn btn-primary pull-right" value="Ver lista completa" />
                  </form></div>';                  
		}else{
		    echo 'XML error al generarse.';
		}
    }
});

function array_to_xml($array, &$xml_user_info) {
    foreach($array as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $xml_user_info->addChild("$key");
                array_to_xml($value, $subnode);
            }else{
                $subnode = $xml_user_info->addChild("item$key");
                array_to_xml($value, $subnode);
            }
        }else {
            $xml_user_info->addChild("$key",htmlspecialchars("$value"));
        }
    }
}
