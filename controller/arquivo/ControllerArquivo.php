<?php

require_once('../../model/arquivo/ClassArquivo.php');

$arquivo = new Arquivos();

$acao = "";
$id = "";
$version = "";
$file = "";

if (isset($_REQUEST['acao'])) {
    $acao = $_REQUEST['acao'];
}

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}

if (isset($_REQUEST['version'])) {
    $version = $_REQUEST['version'];
}

if (isset($_REQUEST['file'])) {
    $file = $_REQUEST['file'];
}

switch ($acao) {
    case '':
        echo $arquivo->listaArquivos();
        break;
    case 'add':
        echo $arquivo->addArquivo($_REQUEST, $_FILES);
        break;
    case 'update':
        echo $arquivo->updateArquivos($_REQUEST, $_FILES);
        break;
    case 'get':
        echo $arquivo->getArquivo($id);
        break;
    case 'del':
        echo $arquivo->delArquivo($id, $version, $file);
        break;
}
