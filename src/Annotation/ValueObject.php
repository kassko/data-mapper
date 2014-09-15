<?php

namespace Kassko\DataAccess\Annotation;

/**
* @Annotation
* @Target("PROPERTY")
*
* @author kko
*/
final class ValueObject
{
	/**
	* @var string
	*/
    public $name;

    /**
	* @var string
	*/
    public $valueObjectClass;

    /**
	* @var array
	*/
    public $fieldNames = [];
}

//DO NOT KILL THE FOLLOWING COMMENTED CODE !
/*
Array (
 	[adresseVente] => Array (
		[0] => Array (
 		 	[name] => adresseVente
 		 	[valueObjectClass] => Address
 		 	[fieldNames] => Array (
 		 		[rue] => @rue
 		 		[ville] => villeVente
 		 		[codePostal] => codePostalVente
 		 		)
		 	)
	 	[1] => Array (
	 	    [name] => rue
	 	    [valueObjectClass] => Rue
	 	    [fieldNames] => Array (
	 	    	[numero] => numRueVente
	 	    	[nom] => nomRueVente
 	    	)
    	)
	)
	[adresseLivraison] => Array ( [0] => Array ( [name] => adresseLivraison [valueObjectClass] => Address [fieldNames] => Array ( [rue] => @rue [ville] => villeLivraison [codePostal] => codePostalLivraison ) ) [1] => Array ( [name] => rue [valueObjectClass] => Rue [fieldNames] => Array ( [numero] => numRueLivraison [nom] => nomRueLivraison ) ) ) [adresseContestation] => Array ( [0] => Array ( [name] => adresseContestation [valueObjectClass] => Address [fieldNames] => Array ( [rue] => rueContestation [ville] => villeContestation [codePostal] => codePostalContestation ) ) ) )
*/

/*
$data = [];
foreach ($vos as $valueObjectName => $valueObjectData) {
	$data[$valueObjectName] = [
		$valueObjectData[]
	];
}*/
